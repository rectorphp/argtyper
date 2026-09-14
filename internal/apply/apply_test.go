package apply_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/symbols"
)

// run collects from every source, resolves types, then applies to target.
func run(target string, sources ...string) (string, int) {
	table := symbols.New()
	for _, source := range sources {
		table.CollectSource([]byte(source))
	}
	var records []collect.Record
	for _, source := range sources {
		records = append(records, collect.FromSource([]byte(source), table)...)
	}
	types := aggregate.Resolve(records)
	output, count, _ := apply.Source([]byte(target), types, table)
	return output, count
}

func TestApply(t *testing.T) {
	tests := []struct {
		name    string
		target  string
		callers []string
		want    string
	}{
		{
			name:   "adds int to method from this call",
			target: "<?php\nfinal class Hotel {\n    public function room($number) {}\n    public function go() { $this->room(5); }\n}",
			want:   "<?php\nfinal class Hotel {\n    public function room(int $number) {}\n    public function go() { $this->room(5); }\n}",
		},
		{
			name:   "adds string to function",
			target: "<?php\nfunction greet($who) {}",
			callers: []string{
				"<?php\ngreet(\"hi\");",
			},
			want: "<?php\nfunction greet(string $who) {}",
		},
		{
			name:   "adds type from constructor",
			target: "<?php\nclass Money {\n    public function __construct($amount) {}\n}",
			callers: []string{
				"<?php\nnew Money(100);",
			},
			want: "<?php\nclass Money {\n    public function __construct(int $amount) {}\n}",
		},
		{
			name:   "adds type from static call",
			target: "<?php\nfinal class Factory {\n    public static function make($label) {}\n}",
			callers: []string{
				"<?php\nFactory::make(\"a\");",
			},
			want: "<?php\nfinal class Factory {\n    public static function make(string $label) {}\n}",
		},
		{
			name:   "nullable when null also passed",
			target: "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(1); $this->set(null); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(?int $v) {}\n    public function go() { $this->set(1); $this->set(null); }\n}",
		},
		{
			name:   "nullable from null default keeps question mark",
			target: "<?php\nfinal class A {\n    public function set($v = null) {}\n    public function go() { $this->set(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(?string $v = null) {}\n    public function go() { $this->set(\"x\"); }\n}",
		},
		{
			name:   "keeps existing type untouched",
			target: "<?php\nfinal class A {\n    public function set(string $v) {}\n    public function go() { $this->set(1); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(string $v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "skips ambiguous multiple types",
			target: "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); }\n}",
		},
		{
			name:   "skips magic method",
			target: "<?php\nfinal class A {\n    public function __get($name) {}\n    public function go() { $this->__get(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    public function __get($name) {}\n    public function go() { $this->__get(\"x\"); }\n}",
		},
		{
			name:   "skips override candidate in class with interface",
			target: "<?php\nclass A implements Contract {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
			want:   "<?php\nclass A implements Contract {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "types private method even with parent",
			target: "<?php\nclass A extends Base {\n    private function set($v) {}\n    public function go() { $this->set(1); }\n}",
			want:   "<?php\nclass A extends Base {\n    private function set(int $v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "keeps comma spacing when typing a later parameter",
			target: "<?php\nfinal class A {\n    public function set($name, $count) {}\n    public function go() { $this->set(\"x\", 1); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(string $name, int $count) {}\n    public function go() { $this->set(\"x\", 1); }\n}",
		},
		{
			name:   "types promoted constructor parameter with modifier",
			target: "<?php\nfinal class A {\n    public function __construct(private $guest) {}\n}",
			callers: []string{
				"<?php\nnew A(true);",
			},
			want: "<?php\nfinal class A {\n    public function __construct(private bool $guest) {}\n}",
		},
		{
			name:   "keeps indentation on multiline parameters",
			target: "<?php\nfunction make(\n    $a,\n    $b\n) {}",
			callers: []string{
				"<?php\nmake(1, \"x\");",
			},
			want: "<?php\nfunction make(\n    int $a,\n    string $b\n) {}",
		},
		{
			name:   "adds array type",
			target: "<?php\nfunction take($items) {}",
			callers: []string{
				"<?php\ntake([1, 2, 3]);",
			},
			want: "<?php\nfunction take(array $items) {}",
		},
		{
			name:   "types from literal default without callers",
			target: "<?php\nfunction paginate($page = 1) {}",
			want:   "<?php\nfunction paginate(int $page = 1) {}",
		},
		{
			name:   "types from array default without callers",
			target: "<?php\nfunction take($items = []) {}",
			want:   "<?php\nfunction take(array $items = []) {}",
		},
		{
			name:   "types closure argument",
			target: "<?php\nfunction run($cb) {}",
			callers: []string{
				"<?php\nrun(function () {});",
			},
			want: "<?php\nfunction run(\\Closure $cb) {}",
		},
		{
			name:   "types arrow function argument",
			target: "<?php\nfunction run($cb) {}",
			callers: []string{
				"<?php\nrun(fn () => 1);",
			},
			want: "<?php\nfunction run(\\Closure $cb) {}",
		},
		{
			name:   "types from builtin return value",
			target: "<?php\nfunction save($length) {}",
			callers: []string{
				"<?php\nsave(strlen($x));",
			},
			want: "<?php\nfunction save(int $length) {}",
		},
		{
			name:   "types class constant reference as string",
			target: "<?php\nfunction register($name) {}",
			callers: []string{
				"<?php\nregister(Foo::class);",
			},
			want: "<?php\nfunction register(string $name) {}",
		},
		{
			name:   "types global constant argument",
			target: "<?php\nfunction limit($max) {}",
			callers: []string{
				"<?php\nconst MAX = 10;\nlimit(MAX);",
			},
			want: "<?php\nfunction limit(int $max) {}",
		},
		{
			name:   "types enum case argument",
			target: "<?php\nfunction handle($status) {}",
			callers: []string{
				"<?php\nenum Status {\n    case Active;\n}\nhandle(Status::Active);",
			},
			want: "<?php\nfunction handle(\\Status $status) {}",
		},
		{
			name:   "types from typed parameter passed as argument",
			target: "<?php\nfinal class Repo {\n    public function save($entity) {}\n    public function go(User $user) { $this->save($user); }\n}",
			want:   "<?php\nfinal class Repo {\n    public function save(\\User $entity) {}\n    public function go(User $user) { $this->save($user); }\n}",
		},
		{
			name:   "types from local new assigned argument",
			target: "<?php\nfinal class Repo {\n    public function save($entity) {}\n    public function go() { $item = new Item(); $this->save($item); }\n}",
			want:   "<?php\nfinal class Repo {\n    public function save(\\Item $entity) {}\n    public function go() { $item = new Item(); $this->save($item); }\n}",
		},
		{
			name:   "qualifies new argument from use import",
			target: "<?php\nnamespace App;\n\nfunction handle($item) {}",
			callers: []string{
				"<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nhandle(new Lead());",
			},
			want: "<?php\nnamespace App;\n\nfunction handle(\\App\\Entity\\Lead $item) {}",
		},
		{
			name:   "qualifies typed parameter argument from use import",
			target: "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save($entity) {}\n    public function go(Lead $lead) { $this->save($lead); }\n}",
			want:   "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save(\\App\\Entity\\Lead $entity) {}\n    public function go(Lead $lead) { $this->save($lead); }\n}",
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			got, _ := run(test.target, append(test.callers, test.target)...)
			if got != test.want {
				t.Errorf("\n got: %q\nwant: %q", got, test.want)
			}
		})
	}
}

func TestNoTypesReturnsUnchanged(t *testing.T) {
	src := "<?php\nfunction greet($who) {}"
	output, count, changed := apply.Source([]byte(src), aggregate.Resolve(nil), symbols.New())
	if changed || count != 0 || output != src {
		t.Errorf("expected unchanged, got changed=%v count=%d", changed, count)
	}
}
