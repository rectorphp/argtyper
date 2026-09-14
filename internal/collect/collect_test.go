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
			want: []collect.Record{{IsFunction: true, Name: "greet", Position: 0, Type: "string"}},
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
			name: "skips variable argument",
			src:  "<?php\nf($x);",
			want: nil,
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
