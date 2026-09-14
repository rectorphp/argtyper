// Package collect walks call sites and records the literal argument types
// passed into locally resolvable methods, constructors and functions.
package collect

import (
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
	Type       string // "int", "float", "string", "bool", "array", "null" or "object:Short"
}

// FromSource collects records from a single PHP source file. The symbols table
// resolves constant and enum-case argument values. Parse errors return no
// records so one broken file never blocks the rest.
func FromSource(src []byte, table *symbols.Table) []Record {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return nil
	}

	collector := &collector{symbols: table}
	collector.walk(root, scope{})
	return collector.records
}

type collector struct {
	records []Record
	symbols *symbols.Table
}

// scope carries the type information available at a call site: the enclosing
// class name, its property types, the current function's parameter types and
// local variables assigned a `new X()`, so `$this->prop` and typed `$param` or
// `$local` variables resolve to a class.
type scope struct {
	class      string
	properties map[string]string // property name -> short class name
	params     map[string]string // parameter variable name -> short class name
	locals     map[string]string // local variable name -> short class name
}

// walk recurses the AST, tracking the enclosing class (so `$this->` and
// `self::` calls resolve), its property types and the current parameter types.
func (c *collector) walk(node ast.Vertex, sc scope) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		sc = scope{class: phpast.ShortName(typed.Name), properties: classProperties(typed)}
	case *ast.StmtTrait:
		sc = scope{class: phpast.ShortName(typed.Name), properties: classProperties(typed)}
	case *ast.StmtEnum:
		sc = scope{class: phpast.ShortName(typed.Name), properties: classProperties(typed)}
	case *ast.StmtFunction:
		sc.params = paramClasses(typed.Params, sc.class)
		sc.locals = localClasses(typed.Stmts)
	case *ast.StmtClassMethod:
		sc.params = paramClasses(typed.Params, sc.class)
		sc.locals = localClasses(phpast.Children(typed.Stmt))
	case *ast.ExprClosure:
		sc.params = paramClasses(typed.Params, sc.class)
		sc.locals = localClasses(typed.Stmts)
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

// callTarget resolves the short class name a method call is made on: `$this`
// maps to the enclosing class, a typed parameter to its type, and a
// `$this->prop` fetch to the property type. Empty when it cannot be resolved.
func callTarget(varNode ast.Vertex, sc scope) string {
	switch typed := varNode.(type) {
	case *ast.ExprVariable:
		name := phpast.VariableName(varNode)
		if name == "this" {
			return sc.class
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

// classProperties maps the object-typed properties of a class to their short
// class names, from both property declarations and promoted constructor params.
func classProperties(class ast.Vertex) map[string]string {
	enclosing := phpast.ShortName(classNameNode(class))
	properties := map[string]string{}

	for _, member := range phpast.Children(class) {
		switch typed := member.(type) {
		case *ast.StmtPropertyList:
			className := classFromType(typed.Type, enclosing)
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
			for name, className := range promotedProperties(typed.Params, enclosing) {
				properties[name] = className
			}
		}
	}

	return properties
}

// promotedProperties collects constructor parameters marked with a visibility
// modifier, which PHP turns into typed class properties.
func promotedProperties(params []ast.Vertex, enclosing string) map[string]string {
	properties := map[string]string{}
	for _, paramNode := range params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || len(param.Modifiers) == 0 {
			continue
		}
		className := classFromType(param.Type, enclosing)
		if className == "" {
			continue
		}
		if name := phpast.VariableName(param.Var); name != "" {
			properties[name] = className
		}
	}
	return properties
}

// paramClasses maps object-typed parameters of a function to their short class
// names, so method calls on those variables resolve to a class.
func paramClasses(params []ast.Vertex, enclosing string) map[string]string {
	classes := map[string]string{}
	for _, paramNode := range params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok {
			continue
		}
		className := classFromType(param.Type, enclosing)
		if className == "" {
			continue
		}
		if name := phpast.VariableName(param.Var); name != "" {
			classes[name] = className
		}
	}
	return classes
}

// classFromType returns the short class name of a type node, unwrapping a
// nullable and resolving self/static to the enclosing class. Empty for builtin
// scalar types, union/intersection types and unresolvable parent.
func classFromType(typeNode ast.Vertex, enclosing string) string {
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
	return name
}

// classNameNode returns the name node of a class/trait/enum declaration.
func classNameNode(class ast.Vertex) ast.Vertex {
	switch typed := class.(type) {
	case *ast.StmtClass:
		return typed.Name
	case *ast.StmtTrait:
		return typed.Name
	case *ast.StmtEnum:
		return typed.Name
	}
	return nil
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

		typeName := c.argType(arg.Expr, sc)
		if typeName == "" {
			continue
		}

		record := base
		record.Position = position
		record.Type = typeName
		c.records = append(c.records, record)
	}
}

// argType returns the type of an argument value: a typed variable or property
// resolves to its class, otherwise the symbols table handles literals,
// constants and enum cases.
func (c *collector) argType(expr ast.Vertex, sc scope) string {
	if class := argClass(expr, sc); class != "" {
		return "object:" + class
	}
	return c.symbols.TypeOfExpr(expr, sc.class)
}

// argClass resolves the class of a variable or `$this->prop` argument: `$this`,
// a typed parameter, a `new X()` local, or a typed property. Empty otherwise.
func argClass(expr ast.Vertex, sc scope) string {
	switch typed := expr.(type) {
	case *ast.ExprVariable:
		name := phpast.VariableName(expr)
		if name == "this" {
			return sc.class
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

// localClasses maps local variables assigned a `new X()` to their short class
// name, so those variables resolve as arguments and call targets.
func localClasses(stmts []ast.Vertex) map[string]string {
	locals := map[string]string{}
	for _, stmt := range stmts {
		collectLocals(stmt, locals)
	}
	return locals
}

func collectLocals(node ast.Vertex, locals map[string]string) {
	if node == nil {
		return
	}
	if assign, ok := node.(*ast.ExprAssign); ok {
		if name := phpast.VariableName(assign.Var); name != "" {
			if newExpr, ok := assign.Expr.(*ast.ExprNew); ok {
				if class := phpast.ShortName(newExpr.Class); class != "" {
					locals[name] = class
				}
			}
		}
	}
	for _, child := range phpast.Children(node) {
		collectLocals(child, locals)
	}
}
