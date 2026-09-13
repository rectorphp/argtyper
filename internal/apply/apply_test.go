package apply_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
)

// run collects from every source, resolves types, then applies to target.
func run(target string, sources ...string) (string, int) {
	var records []collect.Record
	for _, source := range sources {
		records = append(records, collect.FromSource([]byte(source))...)
	}
	types := aggregate.Resolve(records)
	output, count, _ := apply.Source([]byte(target), types)
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
			name:   "adds array type",
			target: "<?php\nfunction take($items) {}",
			callers: []string{
				"<?php\ntake([1, 2, 3]);",
			},
			want: "<?php\nfunction take(array $items) {}",
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
	output, count, changed := apply.Source([]byte(src), aggregate.Resolve(nil))
	if changed || count != 0 || output != src {
		t.Errorf("expected unchanged, got changed=%v count=%d", changed, count)
	}
}
