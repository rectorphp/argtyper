// Package symbols builds a project-wide table of enum names and constant types,
// so argument values like `MAX_SIZE`, `self::LIMIT` or `Status::Active` resolve
// to a type the same way literals do.
package symbols

import (
	"maps"
	"strings"

	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/valuetype"
	"github.com/rectorphp/php-parser-in-go/pkg/ast"
)

// Table holds the project symbols gathered across every file.
type Table struct {
	enums       map[string]bool           // lowercased fully qualified enum name
	consts      map[string]valuetype.Type // CONST name -> type
	classConsts map[string]valuetype.Type // "lowercased fqcn\x00CONST" -> type
}

// New returns an empty table ready for Collect.
func New() *Table {
	return &Table{
		enums:       map[string]bool{},
		consts:      map[string]valuetype.Type{},
		classConsts: map[string]valuetype.Type{},
	}
}

// CollectSource parses one PHP source file and records its symbols. Parse
// errors are ignored so a single broken file never blocks the rest.
func (t *Table) CollectSource(src []byte) {
	root, err := phpast.Parse(src)
	if err != nil || root == nil {
		return
	}
	t.Collect(root)
}

// Collect records the enums and constants declared in one parsed file.
func (t *Table) Collect(root ast.Vertex) {
	t.walk(root, "", phpast.ResolveNames(root))
}

// Merge copies every symbol of other into t; a symbol already in t is replaced.
func (t *Table) Merge(other *Table) {
	maps.Copy(t.enums, other.enums)
	maps.Copy(t.consts, other.consts)
	maps.Copy(t.classConsts, other.classConsts)
}

func (t *Table) walk(node ast.Vertex, enclosing string, names map[ast.Vertex]string) {
	if node == nil {
		return
	}

	switch typed := node.(type) {
	case *ast.StmtClass, *ast.StmtTrait:
		enclosing = phpast.ResolvedName(names, typed)
	case *ast.StmtEnum:
		enclosing = phpast.ResolvedName(names, typed)
		t.enums[phpast.ClassKey(enclosing)] = true
	case *ast.StmtConstList:
		t.recordConsts(typed.Consts, enclosing)
	case *ast.StmtClassConstList:
		t.recordConsts(typed.Consts, enclosing)
	case *ast.ExprFunctionCall:
		t.recordDefine(typed)
	}

	for _, child := range phpast.Children(node) {
		t.walk(child, enclosing, names)
	}
}

// recordConsts stores each constant's type; enclosing is "" for global `const`.
func (t *Table) recordConsts(consts []ast.Vertex, enclosing string) {
	for _, constNode := range consts {
		constant, ok := constNode.(*ast.StmtConstant)
		if !ok {
			continue
		}
		name := phpast.ShortName(constant.Name)
		typeName := valuetype.Of(constant.Expr)
		if name == "" || typeName == valuetype.Unknown || typeName == valuetype.Null {
			continue
		}
		if enclosing == "" {
			t.consts[name] = typeName
		} else {
			t.classConsts[classConstKey(enclosing, name)] = typeName
		}
	}
}

// recordDefine stores a `define('NAME', value)` global constant.
func (t *Table) recordDefine(call *ast.ExprFunctionCall) {
	if !strings.EqualFold(phpast.ShortName(call.Function), "define") || len(call.Args) < 2 {
		return
	}
	nameArg, ok := call.Args[0].(*ast.Argument)
	if !ok {
		return
	}
	name := stringLiteral(nameArg.Expr)
	if name == "" {
		return
	}
	valueArg, ok := call.Args[1].(*ast.Argument)
	if !ok {
		return
	}
	typeName := valuetype.Of(valueArg.Expr)
	if typeName == valuetype.Unknown || typeName == valuetype.Null {
		return
	}
	t.consts[name] = typeName
}

// TypeOfExpr resolves the type of a value expression, using the table for
// constants and enum cases and falling back to valuetype for everything else.
// enclosing is the fully qualified class the expression sits in, for
// `self::`/`static::`, and names is the ResolveNames map of its file.
func (t *Table) TypeOfExpr(expr ast.Vertex, enclosing string, names map[ast.Vertex]string) valuetype.Type {
	switch typed := expr.(type) {
	case *ast.ExprConstFetch:
		if name := phpast.ShortName(typed.Const); t.consts[name] != valuetype.Unknown {
			return t.consts[name]
		}
	case *ast.ExprClassConstFetch:
		if resolved := t.classConst(typed, enclosing, names); resolved != valuetype.Unknown {
			return resolved
		}
	}
	return valuetype.Of(expr)
}

// classConst resolves `Class::CONST` and `Enum::Case` from the table.
func (t *Table) classConst(fetch *ast.ExprClassConstFetch, enclosing string, names map[ast.Vertex]string) valuetype.Type {
	constName := phpast.ShortName(fetch.Const)
	if constName == "" || strings.EqualFold(constName, "class") {
		return valuetype.Unknown
	}

	var class string
	switch strings.ToLower(phpast.ShortName(fetch.Class)) {
	case "self", "static":
		class = enclosing
	case "parent":
		return valuetype.Unknown
	default:
		class = phpast.ResolvedName(names, fetch.Class)
	}
	if class == "" {
		return valuetype.Unknown
	}

	if t.enums[phpast.ClassKey(class)] {
		return valuetype.Object(class)
	}
	return t.classConsts[classConstKey(class, constName)]
}

func stringLiteral(expr ast.Vertex) string {
	str, ok := expr.(*ast.ScalarString)
	if !ok {
		return ""
	}
	return strings.Trim(string(str.Value), `'"`)
}

func classConstKey(class, name string) string {
	// constant names are case-sensitive, only the class part is normalized
	return phpast.ClassKey(class) + "\x00" + name
}
