// Package aggregate turns collected argument records into a single resolved
// type per parameter position, applying the same skip rules as the PHP tool.
package aggregate

import (
	"slices"
	"sort"
	"strconv"
	"strings"

	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/valuetype"
)

// Resolved is the final type decision for one parameter position. Two or more
// types, a callable or an unresolved argument leave Types empty: the parameter
// was seen at a call site but must stay untyped.
type Resolved struct {
	Types    []valuetype.Type // exactly one type, never Null; empty when blocked
	Nullable bool
	Literals []string // sorted string literal values, set only for a plain string type, see literalValues
}

const (
	minLiterals = 2
	maxLiterals = 10
)

// group collects everything observed for one parameter position.
type group struct {
	types    map[valuetype.Type]struct{}
	literals map[string]struct{}
	// set when a string other than a plain literal, or an unresolved value, was
	// passed - the literals then no longer cover every argument
	nonLiterals bool
	// set when an argument type could not be resolved, e.g. a variable - it may
	// be anything, so the parameter must stay untyped
	unresolved bool
	// set when a call site lies outside tests; tests pass fixture values while
	// production may call through a container, form or hydrator instead
	production bool
}

// Types holds resolved parameter types keyed for fast lookup during apply.
// Method keys use the lowercased fully qualified class name.
type Types struct {
	methods   map[string]Resolved // class \x00 method \x00 position
	functions map[string]Resolved // function \x00 position
}

// Method returns the resolved type for a method/constructor parameter. Calls on
// an unresolved receiver, recorded by method name only, may target this method
// too, so they must agree with its type, else the parameter is blocked.
func (t Types) Method(class, method string, position int) (Resolved, bool) {
	resolved, ok := t.methods[methodKey(class, method, position)]
	byName, byNameOK := t.methods[methodKey("", method, position)]
	if !byNameOK {
		return resolved, ok
	}
	if !ok || len(resolved.Types) == 0 || len(byName.Types) == 0 || resolved.Types[0] != byName.Types[0] {
		return Resolved{}, true
	}
	return Resolved{Types: resolved.Types, Nullable: resolved.Nullable || byName.Nullable}, true
}

// Function returns the resolved type for a function parameter.
func (t Types) Function(name string, position int) (Resolved, bool) {
	resolved, ok := t.functions[functionKey(name, position)]
	return resolved, ok
}

// Resolve groups records per parameter into a resolved type: a single observed
// type, with null captured as nullability. Unions and parameters with an
// unresolved argument are blocked.
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
		types := make([]valuetype.Type, 0, len(group.types))
		for typeName := range group.types {
			types = append(types, typeName)
		}
		slices.Sort(types)
		types = dedupeClasses(types)

		// an unresolved argument may be anything, a parameter seen only from
		// tests may get other values in production, and a callable argument (a
		// closure) can take many shapes - all leave the parameter untyped
		if group.unresolved || !group.production || contains(types, valuetype.Callable) {
			resolved[key] = Resolved{}
			continue
		}

		// a DateTime argument leaves the parameter untyped, as it often accepts a
		// string or any DateTimeInterface too
		if slices.ContainsFunc(types, isDateTime) {
			resolved[key] = Resolved{}
			continue
		}

		nullable := contains(types, valuetype.Null)
		members := without(types, valuetype.Null)
		// only a single type is added, unions are left untyped
		if len(members) != 1 {
			resolved[key] = Resolved{}
			continue
		}
		resolved[key] = Resolved{Types: members, Nullable: nullable, Literals: literalValues(members, group)}
	}

	return resolved
}

// literalValues returns the string literals passed into a plain string
// parameter, when every argument was a string literal or null and there are a few
// distinct ones - an enum-like set worth a `'a'|'b'` doc type.
func literalValues(members []valuetype.Type, group *group) []string {
	if len(members) != 1 || members[0] != valuetype.String || group.nonLiterals {
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
		groups[key] = &group{types: map[valuetype.Type]struct{}{}, literals: map[string]struct{}{}}
	}
	if !record.InTest {
		groups[key].production = true
	}
	switch {
	case record.Type == valuetype.Unknown:
		groups[key].nonLiterals = true
		groups[key].unresolved = true
	case record.IsLiteral:
		groups[key].types[record.Type] = struct{}{}
		groups[key].literals[record.Literal] = struct{}{}
	default:
		groups[key].types[record.Type] = struct{}{}
		if record.Type == valuetype.String {
			groups[key].nonLiterals = true
		}
	}
}

// dedupeClasses drops object types naming the same class in another case, as
// PHP class names are case-insensitive. Types are sorted, so the kept casing is
// deterministic.
func dedupeClasses(types []valuetype.Type) []valuetype.Type {
	seen := map[string]bool{}
	kept := make([]valuetype.Type, 0, len(types))
	for _, typeName := range types {
		if typeName.IsObject() {
			key := phpast.ClassKey(typeName.Class())
			if seen[key] {
				continue
			}
			seen[key] = true
		}
		kept = append(kept, typeName)
	}
	return kept
}

func isDateTime(typeName valuetype.Type) bool {
	switch phpast.ClassKey(strings.TrimPrefix(typeName.Class(), "\\")) {
	case "datetime", "datetimeimmutable":
		return true
	}
	return false
}

// PHP method and function names are case-insensitive, so keys lowercase them.
func methodKey(class, method string, position int) string {
	return phpast.ClassKey(class) + "\x00" + strings.ToLower(method) + "\x00" + strconv.Itoa(position)
}

func functionKey(name string, position int) string {
	return strings.ToLower(name) + "\x00" + strconv.Itoa(position)
}

func contains(values []valuetype.Type, needle valuetype.Type) bool {
	return slices.Contains(values, needle)
}

func without(values []valuetype.Type, needle valuetype.Type) []valuetype.Type {
	kept := make([]valuetype.Type, 0, len(values))
	for _, value := range values {
		if value != needle {
			kept = append(kept, value)
		}
	}
	return kept
}
