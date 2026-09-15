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

// docParamLine matches a plain `@param <type> $name` line with no trailing
// description, so only a fully redundant tag is removed.
var docParamLine = regexp.MustCompile(`^\s*\*?\s*@param\s+(\S+)\s+\$(\w+)\s*$`)

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

// StripRedundantDocParams removes `@param` lines from a function or method's doc
// comment when the type they declare equals the type just added for that
// parameter (added maps parameter name to the written type text). When nothing
// meaningful remains, the whole doc comment is removed.
func StripRedundantDocParams(node ast.Vertex, added map[string]string) {
	if len(added) == 0 {
		return
	}
	leading := leadingToken(node)
	if leading == nil {
		return
	}

	index := -1
	for i, free := range leading.FreeFloating {
		if free.ID == token.T_DOC_COMMENT {
			index = i
			break
		}
	}
	if index < 0 {
		return
	}

	stripped, changed, empty := stripDocParamLines(string(leading.FreeFloating[index].Value), added)
	if !changed {
		return
	}
	if !empty {
		leading.FreeFloating[index].Value = []byte(stripped)
		return
	}

	// The doc comment is now empty, so drop it along with the blank line it
	// leaves behind (the whitespace token in front of it).
	drop := map[int]bool{index: true}
	if index > 0 && leading.FreeFloating[index-1].ID == token.T_WHITESPACE {
		drop[index-1] = true
	}
	kept := leading.FreeFloating[:0:0]
	for i, free := range leading.FreeFloating {
		if !drop[i] {
			kept = append(kept, free)
		}
	}
	leading.FreeFloating = kept
}

// stripDocParamLines removes redundant @param lines, reporting whether anything
// changed and whether the doc comment has no content left.
func stripDocParamLines(doc string, added map[string]string) (result string, changed, empty bool) {
	lines := strings.Split(doc, "\n")
	kept := make([]string, 0, len(lines))
	for i := 0; i < len(lines); i++ {
		line := lines[i]
		if match := docParamLine.FindStringSubmatch(line); match != nil {
			if native, ok := added[match[2]]; ok && normalizeDocType(match[1]) == normalizeDocType(native) {
				changed = true
				// also drop a blank comment line that followed the tag
				if i+1 < len(lines) && isBlankCommentLine(lines[i+1]) {
					i++
				}
				continue
			}
		}
		kept = append(kept, line)
	}
	if !changed {
		return doc, false, false
	}
	return strings.Join(kept, "\n"), true, docIsEmpty(kept)
}

// isBlankCommentLine reports whether a doc line carries only the `*` marker.
func isBlankCommentLine(line string) bool {
	text := strings.TrimSpace(line)
	return text == "" || text == "*"
}

// docIsEmpty reports whether the remaining lines carry no content beyond the
// comment markers.
func docIsEmpty(lines []string) bool {
	for _, line := range lines {
		text := strings.TrimSpace(line)
		text = strings.TrimPrefix(text, "/**")
		text = strings.TrimSuffix(text, "*/")
		text = strings.TrimPrefix(text, "*")
		if strings.TrimSpace(text) != "" {
			return false
		}
	}
	return true
}

func normalizeDocType(name string) string {
	return strings.TrimPrefix(name, "\\")
}

// leadingToken returns the first token of a function or method, which carries
// the doc comment in its leading free-floating tokens.
func leadingToken(node ast.Vertex) *token.Token {
	switch typed := node.(type) {
	case *ast.StmtClassMethod:
		if len(typed.Modifiers) > 0 {
			if first := identifierToken(typed.Modifiers[0]); first != nil {
				return first
			}
		}
		return typed.FunctionTkn
	case *ast.StmtFunction:
		return typed.FunctionTkn
	}
	return nil
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
