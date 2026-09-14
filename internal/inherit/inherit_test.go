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
	table.CollectSource([]byte("<?php\nnamespace App;\n\nclass Orphan extends \\Vendor\\Base {\n    public function run($v) {}\n}"))

	tests := []struct {
		name          string
		class, method string
		overrides     bool
		resolved      bool
	}{
		{"inherited from parent class", "App\\Child", "save", true, true},
		{"inherited from interface", "App\\Child", "handle", true, true},
		{"declared only on child", "App\\Child", "own", false, true},
		{"parent chain reaches vendor", "App\\Orphan", "run", false, false},
		{"unknown class", "App\\Missing", "x", false, false},
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
