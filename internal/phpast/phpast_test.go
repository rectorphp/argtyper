package phpast_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

func TestResolveNamesQualifiesNewFromUseImport(t *testing.T) {
	root, err := phpast.Parse([]byte("<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nnew Lead();"))
	if err != nil {
		t.Fatal(err)
	}

	names := phpast.ResolveNames(root)

	newExpr := findNew(root)
	if newExpr == nil {
		t.Fatal("no new expression found")
	}
	classNode := phpast.ObjectClassNode(newExpr)
	if classNode == nil {
		t.Fatal("ObjectClassNode returned nil for new expression")
	}

	if got := names[classNode]; got != "App\\Entity\\Lead" {
		t.Errorf("resolved name = %q, want %q", got, "App\\Entity\\Lead")
	}
}

func findNew(node ast.Vertex) ast.Vertex {
	if _, ok := node.(*ast.ExprNew); ok {
		return node
	}
	for _, child := range phpast.Children(node) {
		if found := findNew(child); found != nil {
			return found
		}
	}
	return nil
}
