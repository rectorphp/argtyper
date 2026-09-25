package collect_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/symbols"
)

func TestFromSource(t *testing.T) {
	tests := []struct {
		name string
		src  string
		want []collect.Record
	}{
		{
			name: "this method call int",
			src:  "<?php\nclass A {\n  function go() { $this->set(5); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: "int"}},
		},
		{
			name: "function call string",
			src:  "<?php\ngreet(\"hi\");",
			want: []collect.Record{{IsFunction: true, Name: "greet", Position: 0, Type: "string", IsLiteral: true, Literal: "hi"}},
		},
		{
			name: "new constructor",
			src:  "<?php\nnew Money(1.5);",
			want: []collect.Record{{Class: "Money", Name: "__construct", Position: 0, Type: "float"}},
		},
		{
			name: "static call maps self to enclosing class",
			src:  "<?php\nclass A {\n  function go() { self::make(true); }\n}",
			want: []collect.Record{{Class: "A", Name: "make", Position: 0, Type: "bool"}},
		},
		{
			name: "explicit static class name",
			src:  "<?php\nFactory::make([1]);",
			want: []collect.Record{{Class: "Factory", Name: "make", Position: 0, Type: "array"}},
		},
		{
			name: "negative number is int",
			src:  "<?php\nf(-5);",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: "int"}},
		},
		{
			name: "null literal",
			src:  "<?php\nf(null);",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: "null"}},
		},
		{
			name: "new as argument is object type",
			src:  "<?php\nf(new Foo());",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: "object:Foo"}},
		},
		{
			name: "variable argument is recorded as unresolved",
			src:  "<?php\nf($x);",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0}},
		},
		{
			name: "skips method call on untyped variable",
			src:  "<?php\nclass A {\n  function go($other) { $other->set(1); }\n}",
			want: nil,
		},
		{
			name: "typed parameter variable resolves target class",
			src:  "<?php\nclass A {\n  function go(Repo $repo) { $repo->find(1); }\n}",
			want: []collect.Record{{Class: "Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "nullable typed parameter resolves target class",
			src:  "<?php\nclass A {\n  function go(?Repo $repo) { $repo->find(1); }\n}",
			want: []collect.Record{{Class: "Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "scalar typed parameter is not a call target",
			src:  "<?php\nclass A {\n  function go(string $name) { $name->set(1); }\n}",
			want: nil,
		},
		{
			name: "declared property resolves this-property call",
			src:  "<?php\nclass A {\n  private Repo $repo;\n  function go() { $this->repo->find(1); }\n}",
			want: []collect.Record{{Class: "Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "promoted constructor property resolves this-property call",
			src:  "<?php\nclass A {\n  function __construct(private Repo $repo) {}\n  function go() { $this->repo->find(1); }\n}",
			want: []collect.Record{{Class: "Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "nullsafe property call resolves target class",
			src:  "<?php\nclass A {\n  private Repo $repo;\n  function go() { $this->repo?->find(1); }\n}",
			want: []collect.Record{{Class: "Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "skips named argument",
			src:  "<?php\nf(name: 1);",
			want: nil,
		},
		{
			name: "does not type a reassigned parameter from its new value",
			src:  "<?php\nclass A {\n  static function fmt($day): string { return \"\"; }\n  function go($d) { $d = new \\DateTime(self::fmt($d)); }\n}",
			want: []collect.Record{
				{Class: "DateTime", Name: "__construct", Position: 0},
				{Class: "A", Name: "fmt", Position: 0},
			},
		},
		{
			name: "method chain rooted at new resolves to receiver class",
			src:  "<?php\nclass A {\n  function go() { $this->set(new \\DateTime()->modify('+2 hours')); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: "object:DateTime"}},
		},
		{
			name: "nullsafe method chain rooted at new resolves to receiver class",
			src:  "<?php\nclass A {\n  function go() { $this->set(new \\DateTime()?->modify('+2 hours')); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: "object:DateTime"}},
		},
		{
			name: "coalesce contributes both sides",
			src:  "<?php\nclass A {\n  function go($e) { $this->set('x' ?? null); }\n}",
			want: []collect.Record{
				{Class: "A", Name: "set", Position: 0, Type: "string"},
				{Class: "A", Name: "set", Position: 0, Type: "null"},
			},
		},
		{
			name: "coalesce with unresolvable left contributes unresolved and null",
			src:  "<?php\nclass A {\n  function go($e) { $this->set($e['k'] ?? null); }\n}",
			want: []collect.Record{
				{Class: "A", Name: "set", Position: 0},
				{Class: "A", Name: "set", Position: 0, Type: "null"},
			},
		},
		{
			name: "single quoted string literal keeps its value",
			src:  "<?php\nf('eq');",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: "string", IsLiteral: true, Literal: "eq"}},
		},
		{
			name: "string with escapes or interpolation is not a literal",
			src:  "<?php\nf('it\\'s'); f(\"a $b\");",
			want: []collect.Record{
				{IsFunction: true, Name: "f", Position: 0, Type: "string"},
				{IsFunction: true, Name: "f", Position: 0, Type: "string"},
			},
		},
		{
			name: "class constant string is not a literal",
			src:  "<?php\nf(Foo::class);",
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: "string"}},
		},
		{
			name: "skips parent static call",
			src:  "<?php\nclass A {\n  function go() { parent::set(1); }\n}",
			want: nil,
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			got := collect.FromSource([]byte(test.src), symbols.New())
			if !equal(got, test.want) {
				t.Errorf("\n got: %+v\nwant: %+v", got, test.want)
			}
		})
	}
}

func equal(a, b []collect.Record) bool {
	if len(a) != len(b) {
		return false
	}
	for i := range a {
		if a[i] != b[i] {
			return false
		}
	}
	return true
}
