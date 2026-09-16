package phpast_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

func TestStripRedundantDocParams(t *testing.T) {
	tests := []struct {
		name  string
		src   string
		added map[string]string
		want  string
	}{
		{
			name:  "removes matching scalar tag and empties the doc",
			src:   "<?php\nclass A {\n    /**\n     * @param string $v\n     */\n    public function m($v) {}\n}",
			added: map[string]string{"v": "string"},
			want:  "<?php\nclass A {\n    public function m($v) {}\n}",
		},
		{
			name:  "matches object type regardless of leading backslash",
			src:   "<?php\nclass A {\n    /**\n     * @param DateTime $d\n     */\n    public function m($d) {}\n}",
			added: map[string]string{"d": "\\DateTime"},
			want:  "<?php\nclass A {\n    public function m($d) {}\n}",
		},
		{
			name:  "matches a union regardless of member order",
			src:   "<?php\nclass A {\n    /**\n     * @param string|array $c\n     */\n    public function m($c) {}\n}",
			added: map[string]string{"c": "array|string"},
			want:  "<?php\nclass A {\n    public function m($c) {}\n}",
		},
		{
			name:  "matches an imported short name against a fully qualified union member",
			src:   "<?php\nclass A {\n    /**\n     * @param CompositeExpression|string $e\n     */\n    public function m($e) {}\n}",
			added: map[string]string{"e": "\\Doctrine\\DBAL\\Query\\Expression\\CompositeExpression|string"},
			want:  "<?php\nclass A {\n    public function m($e) {}\n}",
		},
		{
			name:  "keeps a tag whose type differs from the added type",
			src:   "<?php\nclass A {\n    /**\n     * @param mixed $v\n     */\n    public function m($v) {}\n}",
			added: map[string]string{"v": "\\DateTime"},
			want:  "<?php\nclass A {\n    /**\n     * @param mixed $v\n     */\n    public function m($v) {}\n}",
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			root, err := phpast.Parse([]byte(test.src))
			if err != nil {
				t.Fatal(err)
			}
			var walk func(ast.Vertex)
			walk = func(node ast.Vertex) {
				if method, ok := node.(*ast.StmtClassMethod); ok {
					phpast.StripRedundantDocParams(method, test.added)
				}
				for _, child := range phpast.Children(node) {
					walk(child)
				}
			}
			walk(root)

			if got := phpast.Print(root); got != test.want {
				t.Errorf("\n got: %q\nwant: %q", got, test.want)
			}
		})
	}
}
