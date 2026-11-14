package collect_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/symbols"
	"github.com/rectorphp/argtyper/internal/valuetype"
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
			want: []collect.Record{{IsFunction: true, Name: "f", Position: 0, Type: valuetype.Object("Foo")}},
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
			name: "promoted property of constructor in different case resolves this-property call",
			src:  "<?php\nclass A {\n  function __Construct(private Repo $repo) {}\n  function go() { $this->repo->find(1); }\n}",
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
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: valuetype.Object("DateTime")}},
		},
		{
			name: "nullsafe method chain rooted at new resolves to receiver class",
			src:  "<?php\nclass A {\n  function go() { $this->set(new \\DateTime()?->modify('+2 hours')); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: valuetype.Object("DateTime")}},
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
			name: "new in namespace uses fully qualified class",
			src:  "<?php\nnamespace App\\X;\nnew Foo(1);",
			want: []collect.Record{{Class: "App\\X\\Foo", Name: "__construct", Position: 0, Type: "int"}},
		},
		{
			name: "new of imported class uses fully qualified class",
			src:  "<?php\nnamespace App;\nuse App\\Y\\Foo;\nnew Foo('s');",
			want: []collect.Record{{Class: "App\\Y\\Foo", Name: "__construct", Position: 0, Type: "string", IsLiteral: true, Literal: "s"}},
		},
		{
			name: "this and self calls use fully qualified enclosing class",
			src:  "<?php\nnamespace App;\nclass A {\n  function go() { $this->set(1); self::make(2); }\n}",
			want: []collect.Record{
				{Class: "App\\A", Name: "set", Position: 0, Type: "int"},
				{Class: "App\\A", Name: "make", Position: 0, Type: "int"},
			},
		},
		{
			name: "static call in namespace uses fully qualified class",
			src:  "<?php\nnamespace App;\nFactory::make(1);",
			want: []collect.Record{{Class: "App\\Factory", Name: "make", Position: 0, Type: "int"}},
		},
		{
			name: "typed parameter call uses fully qualified class",
			src:  "<?php\nnamespace App;\nuse Lib\\Repo;\nclass A {\n  function go(Repo $repo) { $repo->find(1); }\n}",
			want: []collect.Record{{Class: "Lib\\Repo", Name: "find", Position: 0, Type: "int"}},
		},
		{
			name: "skips parent static call",
			src:  "<?php\nclass A {\n  function go() { parent::set(1); }\n}",
			want: nil,
		},
		{
			name: "new self and new static use fully qualified enclosing class",
			src:  "<?php\nnamespace App;\nclass A {\n  static function make() { new self(5); new static('s'); }\n}",
			want: []collect.Record{
				{Class: "App\\A", Name: "__construct", Position: 0, Type: "int"},
				{Class: "App\\A", Name: "__construct", Position: 0, Type: "string", IsLiteral: true, Literal: "s"},
			},
		},
		{
			name: "skips new parent",
			src:  "<?php\nclass A extends B {\n  function go() { new parent(1); }\n}",
			want: nil,
		},
		{
			name: "skips new self and self call outside a class",
			src:  "<?php\nnew self(1);\nself::make(1);\nfunction f() { new static(1); }",
			want: nil,
		},
		{
			name: "new self as argument is enclosing class object",
			src:  "<?php\nnamespace App;\nclass A {\n  function go() { $this->set(new self()); }\n}",
			want: []collect.Record{{Class: "App\\A", Name: "set", Position: 0, Type: valuetype.Object("App\\A")}},
		},
		{
			name: "new parent as argument is unresolved",
			src:  "<?php\nclass A extends B {\n  function go() { $this->set(new parent()); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0}},
		},
		{
			name: "method chain rooted at new static resolves to enclosing class",
			src:  "<?php\nclass A {\n  function go() { $this->set(new static()->with()); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: valuetype.Object("A")}},
		},
		{
			name: "local assigned new self resolves call target",
			src:  "<?php\nclass A {\n  function go() { $copy = new self(); $copy->set(1); }\n}",
			want: []collect.Record{{Class: "A", Name: "set", Position: 0, Type: "int"}},
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
