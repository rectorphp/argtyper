// Package collect walks call sites and records the literal argument types
// passed into locally resolvable methods, constructors and functions.
package collect

import (
	"maps"
	"strings"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/argtyper/internal/valuetype"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// Record is a single argument type observed at a call site.
type Record struct {
	IsFunction bool           // true for a plain function call, false for a method/constructor
	Class      string         // fully qualified class name for method/constructor calls, short name when unresolved
	Name       string         // method or function name
	Position   int            // zero-based positional argument index
	Type       valuetype.Type // a scalar, callable or object of a fully qualified class; Unknown when unresolved
	IsLiteral  bool           // true when the argument is a plain string literal, see phpast.StringLiteral
	Literal    string         // the string literal value, when IsLiteral
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
// `$local` variables resolve to a class. Class names are stored fully qualified,
// both for written types and call keys.
type scope struct {
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
	case *ast.StmtClass, *ast.StmtTrait, *ast.StmtEnum:
		sc = c.classScope(typed)
	case *ast.StmtFunction:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(typed.Stmts, typed.Params, sc.classFQCN)
	case *ast.StmtClassMethod:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(phpast.Children(typed.Stmt), typed.Params, sc.classFQCN)
	case *ast.ExprClosure:
		sc.params = c.paramClasses(typed.Params, sc.classFQCN)
		sc.locals = c.localClasses(typed.Stmts, typed.Params, sc.classFQCN)
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
	switch typed := node.(type) {
	case *ast.ExprFunctionCall:
		name := phpast.ShortName(typed.Function)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{IsFunction: true, Name: name}, sc)

	case *ast.ExprNew:
		name := c.staticClassName(typed.Class, sc.classFQCN)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{Class: name, Name: "__construct"}, sc)

	case *ast.ExprStaticCall:
		method, ok := typed.Call.(*ast.Identifier)
		if !ok {
			return
		}
		target := c.staticClassName(typed.Class, sc.classFQCN)
		if target == "" {
			return
		}
		c.record(typed.Args, Record{Class: target, Name: string(method.Value)}, sc)

	case *ast.ExprMethodCall:
		c.recordMethodCall(typed.Var, typed.Method, typed.Args, sc)

	case *ast.ExprNullsafeMethodCall:
		c.recordMethodCall(typed.Var, typed.Method, typed.Args, sc)
	}
}

// recordMethodCall records the arguments of a `$var->method()` call whose target
// class and method name are statically known.
func (c *collector) recordMethodCall(varNode, methodNode ast.Vertex, args []ast.Vertex, sc scope) {
	target := argClass(varNode, sc)
	if target == "" {
		return
	}
	method, ok := methodNode.(*ast.Identifier)
	if !ok {
		return
	}
	c.record(args, Record{Class: target, Name: string(method.Value)}, sc)
}

// classScope builds the scope for a class/trait/enum: its fully qualified name
// for call keys and typing `$this`, and its property types.
func (c *collector) classScope(class ast.Vertex) scope {
	classFQCN := phpast.ResolvedName(c.names, class)
	return scope{
		classFQCN:  classFQCN,
		properties: c.classProperties(class, classFQCN),
	}
}

// staticClassName resolves the class of a static call or `new`: self/static map
// to the enclosing class, parent is unresolvable, anything else is resolved from
// the file's namespace and imports.
func (c *collector) staticClassName(classNode ast.Vertex, enclosing string) string {
	switch strings.ToLower(phpast.ShortName(classNode)) {
	case "self", "static":
		return enclosing
	case "parent":
		return ""
	default:
		return phpast.ResolvedName(c.names, classNode)
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
			if isLiteral && typeName == valuetype.String {
				record.IsLiteral = true
				record.Literal = literal
			}
			c.records = append(c.records, record)
		}
	}
}

// argTypes returns the type(s) an argument contributes. A coalesce expression
// (`$x ?? null`) contributes the types of both sides, so `... ?? null` makes the
// parameter nullable; every other expression contributes one type. A value that
// cannot be resolved contributes an empty type, so the aggregate knows not every
// argument was seen.
func (c *collector) argTypes(expr ast.Vertex, sc scope) []valuetype.Type {
	if coalesce, ok := expr.(*ast.ExprBinaryCoalesce); ok {
		return append(c.argTypes(coalesce.Left, sc), c.argTypes(coalesce.Right, sc)...)
	}
	return []valuetype.Type{c.argType(expr, sc)}
}

// argType returns the type of an argument value as a fully qualified object
// type or a scalar keyword. A typed variable or property resolves to its class;
// otherwise the symbols table handles literals, constants and enum cases, and an
// object result (`new X()`, an enum case) is qualified from the class node.
func (c *collector) argType(expr ast.Vertex, sc scope) valuetype.Type {
	if class := argClass(expr, sc); class != "" {
		return valuetype.Object(class)
	}

	switch expr.(type) {
	case *ast.ExprMethodCall, *ast.ExprNullsafeMethodCall:
		if class := c.newChainClass(expr, sc.classFQCN); class != "" {
			return valuetype.Object(class)
		}
	}

	typeName := c.symbols.TypeOfExpr(expr, sc.classFQCN, c.names)
	if !typeName.IsObject() {
		return typeName
	}
	if fqcn := phpast.ObjectFQCN(expr, c.names, sc.classFQCN); fqcn != "" {
		return valuetype.Object(fqcn)
	}
	// `new parent()`, or `new self()` outside a class, has no known class
	if newExpr, ok := expr.(*ast.ExprNew); ok && c.staticClassName(newExpr.Class, sc.classFQCN) == "" {
		return valuetype.Unknown
	}
	return typeName
}

// newChainClass returns the fully qualified class of the object a method chain
// is rooted at, when that root is a `new X()`. Fluent methods are assumed to
// return their receiver, so `new X()->modify(...)` has type X, and self/static
// map to the enclosing class. Empty when the chain is not rooted at a new, or
// the class is parent.
func (c *collector) newChainClass(expr ast.Vertex, enclosing string) string {
	for {
		switch typed := expr.(type) {
		case *ast.ExprMethodCall:
			expr = typed.Var
		case *ast.ExprNullsafeMethodCall:
			expr = typed.Var
		case *ast.ExprNew:
			return c.staticClassName(typed.Class, enclosing)
		default:
			return ""
		}
	}
}

// argClass resolves the fully qualified class of a variable or `$this->prop`
// argument or method-call target: `$this`, a typed parameter, a `new X()`
// local, or a typed property.
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
		return thisPropertyClass(typed.Var, typed.Prop, sc)
	case *ast.ExprNullsafePropertyFetch:
		return thisPropertyClass(typed.Var, typed.Prop, sc)
	}
	return ""
}

// thisPropertyClass resolves the class of a `$this->prop` fetch from the
// enclosing class's property types. Empty for any other fetch.
func thisPropertyClass(varNode, propNode ast.Vertex, sc scope) string {
	if !phpast.IsThisVariable(varNode) {
		return ""
	}
	return sc.properties[phpast.ShortName(propNode)]
}

// localClasses maps local variables assigned a `new X()` to their fully
// qualified name, so those variables resolve as arguments and call targets.
// Parameters are excluded: a reassignment like `$param = new X()` must not
// retype the parameter's earlier uses, since the tracking is not flow sensitive.
// enclosing is the class that `new self()`/`new static()` resolve to.
func (c *collector) localClasses(stmts, params []ast.Vertex, enclosing string) map[string]string {
	excluded := paramNames(params)
	locals := map[string]string{}
	for _, stmt := range stmts {
		c.collectLocals(stmt, locals, excluded, enclosing)
	}
	return locals
}

func (c *collector) collectLocals(node ast.Vertex, locals map[string]string, excluded map[string]bool, enclosing string) {
	if node == nil {
		return
	}
	if assign, ok := node.(*ast.ExprAssign); ok {
		if name := phpast.VariableName(assign.Var); name != "" && !excluded[name] {
			if newExpr, ok := assign.Expr.(*ast.ExprNew); ok {
				if class := c.staticClassName(newExpr.Class, enclosing); class != "" {
					locals[name] = class
				}
			}
		}
	}
	for _, child := range phpast.Children(node) {
		c.collectLocals(child, locals, excluded, enclosing)
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
