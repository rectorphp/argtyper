package apply_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/inherit"
	"github.com/rectorphp/argtyper/internal/symbols"
)

// run collects from every source, resolves types, then applies to target.
func run(target string, sources ...string) (string, int) {
	table := symbols.New()
	inheritance := inherit.New()
	for _, source := range sources {
		table.CollectSource([]byte(source))
		inheritance.CollectSource([]byte(source))
	}
	var records []collect.Record
	for _, source := range sources {
		records = append(records, collect.FromSource([]byte(source), table)...)
	}
	types := aggregate.Resolve(records)
	output, added, _ := apply.Source([]byte(target), types, table, inheritance, apply.Options{})
	return output, len(added)
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
			name:   "removes a redundant param doc and its empty comment",
			target: "<?php\nfinal class A {\n    /**\n     * @param string $v\n     */\n    public function set($v) {}\n    public function go() { $this->set(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(string $v) {}\n    public function go() { $this->set(\"x\"); }\n}",
		},
		{
			name:   "keeps other doc tags when removing a redundant param",
			target: "<?php\nfinal class A {\n    /**\n     * @param int $id\n     *\n     * @throws \\Exception\n     */\n    public function set($id) {}\n    public function go() { $this->set(1); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @throws \\Exception\n     */\n    public function set(int $id) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "keeps a param doc that has a description",
			target: "<?php\nfinal class A {\n    /**\n     * @param string $v the value\n     */\n    public function set($v) {}\n    public function go() { $this->set(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @param string $v the value\n     */\n    public function set(string $v) {}\n    public function go() { $this->set(\"x\"); }\n}",
		},
		{
			name:   "unions multiple observed types",
			target: "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(int|string $v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); }\n}",
		},
		{
			name:   "unions multiple types with null as a member",
			target: "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); $this->set(null); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(int|string|null $v) {}\n    public function go() { $this->set(1); $this->set(\"x\"); $this->set(null); }\n}",
		},
		{
			name:   "unions object and scalar types",
			target: "<?php\nfinal class A {\n    public function set($v) {}\n    public function go() { $this->set(new Money()); $this->set(1); }\n}",
			want:   "<?php\nfinal class A {\n    public function set(int|\\Money $v) {}\n    public function go() { $this->set(new Money()); $this->set(1); }\n}",
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
			name:   "leaves a closure argument untyped",
			target: "<?php\nfunction run($cb) {}",
			callers: []string{
				"<?php\nrun(function () {});",
			},
			want: "<?php\nfunction run($cb) {}",
		},
		{
			name:   "leaves an arrow function argument untyped",
			target: "<?php\nfunction run($cb) {}",
			callers: []string{
				"<?php\nrun(fn () => 1);",
			},
			want: "<?php\nfunction run($cb) {}",
		},
		{
			name:   "a closure poisons the parameter even with another type",
			target: "<?php\nfunction run($cb) {}",
			callers: []string{
				"<?php\nrun(function () {});\nrun(1);",
			},
			want: "<?php\nfunction run($cb) {}",
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
			name:   "types method whose local parent does not declare it",
			target: "<?php\nclass A extends Base {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
			callers: []string{
				"<?php\nclass Base {\n    public function other() {}\n}",
			},
			want: "<?php\nclass A extends Base {\n    public function set(int $v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "skips method its local parent declares",
			target: "<?php\nclass A extends Base {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
			callers: []string{
				"<?php\nclass Base {\n    public function set($v) {}\n}",
			},
			want: "<?php\nclass A extends Base {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "skips method when parent is unknown (vendor)",
			target: "<?php\nclass A extends \\Vendor\\Base {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
			want:   "<?php\nclass A extends \\Vendor\\Base {\n    public function set($v) {}\n    public function go() { $this->set(1); }\n}",
		},
		{
			name:   "qualifies arrow function typed parameter argument from use import",
			target: "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save($entity) {}\n    public function go() { $run = fn (Lead $lead) => $this->save($lead); }\n}",
			want:   "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save(\\App\\Entity\\Lead $entity) {}\n    public function go() { $run = fn (Lead $lead) => $this->save($lead); }\n}",
		},
		{
			name:   "qualifies typed parameter argument from use import",
			target: "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save($entity) {}\n    public function go(Lead $lead) { $this->save($lead); }\n}",
			want:   "<?php\nnamespace App;\n\nuse App\\Entity\\Lead;\n\nfinal class Repo {\n    public function save(\\App\\Entity\\Lead $entity) {}\n    public function go(Lead $lead) { $this->save($lead); }\n}",
		},
		{
			name:   "adds string literals doc when only literals are passed",
			target: "<?php\nfinal class A {\n    public function compare(int $score, $operator) {}\n    public function go() { $this->compare(7, 'eq'); $this->compare(8, 'neq'); $this->compare(6, 'gt'); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @param 'eq'|'gt'|'neq' $operator\n     */\n    public function compare(int $score, string $operator) {}\n    public function go() { $this->compare(7, 'eq'); $this->compare(8, 'neq'); $this->compare(6, 'gt'); }\n}",
		},
		{
			name:   "adds string literals doc to an existing doc comment",
			target: "<?php\nfinal class A {\n    /**\n     * @throws \\Exception\n     */\n    public function set($v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @throws \\Exception\n     * @param 'a'|'b' $v\n     */\n    public function set(string $v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
		},
		{
			name:   "expands a single-line doc comment for string literals",
			target: "<?php\nfinal class A {\n    /** @return void */\n    public function set($v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @return void\n     * @param 'a'|'b' $v\n     */\n    public function set(string $v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
		},
		{
			name:   "replaces a redundant string doc with string literals",
			target: "<?php\nfinal class A {\n    /**\n     * @param string $v\n     */\n    public function set($v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @param 'a'|'b' $v\n     */\n    public function set(string $v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
		},
		{
			name:   "keeps an existing param doc with description over string literals",
			target: "<?php\nfinal class A {\n    /**\n     * @param string $v the value\n     */\n    public function set($v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
			want:   "<?php\nfinal class A {\n    /**\n     * @param string $v the value\n     */\n    public function set(string $v) {}\n    public function go() { $this->set('a'); $this->set('b'); }\n}",
		},
		{
			name:   "adds nullable string literals doc",
			target: "<?php\nfunction pick($v) {}",
			callers: []string{
				"<?php\npick('a');\npick('b');\npick(null);",
			},
			want: "<?php\n/**\n * @param 'a'|'b'|null $v\n */\nfunction pick(?string $v) {}",
		},
		{
			name:   "includes a string literal default that is passed too",
			target: "<?php\nfunction pick($v = 'a') {}",
			callers: []string{
				"<?php\npick('a');\npick('b');",
			},
			want: "<?php\n/**\n * @param 'a'|'b' $v\n */\nfunction pick(string $v = 'a') {}",
		},
		{
			name:   "skips string literals doc when the default is not among them",
			target: "<?php\nfunction pick($v = 'c') {}",
			callers: []string{
				"<?php\npick('a');\npick('b');",
			},
			want: "<?php\nfunction pick(string $v = 'c') {}",
		},
		{
			name:   "skips string literals doc when a non-literal string is passed",
			target: "<?php\nfunction pick($v) {}",
			callers: []string{
				"<?php\npick('a');\npick('b');\npick(sprintf('%s', 'c'));",
			},
			want: "<?php\nfunction pick(string $v) {}",
		},
		{
			name:   "skips string literals doc when an unresolved value is passed",
			target: "<?php\nfunction pick($v) {}",
			callers: []string{
				"<?php\npick('a');\npick('b');\npick($value);",
			},
			want: "<?php\nfunction pick(string $v) {}",
		},
		{
			name:   "skips string literals doc on an already typed parameter",
			target: "<?php\nfunction pick(string $v) {}",
			callers: []string{
				"<?php\npick('a');\npick('b');",
			},
			want: "<?php\nfunction pick(string $v) {}",
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
	output, added, changed := apply.Source([]byte(src), aggregate.Resolve(nil), symbols.New(), inherit.New(), apply.Options{})
	if changed || len(added) != 0 || output != src {
		t.Errorf("expected unchanged, got changed=%v count=%d", changed, len(added))
	}
}

func TestLiteralsOnly(t *testing.T) {
	src := "<?php\nfunction pick($v, $count, $page = 1) {}\npick('a', 1);\npick('b', 2);"
	table := symbols.New()
	types := aggregate.Resolve(collect.FromSource([]byte(src), table))

	output, added, _ := apply.Source([]byte(src), types, table, inherit.New(), apply.Options{Literals: true})

	want := "<?php\n/**\n * @param 'a'|'b' $v\n */\nfunction pick(string $v, $count, $page = 1) {}\npick('a', 1);\npick('b', 2);"
	if output != want || len(added) != 1 {
		t.Errorf("\n got: %q (%d added)\nwant: %q", output, len(added), want)
	}
}

func TestObjectsOnly(t *testing.T) {
	src := "<?php\nfunction save($user, $item, $count, $mixed, $status = Status::Active) {}\nenum Status {\n    case Active;\n}\nsave(new User(), new Item(), 1, new Money());\nsave(null, new Order(), 2, 5);"
	table := symbols.New()
	table.CollectSource([]byte(src))
	types := aggregate.Resolve(collect.FromSource([]byte(src), table))

	output, added, _ := apply.Source([]byte(src), types, table, inherit.New(), apply.Options{Objects: true})

	want := "<?php\nfunction save(?\\User $user, \\Item|\\Order $item, $count, $mixed, \\Status $status = Status::Active) {}\nenum Status {\n    case Active;\n}\nsave(new User(), new Item(), 1, new Money());\nsave(null, new Order(), 2, 5);"
	if output != want || len(added) != 3 {
		t.Errorf("\n got: %q (%d added)\nwant: %q", output, len(added), want)
	}
}

func TestLiteralsAndObjects(t *testing.T) {
	src := "<?php\nfunction save($user, $mode, $count) {}\nsave(new User(), 'a', 1);\nsave(new User(), 'b', 2);"
	table := symbols.New()
	types := aggregate.Resolve(collect.FromSource([]byte(src), table))

	output, added, _ := apply.Source([]byte(src), types, table, inherit.New(), apply.Options{Literals: true, Objects: true})

	want := "<?php\n/**\n * @param 'a'|'b' $mode\n */\nfunction save(\\User $user, string $mode, $count) {}\nsave(new User(), 'a', 1);\nsave(new User(), 'b', 2);"
	if output != want || len(added) != 2 {
		t.Errorf("\n got: %q (%d added)\nwant: %q", output, len(added), want)
	}
}
