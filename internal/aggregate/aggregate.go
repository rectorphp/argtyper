// Package aggregate turns collected argument records into a single resolved
// type per parameter position, applying the same skip rules as the PHP tool.
package aggregate

import (
	"slices"
	"sort"
	"strconv"

	"github.com/rectorphp/argtyper/internal/collect"
)

// Resolved is the final type decision for one parameter position. Two or more
// types observed for the same parameter become a union.
type Resolved struct {
	Types    []string // one or more source type keywords or "object:Fqcn", never "null"
	Nullable bool
	Literals []string // sorted string literal values, set only for a plain string type, see literalValues
}

const (
	minLiterals = 2
	maxLiterals = 10
)

// group collects everything observed for one parameter position.
type group struct {
	types    map[string]struct{}
	literals map[string]struct{}
	// set when a string other than a plain literal, or an unresolved value, was
	// passed - the literals then no longer cover every argument
	nonLiterals bool
}

// Types holds resolved parameter types keyed for fast lookup during apply.
type Types struct {
	methods   map[string]Resolved // class \x00 method \x00 position
	functions map[string]Resolved // function \x00 position
}

// Method returns the resolved type for a method/constructor parameter.
func (t Types) Method(class, method string, position int) (Resolved, bool) {
	resolved, ok := t.methods[methodKey(class, method, position)]
	return resolved, ok
}

// Function returns the resolved type for a function parameter.
func (t Types) Function(name string, position int) (Resolved, bool) {
	resolved, ok := t.functions[functionKey(name, position)]
	return resolved, ok
}

// Resolve groups records per parameter into a resolved type: the observed types
// as a union, with null captured as nullability rather than a member.
func Resolve(records []collect.Record) Types {
	methodTypes := map[string]*group{}
	functionTypes := map[string]*group{}

	for _, record := range records {
		if record.IsFunction {
			key := functionKey(record.Name, record.Position)
			addRecord(functionTypes, key, record)
			continue
		}
		key := methodKey(record.Class, record.Name, record.Position)
		addRecord(methodTypes, key, record)
	}

	return Types{
		methods:   resolveGroups(methodTypes),
		functions: resolveGroups(functionTypes),
	}
}

func resolveGroups(groups map[string]*group) map[string]Resolved {
	resolved := map[string]Resolved{}

	for key, group := range groups {
		types := make([]string, 0, len(group.types))
		for typeName := range group.types {
			types = append(types, typeName)
		}
		sort.Strings(types)

		// a callable argument (a closure) leaves the parameter untyped, since a
		// callable can take many shapes that must not be narrowed
		if contains(types, "callable") {
			continue
		}

		nullable := contains(types, "null")
		members := without(types, "null")
		if len(members) == 0 {
			continue
		}
		resolved[key] = Resolved{Types: members, Nullable: nullable, Literals: literalValues(members, group)}
	}

	return resolved
}

// literalValues returns the string literals passed into a plain string
// parameter, when every argument was a string literal or null and there are a few
// distinct ones - an enum-like set worth a `'a'|'b'` doc type.
func literalValues(members []string, group *group) []string {
	if len(members) != 1 || members[0] != "string" || group.nonLiterals {
		return nil
	}
	if len(group.literals) < minLiterals || len(group.literals) > maxLiterals {
		return nil
	}
	literals := make([]string, 0, len(group.literals))
	for literal := range group.literals {
		literals = append(literals, literal)
	}
	sort.Strings(literals)
	return literals
}

func addRecord(groups map[string]*group, key string, record collect.Record) {
	if groups[key] == nil {
		groups[key] = &group{types: map[string]struct{}{}, literals: map[string]struct{}{}}
	}
	switch {
	case record.Type == "":
		groups[key].nonLiterals = true
	case record.IsLiteral:
		groups[key].types[record.Type] = struct{}{}
		groups[key].literals[record.Literal] = struct{}{}
	default:
		groups[key].types[record.Type] = struct{}{}
		if record.Type == "string" {
			groups[key].nonLiterals = true
		}
	}
}

func methodKey(class, method string, position int) string {
	return class + "\x00" + method + "\x00" + strconv.Itoa(position)
}

func functionKey(name string, position int) string {
	return name + "\x00" + strconv.Itoa(position)
}

func contains(values []string, needle string) bool {
	return slices.Contains(values, needle)
}

func without(values []string, needle string) []string {
	kept := make([]string, 0, len(values))
	for _, value := range values {
		if value != needle {
			kept = append(kept, value)
		}
	}
	return kept
}
