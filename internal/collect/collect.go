// Package collect walks call sites and records the literal argument types
// passed into locally resolvable methods, constructors and functions.
package collect

import (
	"strings"

	"github.com/rectorphp/argtyper/internal/phpast"
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

// FromSource collects records from a single PHP source file. Parse errors
// return no records so one broken file never blocks the rest.
func FromSource(src []byte) []Record {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return nil
	}

	collector := &collector{}
	collector.walk(root, "")
	return collector.records
}

type collector struct {
	records []Record
}

// walk recurses the AST, tracking the short name of the enclosing
// class/trait/enum so `$this->` and `self::` calls resolve.
func (c *collector) walk(node ast.Vertex, class string) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		class = phpast.ShortName(typed.Name)
	case *ast.StmtTrait:
		class = phpast.ShortName(typed.Name)
	case *ast.StmtEnum:
		class = phpast.ShortName(typed.Name)
	}

	c.visit(node, class)

	for _, child := range phpast.Children(node) {
		c.walk(child, class)
	}
}

func (c *collector) visit(node ast.Vertex, class string) {
	switch typed := node.(type) {
	case *ast.ExprFunctionCall:
		name := phpast.ShortName(typed.Function)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{IsFunction: true, Name: name})

	case *ast.ExprNew:
		name := phpast.ShortName(typed.Class)
		if name == "" {
			return
		}
		c.record(typed.Args, Record{Class: name, Name: "__construct"})

	case *ast.ExprStaticCall:
		method, ok := typed.Call.(*ast.Identifier)
		if !ok {
			return
		}
		target := staticClassName(typed.Class, class)
		if target == "" {
			return
		}
		c.record(typed.Args, Record{Class: target, Name: string(method.Value)})

	case *ast.ExprMethodCall:
		if !phpast.IsThisVariable(typed.Var) || class == "" {
			return
		}
		method, ok := typed.Method.(*ast.Identifier)
		if !ok {
			return
		}
		c.record(typed.Args, Record{Class: class, Name: string(method.Value)})

	case *ast.ExprNullsafeMethodCall:
		if !phpast.IsThisVariable(typed.Var) || class == "" {
			return
		}
		method, ok := typed.Method.(*ast.Identifier)
		if !ok {
			return
		}
		c.record(typed.Args, Record{Class: class, Name: string(method.Value)})
	}
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

func (c *collector) record(args []ast.Vertex, base Record) {
	for position, argNode := range args {
		arg, ok := argNode.(*ast.Argument)
		if !ok {
			continue
		}
		// skip named and variadic arguments, positions no longer line up
		if arg.Name != nil || arg.VariadicTkn != nil {
			continue
		}

		typeName := literalType(arg.Expr)
		if typeName == "" {
			continue
		}

		record := base
		record.Position = position
		record.Type = typeName
		c.records = append(c.records, record)
	}
}

// literalType returns the type of a literal argument expression, or "" when
// the value is not a literal we can infer without a type engine.
func literalType(expr ast.Vertex) string {
	switch typed := expr.(type) {
	case *ast.ScalarLnumber:
		return "int"
	case *ast.ScalarDnumber:
		return "float"
	case *ast.ScalarString, *ast.ScalarEncapsed, *ast.ScalarHeredoc:
		return "string"
	case *ast.ExprArray:
		return "array"
	case *ast.ExprUnaryMinus:
		return numericType(typed.Expr)
	case *ast.ExprUnaryPlus:
		return numericType(typed.Expr)
	case *ast.ExprConstFetch:
		switch strings.ToLower(phpast.ShortName(typed.Const)) {
		case "true", "false":
			return "bool"
		case "null":
			return "null"
		}
		return ""
	case *ast.ExprNew:
		name := phpast.ShortName(typed.Class)
		if name == "" {
			return ""
		}
		return "object:" + name
	default:
		return ""
	}
}

func numericType(expr ast.Vertex) string {
	switch expr.(type) {
	case *ast.ScalarLnumber:
		return "int"
	case *ast.ScalarDnumber:
		return "float"
	default:
		return ""
	}
}
