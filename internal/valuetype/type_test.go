package valuetype_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/valuetype"
)

func TestType(t *testing.T) {
	tests := []struct {
		name      string
		typ       valuetype.Type
		wantClass string
	}{
		{"object", valuetype.Object(`App\Money`), `App\Money`},
		{"short object", valuetype.Object("Money"), "Money"},
		{"scalar", valuetype.Int, ""},
		{"callable", valuetype.Callable, ""},
		{"unknown", valuetype.Unknown, ""},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			if got := test.typ.IsObject(); got != (test.wantClass != "") {
				t.Errorf("IsObject(%q) = %v", test.typ, got)
			}
			if got := test.typ.Class(); got != test.wantClass {
				t.Errorf("Class(%q) = %q, want %q", test.typ, got, test.wantClass)
			}
		})
	}
}
