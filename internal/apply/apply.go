// Package apply fills missing parameter types in function, method and
// constructor definitions from the resolved argument types.
package apply

import (
	"slices"
	"strconv"
	"strings"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/inherit"
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/argtyper/internal/valuetype"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
	"github.com/rectorphp/php-parser-in-go/pkg/token"
)

// Source adds parameter types to a single PHP source file. It returns the new
// source, the type declarations added (their written text, e.g. "int" or
// "?string" or "\Foo"), and whether the file changed. On a parse error the
// original source is returned unchanged. The symbols table resolves constant
// and enum-case default values; the inheritance table decides whether typing a
// method would change an inherited signature. The options limit which types
// are added.
func Source(src []byte, types aggregate.Types, table *symbols.Table, inheritance *inherit.Table, options Options) (string, []string, bool) {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return string(src), nil, false
	}

	applier := &applier{types: types, symbols: table, inheritance: inheritance, names: phpast.ResolveNames(root), options: options}
	applier.walk(root, nil)

	if len(applier.added) == 0 {
		return string(src), nil, false
	}

	return phpast.Print(root), applier.added, true
}

// Options limit the added types to some kinds. With none set, every type is
// added; with one or more set, only the chosen kinds are.
type Options struct {
	Literals bool // string types that get a `'a'|'b'` literal doc type
	Objects  bool // object types
}

// allows reports whether a resolved type passes the options; docType is its
// literal doc type, if any.
func (o Options) allows(resolved aggregate.Resolved, docType string) bool {
	if !o.Literals && !o.Objects {
		return true
	}
	if o.Literals && docType != "" {
		return true
	}
	return o.Objects && onlyObjects(resolved)
}

func onlyObjects(resolved aggregate.Resolved) bool {
	for _, member := range resolved.Types {
		if !member.IsObject() {
			return false
		}
	}
	return len(resolved.Types) > 0
}

type applier struct {
	types       aggregate.Types
	symbols     *symbols.Table
	inheritance *inherit.Table
	names       map[ast.Vertex]string
	added       []string
	options     Options
}

// walk tracks the enclosing class, enum or trait, the scopes collect records
// method calls under.
func (a *applier) walk(node ast.Vertex, class ast.Vertex) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass, *ast.StmtEnum, *ast.StmtTrait:
		class = typed
	case *ast.StmtFunction:
		a.applyFunction(typed)
	case *ast.StmtClassMethod:
		a.applyMethod(typed, class)
	}

	for _, child := range phpast.Children(node) {
		a.walk(child, class)
	}
}

func (a *applier) applyFunction(function *ast.StmtFunction) {
	name := phpast.ShortName(function.Name)
	lookup := func(position int) (aggregate.Resolved, bool) {
		return a.types.Function(name, position)
	}
	a.applyParams(function, function.Params, lookup, "")
}

func (a *applier) applyMethod(method *ast.StmtClassMethod, class ast.Vertex) {
	name := phpast.ShortName(method.Name)
	if isMagicExceptConstructor(name) {
		return
	}
	if !a.methodTypeable(method, class) {
		return
	}

	classFQCN := ""
	if class != nil {
		classFQCN = phpast.ResolvedName(a.names, class)
	}

	lookup := func(position int) (aggregate.Resolved, bool) {
		return a.types.Method(classFQCN, name, position)
	}
	a.applyParams(method, method.Params, lookup, classFQCN)
}

// applyParams types the params of a function or method node: from the call-site
// type found by lookup, else from a literal default value. enclosingFQCN names
// the class, empty for a plain function.
func (a *applier) applyParams(node ast.Vertex, params []ast.Vertex, lookup func(position int) (aggregate.Resolved, bool), enclosingFQCN string) {
	added := map[string]string{}
	docs := map[string]string{}
	var order []string
	checked := typeCheckedVariables(node)
	for position, paramNode := range params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || !typeable(param) || checked[phpast.VariableName(param.Var)] {
			continue
		}
		if resolved, ok := lookup(position); ok {
			if !docAllows(node, param, resolved) {
				continue
			}
			docType := literalDocType(param, resolved)
			if !a.options.allows(resolved, docType) {
				continue
			}
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
			if docType != "" {
				docs[phpast.VariableName(param.Var)] = docType
				order = append(order, phpast.VariableName(param.Var))
			}
			continue
		}
		if resolved, ok := a.defaultType(param, enclosingFQCN); ok && a.options.allows(resolved, "") && docAllows(node, param, resolved) {
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
		}
	}
	phpast.StripRedundantDocParams(node, added)
	phpast.AddDocParams(node, docs, order)
}

// typeCheckFunctions inspect the type of a value at runtime. A parameter passed
// into one of them is expected to take more than one type, even when every
// resolved call site passes the same one.
var typeCheckFunctions = map[string]bool{
	"is_array":       true,
	"is_bool":        true,
	"is_callable":    true,
	"is_countable":   true,
	"is_double":      true,
	"is_float":       true,
	"is_int":         true,
	"is_integer":     true,
	"is_iterable":    true,
	"is_long":        true,
	"is_numeric":     true,
	"is_object":      true,
	"is_scalar":      true,
	"is_string":      true,
	"gettype":        true,
	"get_debug_type": true,
}

// typeCheckedVariables returns the names of variables passed directly into a
// type check function anywhere in the node, e.g. `is_array($sendTo)`.
func typeCheckedVariables(node ast.Vertex) map[string]bool {
	checked := map[string]bool{}
	var walk func(node ast.Vertex)
	walk = func(node ast.Vertex) {
		if node == nil {
			return
		}
		if call, ok := node.(*ast.ExprFunctionCall); ok && typeCheckFunctions[strings.ToLower(phpast.ShortName(call.Function))] {
			for _, argNode := range call.Args {
				if arg, ok := argNode.(*ast.Argument); ok {
					if name := phpast.VariableName(arg.Expr); name != "" {
						checked[name] = true
					}
				}
			}
		}
		for _, child := range phpast.Children(node) {
			walk(child)
		}
	}
	walk(node)
	return checked
}

// defaultType infers a parameter type from its literal default value, so
// `$page = 1` becomes `int $page = 1` even with no call site. A `null` default
// carries no type of its own; enclosingFQCN resolves `self::` constants and
// qualifies an object default such as an enum case.
func (a *applier) defaultType(param *ast.Parameter, enclosingFQCN string) (aggregate.Resolved, bool) {
	if param.DefaultValue == nil {
		return aggregate.Resolved{}, false
	}
	typeName := a.symbols.TypeOfExpr(param.DefaultValue, enclosingFQCN, a.names)
	if typeName == valuetype.Unknown || typeName == valuetype.Null {
		return aggregate.Resolved{}, false
	}
	if typeName.IsObject() {
		if fqcn := phpast.ObjectFQCN(param.DefaultValue, a.names, enclosingFQCN); fqcn != "" {
			typeName = valuetype.Object(fqcn)
		}
	}
	return aggregate.Resolved{Types: []valuetype.Type{typeName}}, true
}

// literalDocType returns a `'a'|'b'` doc type for a parameter that only ever
// receives a few string literals. Empty when there are none, or when a default
// value falls outside them.
func literalDocType(param *ast.Parameter, resolved aggregate.Resolved) string {
	if len(resolved.Literals) == 0 {
		return ""
	}
	if param.DefaultValue != nil && !hasNullDefault(param) {
		value, ok := phpast.StringLiteral(param.DefaultValue)
		if !ok || !slices.Contains(resolved.Literals, value) {
			return ""
		}
	}

	members := make([]string, len(resolved.Literals))
	for i, literal := range resolved.Literals {
		members[i] = "'" + literal + "'"
	}
	docType := strings.Join(members, "|")
	if resolved.Nullable || hasNullDefault(param) {
		docType += "|null"
	}
	return docType
}

// setType writes the resolved type onto the parameter and returns the type text
// written, so the caller can drop a now-redundant @param doc line.
func (a *applier) setType(param *ast.Parameter, resolved aggregate.Resolved) string {
	nullable := resolved.Nullable || hasNullDefault(param)

	members := make([]string, len(resolved.Types))
	for i, member := range resolved.Types {
		members[i] = typeText(member)
	}

	// Take the whitespace that sat before the variable (indentation, or the
	// space after a comma or modifier) and put it in front of the new type, so
	// the type slots into the variable's old position. A single space then
	// separates the type from the variable.
	leading := takeVarLeading(param)

	// A single nullable type is written as `?Type`; a union carries null as a
	// `null` member instead, since `?` cannot combine with a union.
	if len(members) == 1 && nullable {
		param.Type = &ast.Nullable{
			QuestionTkn: &token.Token{Value: []byte("?"), FreeFloating: leading},
			Expr:        &ast.Identifier{IdentifierTkn: &token.Token{Value: []byte(members[0])}},
		}
		nullableText := "?" + members[0]
		a.added = append(a.added, nullableText)
		return nullableText
	}

	text := strings.Join(members, "|")
	if nullable {
		text += "|null"
	}
	param.Type = &ast.Identifier{IdentifierTkn: &token.Token{Value: []byte(text), FreeFloating: leading}}
	a.added = append(a.added, text)
	return text
}

// takeVarLeading returns the leading whitespace tokens of the parameter's
// variable and replaces them with a single space.
func takeVarLeading(param *ast.Parameter) []*token.Token {
	variable, ok := param.Var.(*ast.ExprVariable)
	if !ok {
		return nil
	}

	leadingToken := variable.DollarTkn
	if leadingToken == nil {
		if name, ok := variable.Name.(*ast.Identifier); ok {
			leadingToken = name.IdentifierTkn
		}
	}
	if leadingToken == nil {
		return nil
	}

	leading := leadingToken.FreeFloating
	leadingToken.FreeFloating = []*token.Token{{Value: []byte(" ")}}
	return leading
}

// docPseudoTypes maps doc-only types onto the native type they narrow.
var docPseudoTypes = map[string]string{
	"integer":          "int",
	"positive-int":     "int",
	"negative-int":     "int",
	"non-positive-int": "int",
	"non-negative-int": "int",
	"non-zero-int":     "int",
	"double":           "float",
	"boolean":          "bool",
	"true":             "bool",
	"false":            "bool",
	"non-empty-string": "string",
	"non-falsy-string": "string",
	"truthy-string":    "string",
	"numeric-string":   "string",
	"literal-string":   "string",
	"lowercase-string": "string",
	"uppercase-string": "string",
	"class-string":     "string",
	"interface-string": "string",
	"trait-string":     "string",
	"enum-string":      "string",
	"list":             "array",
	"non-empty-list":   "array",
	"non-empty-array":  "array",
}

// docAllows reports whether an existing `@param` doc type agrees with the
// resolved type. A doc type naming another type, or a union, says the parameter
// takes more than the call sites show, so it is left untyped. No doc tag, or
// `mixed`, carries no such information.
func docAllows(node ast.Vertex, param *ast.Parameter, resolved aggregate.Resolved) bool {
	docType, ok := phpast.DocParamType(node, phpast.VariableName(param.Var))
	if !ok {
		return true
	}
	want := docTypeBase(typeText(resolved.Types[0]))
	for _, member := range phpast.SplitDocUnion(strings.TrimPrefix(strings.TrimSpace(docType), "?")) {
		base := docTypeBase(member)
		if base == "mixed" {
			return true
		}
		if base != "null" && base != want {
			return false
		}
	}
	return true
}

// docTypeBase reduces a single doc type to the native type it narrows:
// generics and shapes are cut, literals and pseudo types map to their scalar,
// and class names to their lowercased short name.
func docTypeBase(member string) string {
	member = strings.TrimSpace(member)
	if index := strings.IndexAny(member, "<{("); index >= 0 {
		member = member[:index]
	}
	if member == "" {
		return ""
	}
	if member[0] == '\'' || member[0] == '"' {
		return "string"
	}
	if _, err := strconv.ParseInt(member, 10, 64); err == nil {
		return "int"
	}
	if _, err := strconv.ParseFloat(member, 64); err == nil {
		return "float"
	}
	if index := strings.LastIndex(member, "\\"); index >= 0 {
		member = member[index+1:]
	}
	member = strings.ToLower(member)
	if native, ok := docPseudoTypes[member]; ok {
		return native
	}
	return member
}

// typeText turns a resolved type into the text written into source. The trailing
// space that separates the type from the variable is added by the caller.
func typeText(resolved valuetype.Type) string {
	if resolved.IsObject() {
		return "\\" + resolved.Class()
	}
	return string(resolved)
}

// typeable reports whether a parameter can receive a type: none yet and not
// variadic, where positions would no longer line up.
func typeable(param *ast.Parameter) bool {
	return param.Type == nil && param.VariadicTkn == nil
}

// methodTypeable reports whether typing this method is safe: it must not change
// the signature of a method inherited from an ancestor. Constructors and private
// methods never override, and a class or enum with no parent or interface has
// nothing to override. Otherwise the inheritance table is consulted, and the
// method is only typed when the whole ancestor chain is resolved and none of it
// declares the method - an unresolved chain (an ancestor in /vendor the scan
// never saw) is treated as unsafe. Other trait methods are skipped, since the
// class using the trait may override a parent method with them.
func (a *applier) methodTypeable(method *ast.StmtClassMethod, class ast.Vertex) bool {
	if strings.EqualFold(phpast.ShortName(method.Name), "__construct") {
		return true
	}
	for _, modifierNode := range method.Modifiers {
		if modifier, ok := modifierNode.(*ast.Identifier); ok {
			if strings.ToLower(string(modifier.Value)) == "private" {
				return true
			}
		}
	}
	switch typed := class.(type) {
	case nil:
		return true
	case *ast.StmtTrait:
		return false
	case *ast.StmtClass:
		if typed.Extends == nil && len(typed.Implements) == 0 {
			return true
		}
	case *ast.StmtEnum:
		if len(typed.Implements) == 0 {
			return true
		}
	}

	classFQCN := a.names[class]
	if classFQCN == "" {
		// cannot identify the class, so fall back to the conservative rule
		return false
	}
	overrides, resolved := a.inheritance.Overrides(classFQCN, phpast.ShortName(method.Name))
	return resolved && !overrides
}

func isMagicExceptConstructor(name string) bool {
	return !strings.EqualFold(name, "__construct") && strings.HasPrefix(name, "__")
}

func hasNullDefault(param *ast.Parameter) bool {
	constFetch, ok := param.DefaultValue.(*ast.ExprConstFetch)
	if !ok {
		return false
	}
	return strings.ToLower(phpast.ShortName(constFetch.Const)) == "null"
}
