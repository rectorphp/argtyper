package symbols_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/argtyper/internal/valuetype"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

func TestTypeOfExpr(t *testing.T) {
	table := symbols.New()
	table.CollectSource([]byte("<?php\nconst MAX = 10;\ndefine('LABEL', 'hi');\nenum Status {\n    case Active;\n}\nclass Config {\n    const LIMIT = 1.5;\n}"))

	tests := []struct {
		name      string
		expr      string
		enclosing string
		want      valuetype.Type
	}{
		{"global const", "MAX", "", "int"},
		{"defined const", "LABEL", "", "string"},
		{"class const", "Config::LIMIT", "", "float"},
		{"self const", "LIMIT", "", ""}, // bare name, not the const fetch form
		{"self class const", "self::LIMIT", "Config", "float"},
		{"enum case", "Status::Active", "", valuetype.Object("Status")},
		{"class name stays string", "Status::class", "", "string"},
		{"unknown const", "NOPE", "", ""},
		{"unknown class const", "Other::THING", "", ""},
		{"plain literal falls back", "5", "", "int"},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			expr, names := firstArg(t, "", test.expr)
			got := table.TypeOfExpr(expr, test.enclosing, names)
			if got != test.want {
				t.Errorf("TypeOfExpr(%s) = %q, want %q", test.expr, got, test.want)
			}
		})
	}
}

func TestTypeOfExprNamespaced(t *testing.T) {
	table := symbols.New()
	table.CollectSource([]byte("<?php\nnamespace App\\X;\nenum Status {\n    case Active;\n}\nclass Config {\n    const LIMIT = 1.5;\n}"))
	table.CollectSource([]byte("<?php\nnamespace App\\Y;\nclass Config {\n    const LIMIT = 'high';\n}"))

	tests := []struct {
		name      string
		header    string
		expr      string
		enclosing string
		want      valuetype.Type
	}{
		{"class const in same namespace", "namespace App\\X;", "Config::LIMIT", "", "float"},
		{"same-named class in other namespace", "namespace App\\Y;", "Config::LIMIT", "", "string"},
		{"imported class const", "use App\\Y\\Config;", "Config::LIMIT", "", "string"},
		{"fully qualified class const", "", "\\App\\X\\Config::LIMIT", "", "float"},
		{"self resolves to enclosing class", "", "self::LIMIT", "App\\Y\\Config", "string"},
		{"enum case is fully qualified", "namespace App\\X;", "Status::Active", "", valuetype.Object("App\\X\\Status")},
		{"short name outside the namespace is unknown", "", "Config::LIMIT", "", ""},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			expr, names := firstArg(t, test.header, test.expr)
			got := table.TypeOfExpr(expr, test.enclosing, names)
			if got != test.want {
				t.Errorf("TypeOfExpr(%s) = %q, want %q", test.expr, got, test.want)
			}
		})
	}
}

func TestMerge(t *testing.T) {
	table := symbols.New()
	table.CollectSource([]byte("<?php\nconst MAX = 10;"))

	other := symbols.New()
	other.CollectSource([]byte("<?php\nnamespace App;\nconst MAX = 'ten';\nenum Status {\n    case Active;\n}"))
	table.Merge(other)

	expr, names := firstArg(t, "", "MAX")
	if got := table.TypeOfExpr(expr, "", names); got != "string" {
		t.Errorf("TypeOfExpr(MAX) = %q, want %q", got, "string")
	}
	expr, names = firstArg(t, "namespace App;", "Status::Active")
	if got := table.TypeOfExpr(expr, "", names); got != "object:App\\Status" {
		t.Errorf("TypeOfExpr(Status::Active) = %q, want %q", got, "object:App\\Status")
	}
}

// firstArg parses `call(<expr>);` after an optional header (namespace or use
// statement) and returns the argument with the file's resolved names.
func firstArg(t *testing.T, header, expr string) (ast.Vertex, map[ast.Vertex]string) {
	t.Helper()
	root, err := phpast.Parse([]byte("<?php\n" + header + "\ncall(" + expr + ");"))
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
	return found, phpast.ResolveNames(root)
}
