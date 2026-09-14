// Package inherit builds a class inheritance table - each class's parent,
// interfaces and declared method names - so apply can tell whether typing a
// method would change the signature of a method it inherits from an ancestor
// (including one in /vendor), which must be left alone.
package inherit

import (
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// class holds the ancestors and declared methods of one class or interface.
type class struct {
	parent     string   // fully qualified parent class, "" if none
	interfaces []string // fully qualified implemented/extended interfaces
	methods    map[string]bool
}

// Table maps a fully qualified class name to its inheritance data.
type Table struct {
	classes map[string]*class
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
	names := phpast.ResolveNames(root)
	t.walk(root, names)
}

func (t *Table) walk(node ast.Vertex, names map[ast.Vertex]string) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		t.add(names[typed], names[typed.Extends], typed.Implements, typed, names)
	case *ast.StmtInterface:
		// interface parents are declared with `extends`; treat them like
		// implemented interfaces so method lookups walk them too.
		t.add(names[typed], "", typed.Extends, typed, names)
	}

	for _, child := range phpast.Children(node) {
		t.walk(child, names)
	}
}

func (t *Table) add(fqcn, parent string, interfaces []ast.Vertex, body ast.Vertex, names map[ast.Vertex]string) {
	if fqcn == "" {
		return
	}

	entry := &class{parent: parent, methods: map[string]bool{}}
	for _, interfaceNode := range interfaces {
		if name := names[interfaceNode]; name != "" {
			entry.interfaces = append(entry.interfaces, name)
		}
	}
	for _, member := range phpast.Children(body) {
		if method, ok := member.(*ast.StmtClassMethod); ok {
			if name := phpast.ShortName(method.Name); name != "" {
				entry.methods[name] = true
			}
		}
	}

	t.classes[fqcn] = entry
}

// Overrides reports whether a method of the given class is also declared by an
// ancestor (a parent class or interface, transitively), and whether the whole
// ancestor chain was resolved. When resolved is false, an unknown ancestor - a
// class the scan never saw, typically in /vendor - may still declare it, so the
// caller should treat the method as unsafe to change.
func (t *Table) Overrides(fqcn, method string) (overrides bool, resolved bool) {
	entry, ok := t.classes[fqcn]
	if !ok {
		return false, false
	}

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
		visit(ancestor.parent)
		for _, interfaceName := range ancestor.interfaces {
			visit(interfaceName)
		}
	}

	visit(entry.parent)
	for _, interfaceName := range entry.interfaces {
		visit(interfaceName)
	}
	return overrides, resolved
}
