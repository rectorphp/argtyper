package aggregate_test

import (
	"strings"
	"testing"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/valuetype"
)

func TestResolveMethod(t *testing.T) {
	tests := []struct {
		name         string
		records      []collect.Record
		wantType     string // members joined by "|"
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
			name: "two real types become a union",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: "int"},
				{Class: "A", Name: "m", Type: "string"},
			},
			wantType:  "int|string",
			wantFound: true,
		},
		{
			name: "union with null",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: "int"},
				{Class: "A", Name: "m", Type: "string"},
				{Class: "A", Name: "m", Type: "null"},
			},
			wantType:     "int|string",
			wantNullable: true,
			wantFound:    true,
		},
		{
			name: "unresolved argument keeps the resolved type",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: "int"},
				{Class: "A", Name: "m"},
			},
			wantType:  "int",
			wantFound: true,
		},
		{
			name:      "class name differing in case shares the key",
			records:   []collect.Record{{Class: "a", Name: "m", Type: "int"}},
			wantType:  "int",
			wantFound: true,
		},
		{
			name: "same class in another case is one object type",
			records: []collect.Record{
				{Class: "A", Name: "m", Type: valuetype.Object("App\\Foo")},
				{Class: "A", Name: "m", Type: valuetype.Object("App\\foo")},
			},
			wantType:  "object:App\\Foo",
			wantFound: true,
		},
		{
			name:      "only unresolved resolves to nothing",
			records:   []collect.Record{{Class: "A", Name: "m"}},
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
			if found && (join(resolved.Types) != test.wantType || resolved.Nullable != test.wantNullable) {
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
	if !ok || join(first.Types) != "int" {
		t.Errorf("position 0: got %+v ok=%v", first, ok)
	}
	second, ok := types.Function("f", 1)
	if !ok || join(second.Types) != "string" {
		t.Errorf("position 1: got %+v ok=%v", second, ok)
	}
}

func TestResolveLiterals(t *testing.T) {
	literal := func(value string) collect.Record {
		return collect.Record{Class: "A", Name: "m", Type: "string", IsLiteral: true, Literal: value}
	}

	tests := []struct {
		name    string
		records []collect.Record
		want    string // literals joined by "|"
	}{
		{
			name:    "distinct literals sorted and deduplicated",
			records: []collect.Record{literal("neq"), literal("eq"), literal("neq")},
			want:    "eq|neq",
		},
		{
			name:    "literals with null",
			records: []collect.Record{literal("eq"), literal("neq"), {Class: "A", Name: "m", Type: "null"}},
			want:    "eq|neq",
		},
		{
			name:    "single literal is skipped",
			records: []collect.Record{literal("eq"), literal("eq")},
			want:    "",
		},
		{
			name: "more than ten literals are skipped",
			records: []collect.Record{
				literal("a"), literal("b"), literal("c"), literal("d"), literal("e"), literal("f"),
				literal("g"), literal("h"), literal("i"), literal("j"), literal("k"),
			},
			want: "",
		},
		{
			name:    "non-literal string drops the literals",
			records: []collect.Record{literal("eq"), literal("neq"), {Class: "A", Name: "m", Type: "string"}},
			want:    "",
		},
		{
			name:    "unresolved argument drops the literals",
			records: []collect.Record{literal("eq"), literal("neq"), {Class: "A", Name: "m"}},
			want:    "",
		},
		{
			name:    "union with another type drops the literals",
			records: []collect.Record{literal("eq"), literal("neq"), {Class: "A", Name: "m", Type: "int"}},
			want:    "",
		},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			resolved, _ := aggregate.Resolve(test.records).Method("A", "m", 0)
			if got := strings.Join(resolved.Literals, "|"); got != test.want {
				t.Errorf("got %q want %q", got, test.want)
			}
		})
	}
}

func join(types []valuetype.Type) string {
	members := make([]string, len(types))
	for i, member := range types {
		members[i] = string(member)
	}
	return strings.Join(members, "|")
}
