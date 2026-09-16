// Package apply fills missing parameter types in function, method and
// constructor definitions from the resolved argument types.
package apply

import (
	"strings"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/inherit"
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
	"github.com/rectorphp/php-parser-in-go/pkg/token"
)

// Source adds parameter types to a single PHP source file. It returns the new
// source, the type declarations added (their written text, e.g. "int" or
// "?string" or "\Foo"), and whether the file changed. On a parse error the
// original source is returned unchanged. The symbols table resolves constant
// and enum-case default values; the inheritance table decides whether typing a
// method would change an inherited signature.
func Source(src []byte, types aggregate.Types, table *symbols.Table, inheritance *inherit.Table) (string, []string, bool) {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return string(src), nil, false
	}

	applier := &applier{types: types, symbols: table, inheritance: inheritance, names: phpast.ResolveNames(root)}
	applier.walk(root, nil)

	if len(applier.added) == 0 {
		return string(src), nil, false
	}

	return phpast.Print(root), applier.added, true
}

type applier struct {
	types       aggregate.Types
	symbols     *symbols.Table
	inheritance *inherit.Table
	names       map[ast.Vertex]string
	added       []string
}

func (a *applier) walk(node ast.Vertex, class *ast.StmtClass) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
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
	added := map[string]string{}
	for position, paramNode := range function.Params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || !typeable(param) {
			continue
		}
		if resolved, ok := a.types.Function(name, position); ok {
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
			continue
		}
		if resolved, ok := a.defaultType(param, "", ""); ok {
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
		}
	}
	phpast.StripRedundantDocParams(function, added)
}

func (a *applier) applyMethod(method *ast.StmtClassMethod, class *ast.StmtClass) {
	name := phpast.ShortName(method.Name)
	if isMagicExceptConstructor(name) {
		return
	}
	if !a.methodTypeable(method, class) {
		return
	}

	className := ""
	classFQCN := ""
	if class != nil {
		className = phpast.ShortName(class.Name)
		classFQCN = a.names[class]
	}

	added := map[string]string{}
	for position, paramNode := range method.Params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || !typeable(param) {
			continue
		}
		if resolved, ok := a.types.Method(className, name, position); ok {
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
			continue
		}
		if resolved, ok := a.defaultType(param, className, classFQCN); ok {
			added[phpast.VariableName(param.Var)] = a.setType(param, resolved)
		}
	}
	phpast.StripRedundantDocParams(method, added)
}

// defaultType infers a parameter type from its literal default value, so
// `$page = 1` becomes `int $page = 1` even with no call site. A `null` default
// carries no type of its own; enclosing (short name) resolves `self::` constants
// and enclosingFQCN qualifies an object default such as an enum case.
func (a *applier) defaultType(param *ast.Parameter, enclosing, enclosingFQCN string) (aggregate.Resolved, bool) {
	if param.DefaultValue == nil {
		return aggregate.Resolved{}, false
	}
	typeName := a.symbols.TypeOfExpr(param.DefaultValue, enclosing)
	if typeName == "" || typeName == "null" {
		return aggregate.Resolved{}, false
	}
	if strings.HasPrefix(typeName, "object:") {
		if fqcn := a.objectFQCN(param.DefaultValue, enclosingFQCN); fqcn != "" {
			typeName = "object:" + fqcn
		}
	}
	return aggregate.Resolved{Types: []string{typeName}}, true
}

// objectFQCN qualifies the class of a `new X()` or `X::CASE` default value,
// mapping self/static to the enclosing class. Empty when it cannot be resolved.
func (a *applier) objectFQCN(expr ast.Vertex, enclosingFQCN string) string {
	classNode := phpast.ObjectClassNode(expr)
	if classNode == nil {
		return ""
	}
	switch strings.ToLower(phpast.ShortName(classNode)) {
	case "self", "static":
		return enclosingFQCN
	case "parent":
		return ""
	}
	return a.names[classNode]
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

// typeText turns a resolved type into the text written into source. The trailing
// space that separates the type from the variable is added by the caller.
func typeText(resolved string) string {
	if after, ok := strings.CutPrefix(resolved, "object:"); ok {
		return "\\" + after
	}
	return resolved
}

// typeable reports whether a parameter can receive a type: none yet and not
// variadic, where positions would no longer line up.
func typeable(param *ast.Parameter) bool {
	return param.Type == nil && param.VariadicTkn == nil
}

// methodTypeable reports whether typing this method is safe: it must not change
// the signature of a method inherited from an ancestor. Constructors and private
// methods never override, and a class with no parent or interface has nothing to
// override. Otherwise the inheritance table is consulted, and the method is only
// typed when the whole ancestor chain is resolved and none of it declares the
// method - an unresolved chain (an ancestor in /vendor the scan never saw) is
// treated as unsafe.
func (a *applier) methodTypeable(method *ast.StmtClassMethod, class *ast.StmtClass) bool {
	if phpast.ShortName(method.Name) == "__construct" {
		return true
	}
	for _, modifierNode := range method.Modifiers {
		if modifier, ok := modifierNode.(*ast.Identifier); ok {
			if strings.ToLower(string(modifier.Value)) == "private" {
				return true
			}
		}
	}
	if class == nil {
		return true
	}
	if class.Extends == nil && len(class.Implements) == 0 {
		return true
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
	return name != "__construct" && strings.HasPrefix(name, "__")
}

func hasNullDefault(param *ast.Parameter) bool {
	constFetch, ok := param.DefaultValue.(*ast.ExprConstFetch)
	if !ok {
		return false
	}
	return strings.ToLower(phpast.ShortName(constFetch.Const)) == "null"
}
