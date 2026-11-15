package inherit_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/inherit"
)

func TestOverrides(t *testing.T) {
	table := inherit.New()
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Base {\n    public function save($v) {}\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\ninterface Contract {\n    public function handle($v);\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Child extends Base implements Contract {\n    public function save($v) {}\n    public function handle($v) {}\n    public function own($v) {}\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Loud {\n    public function Shout($v) {}\n}\n\nclass Quiet extends Loud {}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Lower extends base {}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Orphan extends \\Vendor\\Base {\n    public function run($v) {}\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\ntrait Renders {\n    protected function view($data) {}\n}\n\nabstract class Controller {\n    use Renders;\n}\n\nclass Page extends Controller {\n    protected function view($data) {}\n}"))

	tests := []struct {
		name          string
		class, method string
		overrides     bool
		resolved      bool
	}{
		{"inherited from parent class", "App\\Child", "save", true, true},
		{"inherited from interface", "App\\Child", "handle", true, true},
		{"declared only on child", "App\\Child", "own", false, true},
		{"inherited with different case", "App\\Child", "SAVE", true, true},
		{"looked up in different case on interface", "App\\Child", "Handle", true, true},
		{"declared with different case on ancestor", "App\\Quiet", "shout", true, true},
		{"class looked up in different case", "app\\CHILD", "save", true, true},
		{"parent written in different case", "App\\Lower", "save", true, true},
		{"parent chain reaches vendor", "App\\Orphan", "run", false, false},
		{"unknown class", "App\\Missing", "x", false, false},
		{"inherited from trait used by parent", "App\\Page", "view", true, true},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			overrides, resolved := table.Overrides(test.class, test.method)
			if overrides != test.overrides || resolved != test.resolved {
				t.Errorf("Overrides(%s, %s) = (%v, %v), want (%v, %v)",
					test.class, test.method, overrides, resolved, test.overrides, test.resolved)
			}
		})
	}
}

func TestMerge(t *testing.T) {
	table := inherit.New()
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Child extends Base {\n    public function save($v) {}\n}"))

	other := inherit.New()
	other.CollectSource([]byte("<?php\nnamespace App;\n\nclass Base {\n    public function save($v) {}\n}"))
	table.Merge(other)

	if overrides, resolved := table.Overrides("App\\Child", "save"); !overrides || !resolved {
		t.Errorf("Overrides(App\\Child, save) = (%v, %v), want (true, true)", overrides, resolved)
	}
}

func TestOverriddenByChild(t *testing.T) {
	table := inherit.New()
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Base {\n    public function save($v) {}\n    public function load($v) {}\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Middle extends Base {}\n\nclass Leaf extends Middle {\n    public function SAVE($v) {}\n}"))
	table.CollectSource([]byte("<?php\nnamespace App;\n\ninterface Contract {\n    public function handle($v);\n}\n\nclass Handler implements Contract {\n    public function handle($v) {}\n}"))

	tests := []struct {
		name          string
		class, method string
		want          bool
	}{
		{"overridden by grandchild", "App\\Base", "save", true},
		{"not overridden", "App\\Base", "load", false},
		{"implemented by class", "app\\contract", "handle", true},
		{"leaf class", "App\\Leaf", "save", false},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			if got := table.OverriddenByChild(test.class, test.method); got != test.want {
				t.Errorf("OverriddenByChild(%s, %s) = %v, want %v", test.class, test.method, got, test.want)
			}
		})
	}
}
