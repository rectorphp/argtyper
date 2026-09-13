package aggregate_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/collect"
)

func TestResolveMethod(t *testing.T) {
	tests := []struct {
		name         string
		records      []collect.Record
		wantType     string
		wantNullable bool
		wantFound    bool
	}{
		{
			name:      "single type",
			records:   []collect.Record{{Class: "A", Name: "m", Type: "int"}},
			wantType:  "int",
			wantFound: true,
		},
		{
			name: "type plus null is nullable",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: "string"},
				{Class: "A", Name: "m", Type: "null"},
			},
			wantType:     "string",
			wantNullable: true,
			wantFound:    true,
		},
		{
			name: "two real types is ambiguous",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: "int"},
				{Class: "A", Name: "m", Type: "string"},
			},
			wantFound: false,
		},
		{
			name:      "only null resolves to nothing",
			records:   []collect.Record{{Class: "A", Name: "m", Type: "null"}},
			wantFound: false,
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			resolved, found := aggregate.Resolve(test.records).Method("A", "m", 0)
			if found != test.wantFound {
				t.Fatalf("found=%v want %v", found, test.wantFound)
			}
			if found && (resolved.Type != test.wantType || resolved.Nullable != test.wantNullable) {
				t.Errorf("got %+v want type=%s nullable=%v", resolved, test.wantType, test.wantNullable)
			}
		})
	}
}

func TestResolveFunctionAndPositionIsolation(t *testing.T) {
	records := []collect.Record{
		{IsFunction: true, Name: "f", Position: 0, Type: "int"},
		{IsFunction: true, Name: "f", Position: 1, Type: "string"},
	}
	types := aggregate.Resolve(records)

	first, ok := types.Function("f", 0)
	if !ok || first.Type != "int" {
		t.Errorf("position 0: got %+v ok=%v", first, ok)
	}
	second, ok := types.Function("f", 1)
	if !ok || second.Type != "string" {
		t.Errorf("position 1: got %+v ok=%v", second, ok)
	}
}
