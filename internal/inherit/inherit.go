// Package inherit builds a class inheritance table - each class's parent,
// interfaces, used traits and declared method names - so apply can tell whether
// typing a method would change the signature of a method it inherits from an
// ancestor (including one in /vendor), or one a child class overrides, which
// must be left alone.
package inherit

import (
	"fmt"
	"maps"
	"strings"
	"sync"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// class holds the ancestors and declared methods of one class or interface.
type class struct {
	parent     string   // lowercased fully qualified parent class, "" if none
	interfaces []string // lowercased fully qualified implemented/extended interfaces
	traits     []string // lowercased fully qualified used traits
	methods    map[string]bool
}

// Table maps a lowercased fully qualified class name to its inheritance data.
type Table struct {
	classes map[string]*class

	childrenOnce sync.Once
	children     map[string][]string // ancestor -> classes that extend, implement or use it
}

// New returns an empty table ready for CollectSource.
func New() *Table {
	return &Table{classes: map[string]*class{}}
}

// CollectSource parses one PHP source file and records the classes and
// interfaces it declares. Parse errors are ignored.
func (t *Table) CollectSource(src []byte) {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return
	}
	t.Collect(root)
}

// Collect records the classes and interfaces declared in one parsed file.
func (t *Table) Collect(root ast.Vertex) {
	t.walk(root, phpast.ResolveNames(root))
}

// Merge copies every class of other into t; a class already in t is replaced.
func (t *Table) Merge(other *Table) {
	maps.Copy(t.classes, other.classes)
}

func (t *Table) walk(node ast.Vertex, names map[ast.Vertex]string) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		name := names[typed]
		if typed.Name == nil {
			// an anonymous class has no name, but may still override a method
			name = fmt.Sprintf("class@anonymous%p", typed)
		}
		t.add(name, names[typed.Extends], typed.Implements, typed, names)
	case *ast.StmtInterface:
		// interface parents are declared with `extends`; treat them like
		// implemented interfaces so method lookups walk them too.
		t.add(names[typed], "", typed.Extends, typed, names)
	case *ast.StmtEnum:
		t.add(names[typed], "", typed.Implements, typed, names)
	case *ast.StmtTrait:
		t.add(names[typed], "", nil, typed, names)
	}

	for _, child := range phpast.Children(node) {
		t.walk(child, names)
	}
}

func (t *Table) add(fqcn, parent string, interfaces []ast.Vertex, body ast.Vertex, names map[ast.Vertex]string) {
	if fqcn == "" {
		return
	}

	entry := &class{parent: phpast.ClassKey(parent), methods: map[string]bool{}}
	for _, interfaceNode := range interfaces {
		if name := names[interfaceNode]; name != "" {
			entry.interfaces = append(entry.interfaces, phpast.ClassKey(name))
		}
	}
	for _, member := range phpast.Children(body) {
		switch typed := member.(type) {
		case *ast.StmtClassMethod:
			if name := phpast.ShortName(typed.Name); name != "" {
				entry.methods[strings.ToLower(name)] = true
			}
		case *ast.StmtTraitUse:
			for _, traitNode := range typed.Traits {
				if name := names[traitNode]; name != "" {
					entry.traits = append(entry.traits, phpast.ClassKey(name))
				}
			}
		}
	}

	t.classes[phpast.ClassKey(fqcn)] = entry
}

// Overrides reports whether a method of the given class is also declared by an
// ancestor (a parent class, interface or used trait, transitively), and whether the whole
// ancestor chain was resolved. When resolved is false, an unknown ancestor - a
// class the scan never saw, typically in /vendor - may still declare it, so the
// caller should treat the method as unsafe to change.
func (t *Table) Overrides(fqcn, method string) (overrides bool, resolved bool) {
	// PHP class and method names are case-insensitive
	entry, ok := t.classes[phpast.ClassKey(fqcn)]
	if !ok {
		return false, false
	}
	method = strings.ToLower(method)

	resolved = true
	seen := map[string]bool{}
	var visit func(name string)
	visit = func(name string) {
		if name == "" || seen[name] {
			return
		}
		seen[name] = true

		ancestor, ok := t.classes[name]
		if !ok {
			resolved = false
			return
		}
		if ancestor.methods[method] {
			overrides = true
		}
		visitAncestors(ancestor, visit)
	}

	visitAncestors(entry, visit)
	return overrides, resolved
}

func visitAncestors(entry *class, visit func(name string)) {
	visit(entry.parent)
	for _, interfaceName := range entry.interfaces {
		visit(interfaceName)
	}
	for _, traitName := range entry.traits {
		visit(traitName)
	}
}

// OverriddenByChild reports whether a class that extends, implements or uses the
// given class, transitively, declares the method again. Typing the method would
// then change a signature the child relies on.
func (t *Table) OverriddenByChild(fqcn, method string) bool {
	t.childrenOnce.Do(t.buildChildren)
	method = strings.ToLower(method)

	seen := map[string]bool{}
	var visit func(name string) bool
	visit = func(name string) bool {
		for _, childName := range t.children[name] {
			if seen[childName] {
				continue
			}
			seen[childName] = true
			if t.declares(childName, method, map[string]bool{}) || visit(childName) {
				return true
			}
		}
		return false
	}
	return visit(phpast.ClassKey(fqcn))
}

// DeclaringClass returns the class that declares a method called on fqcn: fqcn
// itself, a used trait or the closest parent class. A call on a child class
// then counts for the method its parent declares. Unknown ones return fqcn.
func (t *Table) DeclaringClass(fqcn, method string) string {
	method = strings.ToLower(method)
	seen := map[string]bool{}
	for name := phpast.ClassKey(fqcn); name != "" && !seen[name]; {
		seen[name] = true
		entry, ok := t.classes[name]
		if !ok {
			break
		}
		if entry.methods[method] {
			return name
		}
		for _, traitName := range entry.traits {
			if t.declares(traitName, method, map[string]bool{}) {
				return traitName
			}
		}
		name = entry.parent
	}
	return fqcn
}

// declares reports whether a class or trait declares a method itself or through
// the traits it uses.
func (t *Table) declares(name, method string, seen map[string]bool) bool {
	entry, ok := t.classes[name]
	if !ok || seen[name] {
		return false
	}
	seen[name] = true
	if entry.methods[method] {
		return true
	}
	for _, traitName := range entry.traits {
		if t.declares(traitName, method, seen) {
			return true
		}
	}
	return false
}

func (t *Table) buildChildren() {
	t.children = map[string][]string{}
	for name, entry := range t.classes {
		visitAncestors(entry, func(ancestor string) {
			if ancestor != "" {
				t.children[ancestor] = append(t.children[ancestor], name)
			}
		})
	}
}
