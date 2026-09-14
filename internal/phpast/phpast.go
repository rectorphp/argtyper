// Package phpast holds small helpers over the php-parser-in-go AST:
// parsing, printing and node inspection shared by collect and apply.
package phpast

import (
	"bytes"
	"reflect"
	"regexp"
	"strings"

	"github.com/rectorphp/php-parser-in-go/pkg/ast"
	"github.com/rectorphp/php-parser-in-go/pkg/conf"
	"github.com/rectorphp/php-parser-in-go/pkg/parser"
	"github.com/rectorphp/php-parser-in-go/pkg/token"
	"github.com/rectorphp/php-parser-in-go/pkg/version"
	"github.com/rectorphp/php-parser-in-go/pkg/visitor/nsresolver"
	"github.com/rectorphp/php-parser-in-go/pkg/visitor/printer"
	"github.com/rectorphp/php-parser-in-go/pkg/visitor/traverser"
)

var docParamPattern = regexp.MustCompile(`@param\b[^\n$]*\$(\w+)`)

var phpVersion, _ = version.New("8.3")

var vertexType = reflect.TypeOf((*ast.Vertex)(nil)).Elem()

// Parse turns PHP source into an AST root.
func Parse(src []byte) (ast.Vertex, error) {
	return parser.Parse(src, conf.Config{Version: phpVersion})
}

// Print reprints an AST back to PHP source. Unchanged nodes keep their
// original formatting via the tokens attached to them.
func Print(root ast.Vertex) string {
	var buf bytes.Buffer
	root.Accept(printer.NewPrinter(&buf))
	return buf.String()
}

// Children returns the direct child nodes of any AST node using reflection,
// so callers do not have to enumerate every one of the ~150 node types.
func Children(node ast.Vertex) []ast.Vertex {
	value := reflect.ValueOf(node).Elem()

	var children []ast.Vertex
	for i := 0; i < value.NumField(); i++ {
		field := value.Field(i)

		switch field.Kind() {
		case reflect.Interface:
			if !field.IsNil() {
				if child, ok := field.Interface().(ast.Vertex); ok {
					children = append(children, child)
				}
			}
		case reflect.Slice:
			if !field.Type().Elem().Implements(vertexType) {
				continue
			}
			for j := 0; j < field.Len(); j++ {
				item := field.Index(j)
				if !item.IsNil() {
					children = append(children, item.Interface().(ast.Vertex))
				}
			}
		}
	}

	return children
}

// ShortName returns the last part of a name node, e.g. "Foo" for "App\Foo".
// Empty string when the node is not a plain name (anonymous class, variable).
func ShortName(node ast.Vertex) string {
	switch typed := node.(type) {
	case *ast.Identifier:
		return string(typed.Value)
	case *ast.Name:
		if len(typed.Parts) == 0 {
			return ""
		}
		return ShortName(typed.Parts[len(typed.Parts)-1])
	case *ast.NameFullyQualified:
		if len(typed.Parts) == 0 {
			return ""
		}
		return ShortName(typed.Parts[len(typed.Parts)-1])
	case *ast.NameRelative:
		if len(typed.Parts) == 0 {
			return ""
		}
		return ShortName(typed.Parts[len(typed.Parts)-1])
	case *ast.NamePart:
		return string(typed.Value)
	default:
		return ""
	}
}

// ResolveNames returns the fully qualified name (without a leading backslash)
// of every class-name node in the file, resolved from its namespace and `use`
// statements by the parser's namespace resolver. Class/enum/trait declarations
// are keyed by their statement node.
func ResolveNames(root ast.Vertex) map[ast.Vertex]string {
	resolver := nsresolver.NewNamespaceResolver()
	traverser.NewTraverser(resolver).Traverse(root)
	return resolver.ResolvedNames
}

// ObjectClassNode returns the class-name node of a value that produces an
// object - `new X()` or a `X::CASE` enum case - or nil for anything else.
func ObjectClassNode(expr ast.Vertex) ast.Vertex {
	switch typed := expr.(type) {
	case *ast.ExprNew:
		return typed.Class
	case *ast.ExprClassConstFetch:
		return typed.Class
	}
	return nil
}

// DocParamNames returns the parameter names (without `$`) that a function or
// method's doc comment declares with an `@param` tag. Those parameters carry a
// documented type the tool cannot see, so they are left untouched rather than
// narrowed from observed call sites.
func DocParamNames(node ast.Vertex) map[string]bool {
	names := map[string]bool{}
	for _, match := range docParamPattern.FindAllStringSubmatch(docComment(node), -1) {
		names[match[1]] = true
	}
	return names
}

// docComment returns the doc comment attached before a function or method.
func docComment(node ast.Vertex) string {
	var first *token.Token
	switch typed := node.(type) {
	case *ast.StmtClassMethod:
		if len(typed.Modifiers) > 0 {
			first = identifierToken(typed.Modifiers[0])
		}
		if first == nil {
			first = typed.FunctionTkn
		}
	case *ast.StmtFunction:
		first = typed.FunctionTkn
	}
	if first == nil {
		return ""
	}

	for _, free := range first.FreeFloating {
		if free.ID == token.T_DOC_COMMENT {
			return string(free.Value)
		}
	}
	return ""
}

func identifierToken(node ast.Vertex) *token.Token {
	if identifier, ok := node.(*ast.Identifier); ok {
		return identifier.IdentifierTkn
	}
	return nil
}

// IsThisVariable reports whether a node is the `$this` variable.
func IsThisVariable(node ast.Vertex) bool {
	return VariableName(node) == "this"
}

// VariableName returns the name of an $variable node without the leading `$`,
// or empty string when the node is not a plain variable.
func VariableName(node ast.Vertex) string {
	variable, ok := node.(*ast.ExprVariable)
	if !ok {
		return ""
	}
	identifier, ok := variable.Name.(*ast.Identifier)
	if !ok {
		return ""
	}
	return strings.TrimPrefix(string(identifier.Value), "$")
}
