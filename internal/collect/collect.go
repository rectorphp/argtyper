// Package collect walks call sites and records the literal argument types
// passed into locally resolvable methods, constructors and functions.
package collect

import (
	"maps"
	"strings"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// Record is a single argument type observed at a call site.
type Record struct {
	IsFunction bool   // true for a plain function call, false for a method/constructor
	Class      string // short class name for method/constructor calls
	Name       string // method or function name
	Position   int    // zero-based positional argument index
	Type       string // "int", "float", "string", "bool", "array", "null" or "object:Fqcn"
	IsLiteral  bool   // true when the argument is a plain string literal, see phpast.StringLiteral
	Literal    string // the string literal value, when IsLiteral
}

// FromSource collects records from a single PHP source file. The symbols table
// resolves constant and enum-case argument values. Parse errors return no
// records so one broken file never blocks the rest.
func FromSource(src []byte, table *symbols.Table) []Record {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return nil
	}

	collector := &collector{symbols: table, names: phpast.ResolveNames(root)}
	collector.walk(root, scope{})
	return collector.records
}

type collector struct {
	records []Record
	symbols *symbols.Table
	names   map[ast.Vertex]string // class-name node -> fully qualified name
}

// scope carries the type information available at a call site: the enclosing
// class name, its property types, the current function's parameter types and
// local variables assigned a `new X()`, so `$this->prop` and typed `$param` or
// `$local` variables resolve to a class. Class names are stored fully qualified
// so a written type is absolute; call keys use the short name (see shortName).
type scope struct {
	class      string
	classFQCN  string
	properties map[string]string // property name -> fully qualified class name
	params     map[string]string // parameter variable name -> fully qualified class name
	locals     map[string]string // local variable name -> fully qualified class name
}

// walk recurses the AST, tracking the enclosing class (so `$this->` and
// `self::` calls resolve), its property types and the current parameter types.
func (c *collector) walk(node ast.Vertex, sc scope) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		sc = c.classScope(typed, typed.Name)
	case *ast.StmtTrait:
		sc = c.classScope(typed, typed.Name)
	case *ast.StmtEnum:
		sc = c.classScope(typed, typed.Name)
	case *ast.StmtFunction:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(typed.Stmts, typed.Params)
	case *ast.StmtClassMethod:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(phpast.Children(typed.Stmt), typed.Params)
	case *ast.ExprClosure:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(typed.Stmts, typed.Params)
	case *ast.ExprArrowFunction:
		// arrow functions capture outer variables, so keep the inherited params
		// and locals and overlay the arrow's own typed parameters.
		sc.params = merge(sc.params, c.paramClasses(typed.Params, sc.classFQCN))
	}

	c.visit(node, sc)

	for _, child := range phpast.Children(node) {
		c.walk(child, sc)
	}
}

func (c *collector) visit(node ast.Vertex, sc scope) {
	class := sc.class
	switch typed := node.(type) {
	case *ast.ExprFunctionCall:
		name := phpast.ShortName(typed.Function)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{IsFunction: true, Name: name}, sc)

	case *ast.ExprNew:
		name := phpast.ShortName(typed.Class)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{Class: name, Name: "__construct"}, sc)

	case *ast.ExprStaticCall:
		method, ok := typed.Call.(*ast.Identifier)
		if !ok {
			return
		}
		target := staticClassName(typed.Class, class)
		if target == "" {
			return
		}
		c.record(typed.Args, Record{Class: target, Name: string(method.Value)}, sc)

	case *ast.ExprMethodCall:
		target := callTarget(typed.Var, sc)
		if target == "" {
			return
		}
		method, ok := typed.Method.(*ast.Identifier)
		if !ok {
			return
		}
		c.record(typed.Args, Record{Class: target, Name: string(method.Value)}, sc)

	case *ast.ExprNullsafeMethodCall:
		target := callTarget(typed.Var, sc)
		if target == "" {
			return
		}
		method, ok := typed.Method.(*ast.Identifier)
		if !ok {
			return
		}
		c.record(typed.Args, Record{Class: target, Name: string(method.Value)}, sc)
	}
}

// classScope builds the scope for a class/trait/enum: its short name for call
// keys, its fully qualified name for typing `$this`, and its property types.
func (c *collector) classScope(class ast.Vertex, nameNode ast.Vertex) scope {
	return scope{
		class:      phpast.ShortName(nameNode),
		classFQCN:  c.names[class],
		properties: c.classProperties(class, c.names[class]),
	}
}

// callTarget resolves the short class name a method call is made on, used as the
// method's lookup key: `$this` maps to the enclosing class, a typed parameter or
// `new X()` local to its type, and a `$this->prop` fetch to the property type.
func callTarget(varNode ast.Vertex, sc scope) string {
	switch typed := varNode.(type) {
	case *ast.ExprVariable:
		name := phpast.VariableName(varNode)
		if name == "this" {
			return sc.class
		}
		if class := sc.params[name]; class != "" {
			return shortName(class)
		}
		return shortName(sc.locals[name])
	case *ast.ExprPropertyFetch:
		if phpast.IsThisVariable(typed.Var) {
			return shortName(sc.properties[phpast.ShortName(typed.Prop)])
		}
	case *ast.ExprNullsafePropertyFetch:
		if phpast.IsThisVariable(typed.Var) {
			return shortName(sc.properties[phpast.ShortName(typed.Prop)])
		}
	}
	return ""
}

// staticClassName resolves the class of a static call: self/static map to the
// enclosing class, parent is unresolvable, anything else is a plain name.
func staticClassName(classNode ast.Vertex, enclosing string) string {
	name := phpast.ShortName(classNode)
	switch strings.ToLower(name) {
	case "self", "static":
		return enclosing
	case "parent":
		return ""
	default:
		return name
	}
}

// classProperties maps the object-typed properties of a class to their fully
// qualified names, from both property declarations and promoted constructor
// params. enclosing is the class's own name, for `self`/`static` types.
func (c *collector) classProperties(class ast.Vertex, enclosing string) map[string]string {
	properties := map[string]string{}

	for _, member := range phpast.Children(class) {
		switch typed := member.(type) {
		case *ast.StmtPropertyList:
			className := c.classFromType(typed.Type, enclosing)
			if className == "" {
				continue
			}
			for _, propNode := range typed.Props {
				property, ok := propNode.(*ast.StmtProperty)
				if !ok {
					continue
				}
				if name := phpast.VariableName(property.Var); name != "" {
					properties[name] = className
				}
			}
		case *ast.StmtClassMethod:
			if phpast.ShortName(typed.Name) != "__construct" {
				continue
			}
			maps.Copy(properties, c.promotedProperties(typed.Params, enclosing))
		}
	}

	return properties
}

// promotedProperties collects constructor parameters marked with a visibility
// modifier, which PHP turns into typed class properties.
func (c *collector) promotedProperties(params []ast.Vertex, enclosing string) map[string]string {
	properties := map[string]string{}
	for _, paramNode := range params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || len(param.Modifiers) == 0 {
			continue
		}
		className := c.classFromType(param.Type, enclosing)
		if className == "" {
			continue
		}
		if name := phpast.VariableName(param.Var); name != "" {
			properties[name] = className
		}
	}
	return properties
}

// paramClasses maps object-typed parameters of a function to their fully
// qualified names, so those variables resolve to a class as arguments and
// method-call targets.
func (c *collector) paramClasses(params []ast.Vertex, enclosing string) map[string]string {
	classes := map[string]string{}
	for _, paramNode := range params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok {
			continue
		}
		className := c.classFromType(param.Type, enclosing)
		if className == "" {
			continue
		}
		if name := phpast.VariableName(param.Var); name != "" {
			classes[name] = className
		}
	}
	return classes
}

// classFromType returns the fully qualified name of a type node, unwrapping a
// nullable and resolving self/static to the enclosing class. Empty for builtin
// scalar types, union/intersection types and unresolvable parent.
func (c *collector) classFromType(typeNode ast.Vertex, enclosing string) string {
	nullable, ok := typeNode.(*ast.Nullable)
	if ok {
		typeNode = nullable.Expr
	}

	switch typeNode.(type) {
	case *ast.Name, *ast.NameFullyQualified, *ast.NameRelative, *ast.Identifier:
	default:
		return ""
	}

	name := phpast.ShortName(typeNode)
	switch strings.ToLower(name) {
	case "self", "static":
		return enclosing
	case "parent", "int", "float", "string", "bool", "false", "true", "null",
		"array", "iterable", "callable", "object", "mixed", "void", "never":
		return ""
	}
	if fqcn := c.names[typeNode]; fqcn != "" {
		return fqcn
	}
	return name
}

// merge overlays the over map onto a copy of base, with over winning on clashes.
func merge(base, over map[string]string) map[string]string {
	if len(over) == 0 {
		return base
	}
	merged := make(map[string]string, len(base)+len(over))
	maps.Copy(merged, base)
	maps.Copy(merged, over)
	return merged
}

// shortName returns the last segment of a fully qualified name, the form used
// as a method lookup key.
func shortName(fqcn string) string {
	if index := strings.LastIndex(fqcn, "\\"); index >= 0 {
		return fqcn[index+1:]
	}
	return fqcn
}

func (c *collector) record(args []ast.Vertex, base Record, sc scope) {
	for position, argNode := range args {
		arg, ok := argNode.(*ast.Argument)
		if !ok {
			continue
		}
		// skip named and variadic arguments, positions no longer line up
		if arg.Name != nil || arg.VariadicTkn != nil {
			continue
		}

		literal, isLiteral := phpast.StringLiteral(arg.Expr)
		for _, typeName := range c.argTypes(arg.Expr, sc) {
			record := base
			record.Position = position
			record.Type = typeName
			if isLiteral && typeName == "string" {
				record.IsLiteral = true
				record.Literal = literal
			}
			c.records = append(c.records, record)
		}
	}
}

// argTypes returns the type(s) an argument contributes. A coalesce expression
// (`$x ?? null`) contributes the types of both sides, so `... ?? null` makes the
// parameter nullable; every other expression contributes at most one type.
func (c *collector) argTypes(expr ast.Vertex, sc scope) []string {
	if coalesce, ok := expr.(*ast.ExprBinaryCoalesce); ok {
		return append(c.argTypes(coalesce.Left, sc), c.argTypes(coalesce.Right, sc)...)
	}
	if typeName := c.argType(expr, sc); typeName != "" {
		return []string{typeName}
	}
	return nil
}

// argType returns the type of an argument value as a fully qualified object
// type or a scalar keyword. A typed variable or property resolves to its class;
// otherwise the symbols table handles literals, constants and enum cases, and an
// object result (`new X()`, an enum case) is qualified from the class node.
func (c *collector) argType(expr ast.Vertex, sc scope) string {
	if class := argClass(expr, sc); class != "" {
		return "object:" + class
	}

	switch expr.(type) {
	case *ast.ExprMethodCall, *ast.ExprNullsafeMethodCall:
		if class := c.newChainClass(expr); class != "" {
			return "object:" + class
		}
	}

	typeName := c.symbols.TypeOfExpr(expr, sc.class)
	if !strings.HasPrefix(typeName, "object:") {
		return typeName
	}
	if fqcn := c.objectFQCN(expr, sc); fqcn != "" {
		return "object:" + fqcn
	}
	return typeName
}

// objectFQCN qualifies the class of a `new X()` or `X::CASE` value, mapping
// self/static to the enclosing class. Empty when it cannot be resolved.
func (c *collector) objectFQCN(expr ast.Vertex, sc scope) string {
	classNode := phpast.ObjectClassNode(expr)
	if classNode == nil {
		return ""
	}
	switch strings.ToLower(phpast.ShortName(classNode)) {
	case "self", "static":
		return sc.classFQCN
	case "parent":
		return ""
	}
	return c.names[classNode]
}

// newChainClass returns the fully qualified class of the object a method chain
// is rooted at, when that root is a `new X()`. Fluent methods are assumed to
// return their receiver, so `new X()->modify(...)` has type X. Empty when the
// chain is not rooted at a new, or the class is self/static/parent.
func (c *collector) newChainClass(expr ast.Vertex) string {
	for {
		switch typed := expr.(type) {
		case *ast.ExprMethodCall:
			expr = typed.Var
		case *ast.ExprNullsafeMethodCall:
			expr = typed.Var
		case *ast.ExprNew:
			switch strings.ToLower(phpast.ShortName(typed.Class)) {
			case "", "self", "static", "parent":
				return ""
			}
			if fqcn := c.names[typed.Class]; fqcn != "" {
				return fqcn
			}
			return phpast.ShortName(typed.Class)
		default:
			return ""
		}
	}
}

// argClass resolves the fully qualified class of a variable or `$this->prop`
// argument: `$this`, a typed parameter, a `new X()` local, or a typed property.
func argClass(expr ast.Vertex, sc scope) string {
	switch typed := expr.(type) {
	case *ast.ExprVariable:
		name := phpast.VariableName(expr)
		if name == "this" {
			return sc.classFQCN
		}
		if class := sc.params[name]; class != "" {
			return class
		}
		return sc.locals[name]
	case *ast.ExprPropertyFetch:
		if phpast.IsThisVariable(typed.Var) {
			return sc.properties[phpast.ShortName(typed.Prop)]
		}
	case *ast.ExprNullsafePropertyFetch:
		if phpast.IsThisVariable(typed.Var) {
			return sc.properties[phpast.ShortName(typed.Prop)]
		}
	}
	return ""
}

// localClasses maps local variables assigned a `new X()` to their fully
// qualified name, so those variables resolve as arguments and call targets.
// Parameters are excluded: a reassignment like `$param = new X()` must not
// retype the parameter's earlier uses, since the tracking is not flow sensitive.
func (c *collector) localClasses(stmts, params []ast.Vertex) map[string]string {
	excluded := paramNames(params)
	locals := map[string]string{}
	for _, stmt := range stmts {
		c.collectLocals(stmt, locals, excluded)
	}
	return locals
}

func (c *collector) collectLocals(node ast.Vertex, locals map[string]string, excluded map[string]bool) {
	if node == nil {
		return
	}
	if assign, ok := node.(*ast.ExprAssign); ok {
		if name := phpast.VariableName(assign.Var); name != "" && !excluded[name] {
			if newExpr, ok := assign.Expr.(*ast.ExprNew); ok {
				if fqcn := c.names[newExpr.Class]; fqcn != "" {
					locals[name] = fqcn
				} else if class := phpast.ShortName(newExpr.Class); class != "" {
					locals[name] = class
				}
			}
		}
	}
	for _, child := range phpast.Children(node) {
		c.collectLocals(child, locals, excluded)
	}
}

// paramNames returns the set of a function's parameter variable names.
func paramNames(params []ast.Vertex) map[string]bool {
	names := map[string]bool{}
	for _, paramNode := range params {
		if param, ok := paramNode.(*ast.Parameter); ok {
			if name := phpast.VariableName(param.Var); name != "" {
				names[name] = true
			}
		}
	}
	return names
}
