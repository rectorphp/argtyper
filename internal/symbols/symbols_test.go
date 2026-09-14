package symbols_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

func TestTypeOfExpr(t *testing.T) {
	table := symbols.New()
	table.CollectSource([]byte("<?php\nconst MAX = 10;\ndefine('LABEL', 'hi');\nenum Status {\n    case Active;\n}\nclass Config {\n    const LIMIT = 1.5;\n}"))

	tests := []struct {
		name      string
		expr      string
		enclosing string
		want      string
	}{
		{"global const", "MAX", "", "int"},
		{"defined const", "LABEL", "", "string"},
		{"class const", "Config::LIMIT", "", "float"},
		{"self const", "LIMIT", "", ""}, // bare name, not the const fetch form
		{"self class const", "self::LIMIT", "Config", "float"},
		{"enum case", "Status::Active", "", "object:Status"},
		{"class name stays string", "Status::class", "", "string"},
		{"unknown const", "NOPE", "", ""},
		{"unknown class const", "Other::THING", "", ""},
		{"plain literal falls back", "5", "", "int"},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			got := table.TypeOfExpr(firstArg(t, test.expr), test.enclosing)
			if got != test.want {
				t.Errorf("TypeOfExpr(%s) = %q, want %q", test.expr, got, test.want)
			}
		})
	}
}

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
