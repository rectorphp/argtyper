// Package apply fills missing parameter types in function, method and
// constructor definitions from the resolved argument types.
package apply

import (
	"strings"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// Source adds parameter types to a single PHP source file. It returns the new
// source, the number of types added, and whether the file changed. On a parse
// error the original source is returned unchanged.
func Source(src []byte, types aggregate.Types) (string, int, bool) {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return string(src), 0, false
	}

	applier := &applier{types: types}
	applier.walk(root, nil)

	if applier.added == 0 {
		return string(src), 0, false
	}

	return phpast.Print(root), applier.added, true
}

type applier struct {
	types aggregate.Types
	added int
}

func (a *applier) walk(node ast.Vertex, class *ast.StmtClass) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass:
		class = typed
	case *ast.StmtFunction:
		a.applyFunction(typed)
	case *ast.StmtClassMethod:
		a.applyMethod(typed, class)
	}

	for _, child := range phpast.Children(node) {
		a.walk(child, class)
	}
}

func (a *applier) applyFunction(function *ast.StmtFunction) {
	name := phpast.ShortName(function.Name)
	for position, paramNode := range function.Params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || !typeable(param) {
			continue
		}
		if resolved, ok := a.types.Function(name, position); ok {
			a.setType(param, resolved)
		}
	}
}

func (a *applier) applyMethod(method *ast.StmtClassMethod, class *ast.StmtClass) {
	name := phpast.ShortName(method.Name)
	if isMagicExceptConstructor(name) {
		return
	}
	if !overridable(method, class) {
		return
	}

	className := ""
	if class != nil {
		className = phpast.ShortName(class.Name)
	}

	for position, paramNode := range method.Params {
		param, ok := paramNode.(*ast.Parameter)
		if !ok || !typeable(param) {
			continue
		}
		if resolved, ok := a.types.Method(className, name, position); ok {
			a.setType(param, resolved)
		}
	}
}

func (a *applier) setType(param *ast.Parameter, resolved aggregate.Resolved) {
	nullable := resolved.Nullable || hasNullDefault(param)
	typeText := typeText(resolved.Type)

	if nullable {
		param.Type = &ast.Nullable{Expr: &ast.Identifier{Value: []byte(typeText + " ")}}
	} else {
		param.Type = &ast.Identifier{Value: []byte(typeText + " ")}
	}
	a.added++
}

// typeText turns a resolved type into the text written into source. The trailing
// space that separates the type from the variable is added by the caller.
func typeText(resolved string) string {
	if strings.HasPrefix(resolved, "object:") {
		return "\\" + strings.TrimPrefix(resolved, "object:")
	}
	return resolved
}

// typeable reports whether a parameter can receive a type: none yet and not
// variadic, where positions would no longer line up.
func typeable(param *ast.Parameter) bool {
	return param.Type == nil && param.VariadicTkn == nil
}

// overridable reports whether typing this method is safe without a reflection
// based parent lookup: constructors and private methods never override, and a
// class with no parent or interface cannot override either.
func overridable(method *ast.StmtClassMethod, class *ast.StmtClass) bool {
	if phpast.ShortName(method.Name) == "__construct" {
		return true
	}
	for _, modifierNode := range method.Modifiers {
		if modifier, ok := modifierNode.(*ast.Identifier); ok {
			if strings.ToLower(string(modifier.Value)) == "private" {
				return true
			}
		}
	}
	if class == nil {
		return true
	}
	return class.Extends == nil && len(class.Implements) == 0
}

func isMagicExceptConstructor(name string) bool {
	return name != "__construct" && strings.HasPrefix(name, "__")
}

func hasNullDefault(param *ast.Parameter) bool {
	constFetch, ok := param.DefaultValue.(*ast.ExprConstFetch)
	if !ok {
		return false
	}
	return strings.ToLower(phpast.ShortName(constFetch.Const)) == "null"
}
