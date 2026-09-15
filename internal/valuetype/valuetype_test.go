package valuetype_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/valuetype"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

func TestOf(t *testing.T) {
	tests := []struct {
		name string
		expr string // a single expression, wrapped as an argument below
		want string
	}{
		{"int literal", "1", "int"},
		{"float literal", "1.5", "float"},
		{"string literal", "'x'", "string"},
		{"array literal", "[1, 2]", "array"},
		{"true", "true", "bool"},
		{"null", "null", "null"},
		{"negative int", "-3", "int"},
		{"closure", "function () {}", "callable"},
		{"arrow function", "fn () => 1", "callable"},
		{"class name fetch", "Foo::class", "string"},
		{"new object", "new Money()", "object:Money"},
		{"builtin int return", "strlen($x)", "int"},
		{"builtin string return", "sprintf('%d', $n)", "string"},
		{"builtin float return", "floor($n)", "float"},
		{"unknown constant", "SOME_CONST", ""},
		{"unknown call", "helper($x)", ""},
		{"variable", "$x", ""},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			if got := valuetype.Of(firstArg(t, test.expr)); got != test.want {
				t.Errorf("Of(%s) = %q, want %q", test.expr, got, test.want)
			}
		})
	}
}

// firstArg parses `call(<expr>)` and returns the argument expression.
func firstArg(t *testing.T, expr string) ast.Vertex {
	t.Helper()
	root, err := phpast.Parse([]byte("<?php\ncall(" + expr + ");"))
	if err != nil {
		t.Fatal(err)
	}

	var found ast.Vertex
	var walk func(ast.Vertex)
	walk = func(node ast.Vertex) {
		if arg, ok := node.(*ast.Argument); ok && found == nil {
			found = arg.Expr
			return
		}
		for _, child := range phpast.Children(node) {
			walk(child)
		}
	}
	walk(root)

	if found == nil {
		t.Fatalf("no argument found in %q", expr)
	}
	return found
}
