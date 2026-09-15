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

// Of returns the type of a value expression as a vocabulary token ("int",
// "float", "string", "bool", "array", "null" or "object:Short"), or "" when the
// value is not one we can infer without more context.
func Of(expr ast.Vertex) string {
	switch typed := expr.(type) {
	case *ast.ScalarLnumber:
		return "int"
	case *ast.ScalarDnumber:
		return "float"
	case *ast.ScalarString, *ast.ScalarEncapsed, *ast.ScalarHeredoc:
		return "string"
	case *ast.ExprArray:
		return "array"
	case *ast.ExprClosure, *ast.ExprArrowFunction:
		// a closure argument means the parameter takes a callable, which can be
		// many things (a closure, a "func" string, a [$obj, method] array, a
		// first-class callable). "callable" is a poison type: it is never
		// written, and it stops the parameter from being typed at all.
		return "callable"
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
	case *ast.ExprClassConstFetch:
		// `Foo::class` is a string; other class constants need the symbols table.
		if strings.EqualFold(phpast.ShortName(typed.Const), "class") {
			return "string"
		}
		return ""
	case *ast.ExprFunctionCall:
		return builtinReturns[strings.ToLower(phpast.ShortName(typed.Function))]
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

// builtinReturns maps builtin functions to their return type, limited to ones
// that always return a single scalar type (no `string|false` cases).
var builtinReturns = map[string]string{
	"strlen":    "int",
	"count":     "int",
	"sizeof":    "int",
	"mb_strlen": "int",
	"intval":    "int",
	"intdiv":    "int",

	"strval":        "string",
	"sprintf":       "string",
	"vsprintf":      "string",
	"implode":       "string",
	"join":          "string",
	"str_repeat":    "string",
	"strtolower":    "string",
	"strtoupper":    "string",
	"mb_strtolower": "string",
	"mb_strtoupper": "string",
	"ucfirst":       "string",
	"lcfirst":       "string",
	"ucwords":       "string",
	"trim":          "string",
	"ltrim":         "string",
	"rtrim":         "string",
	"number_format": "string",

	"floatval": "float",
	"floor":    "float",
	"ceil":     "float",
	"round":    "float",
	"sqrt":     "float",
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
