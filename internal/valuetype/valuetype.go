// Package valuetype infers the type of a self-contained value expression:
// literals, closures, `::class`, and calls to builtin functions with a known
// return type. Anything needing project knowledge (constants, enum cases) is
// resolved by the symbols package, which falls back here.
package valuetype

import (
	"strings"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// Of returns the type of a value expression (a scalar, callable or an object of
// a short class name), or Unknown when the value is not one we can infer without
// more context.
func Of(expr ast.Vertex) Type {
	switch typed := expr.(type) {
	case *ast.ScalarLnumber:
		return Int
	case *ast.ScalarDnumber:
		return Float
	case *ast.ScalarString, *ast.ScalarEncapsed, *ast.ScalarHeredoc:
		return String
	case *ast.ExprArray:
		return Array
	case *ast.ExprClosure, *ast.ExprArrowFunction:
		// a closure argument means the parameter takes a callable, which can be
		// many things (a closure, a "func" string, a [$obj, method] array, a
		// first-class callable). "callable" is a poison type: it is never
		// written, and it stops the parameter from being typed at all.
		return Callable
	case *ast.ExprUnaryMinus:
		return numericType(typed.Expr)
	case *ast.ExprUnaryPlus:
		return numericType(typed.Expr)
	case *ast.ExprConstFetch:
		switch strings.ToLower(phpast.ShortName(typed.Const)) {
		case "true", "false":
			return Bool
		case "null":
			return Null
		}
		return Unknown
	case *ast.ExprClassConstFetch:
		// `Foo::class` is a string; other class constants need the symbols table.
		if strings.EqualFold(phpast.ShortName(typed.Const), "class") {
			return String
		}
		return Unknown
	case *ast.ExprFunctionCall:
		return builtinReturns[strings.ToLower(phpast.ShortName(typed.Function))]
	case *ast.ExprNew:
		name := phpast.ShortName(typed.Class)
		if name == "" {
			return Unknown
		}
		return Object(name)
	default:
		return Unknown
	}
}

// builtinReturns maps builtin functions to their return type, limited to ones
// that always return a single scalar type (no `string|false` cases).
var builtinReturns = map[string]Type{
	"strlen":    Int,
	"count":     Int,
	"sizeof":    Int,
	"mb_strlen": Int,
	"intval":    Int,
	"intdiv":    Int,

	"strval":        String,
	"sprintf":       String,
	"vsprintf":      String,
	"implode":       String,
	"join":          String,
	"str_repeat":    String,
	"strtolower":    String,
	"strtoupper":    String,
	"mb_strtolower": String,
	"mb_strtoupper": String,
	"ucfirst":       String,
	"lcfirst":       String,
	"ucwords":       String,
	"trim":          String,
	"ltrim":         String,
	"rtrim":         String,
	"number_format": String,

	"floatval": Float,
	"floor":    Float,
	"ceil":     Float,
	"round":    Float,
	"sqrt":     Float,
}

func numericType(expr ast.Vertex) Type {
	switch expr.(type) {
	case *ast.ScalarLnumber:
		return Int
	case *ast.ScalarDnumber:
		return Float
	default:
		return Unknown
	}
}
