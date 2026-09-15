// Package aggregate turns collected argument records into a single resolved
// type per parameter position, applying the same skip rules as the PHP tool.
package aggregate

import (
	"sort"
	"strconv"

	"github.com/rectorphp/argtyper/internal/collect"
)

// Resolved is the final type decision for one parameter position. Two or more
// types observed for the same parameter become a union.
type Resolved struct {
	Types    []string // one or more source type keywords or "object:Fqcn", never "null"
	Nullable bool
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
	methodTypes := map[string]map[string]struct{}{}
	functionTypes := map[string]map[string]struct{}{}

	for _, record := range records {
		if record.IsFunction {
			key := functionKey(record.Name, record.Position)
			addType(functionTypes, key, record.Type)
			continue
		}
		key := methodKey(record.Class, record.Name, record.Position)
		addType(methodTypes, key, record.Type)
	}

	return Types{
		methods:   resolveGroups(methodTypes),
		functions: resolveGroups(functionTypes),
	}
}

func resolveGroups(groups map[string]map[string]struct{}) map[string]Resolved {
	resolved := map[string]Resolved{}

	for key, typeSet := range groups {
		types := make([]string, 0, len(typeSet))
		for typeName := range typeSet {
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
		resolved[key] = Resolved{Types: members, Nullable: nullable}
	}

	return resolved
}

func addType(groups map[string]map[string]struct{}, key, typeName string) {
	if groups[key] == nil {
		groups[key] = map[string]struct{}{}
	}
	groups[key][typeName] = struct{}{}
}

func methodKey(class, method string, position int) string {
	return class + "\x00" + method + "\x00" + strconv.Itoa(position)
}

func functionKey(name string, position int) string {
	return name + "\x00" + strconv.Itoa(position)
}

func contains(values []string, needle string) bool {
	for _, value := range values {
		if value == needle {
			return true
		}
	}
	return false
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
