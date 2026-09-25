// Package phpast holds small helpers over the php-parser-in-go AST:
// parsing, printing and node inspection shared by collect and apply.
package phpast

import (
	"bytes"
	"reflect"
	"regexp"
	"slices"
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

// simpleLiteral limits string literals to values that can be written into a
// doc comment as-is, with no quotes, escapes or whitespace to carry over.
var simpleLiteral = regexp.MustCompile(`^[\w.:/-]*$`)

var phpVersion, _ = version.New("8.3")

var vertexType = reflect.TypeFor[ast.Vertex]()

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
	for _, field := range value.Fields() {

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
			if native, ok := added[match[2]]; ok && docTypeRedundant(match[1], native) {
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

// normalizeDocType reduces a type to a form that compares equal regardless of
// union member order and of whether a class is written as a short name or a
// fully qualified one: each member is cut to its last name part and the members
// are sorted.
// docTypeRedundant reports whether a `@param` type adds nothing over the type
// just written on the parameter: either it equals the added type, or it is
// `mixed`, which carries no information once a real type is present.
func docTypeRedundant(docType, native string) bool {
	if strings.EqualFold(docType, "mixed") {
		return true
	}
	return normalizeDocType(docType) == normalizeDocType(native)
}

func normalizeDocType(name string) string {
	parts := strings.Split(name, "|")
	for i, part := range parts {
		if index := strings.LastIndex(part, "\\"); index >= 0 {
			part = part[index+1:]
		}
		parts[i] = part
	}
	slices.Sort(parts)
	return strings.Join(parts, "|")
}

// StringLiteral returns the value of a plain quoted string literal like 'eq' or
// "eq". Strings with escapes, interpolation or other special characters are
// rejected, so the value is safe to write into a doc comment.
func StringLiteral(expr ast.Vertex) (string, bool) {
	scalar, ok := expr.(*ast.ScalarString)
	if !ok || scalar.MinusTkn != nil {
		return "", false
	}
	raw := string(scalar.Value)
	if len(raw) < 2 || (raw[0] != '\'' && raw[0] != '"') || raw[len(raw)-1] != raw[0] {
		return "", false
	}
	value := raw[1 : len(raw)-1]
	if !simpleLiteral.MatchString(value) {
		return "", false
	}
	return value, true
}

// AddDocParams adds `@param <type> $<name>` lines to a function or method's doc
// comment, creating the doc comment when there is none. Parameters that already
// have a @param line are left alone. params maps parameter name to doc type and
// order lists the names in parameter order.
func AddDocParams(node ast.Vertex, params map[string]string, order []string) {
	leading := leadingToken(node)
	if leading == nil || len(params) == 0 {
		return
	}

	index := -1
	for i, free := range leading.FreeFloating {
		if free.ID == token.T_DOC_COMMENT {
			index = i
			break
		}
	}

	doc := ""
	if index >= 0 {
		doc = string(leading.FreeFloating[index].Value)
	}

	var tags []string
	for _, name := range order {
		docType, ok := params[name]
		if !ok || hasDocParam(doc, name) {
			continue
		}
		tags = append(tags, "@param "+docType+" $"+name)
	}
	if len(tags) == 0 {
		return
	}

	indent := indentation(leading.FreeFloating)
	if index >= 0 {
		leading.FreeFloating[index].Value = []byte(appendDocTags(doc, tags, indent))
		return
	}

	newDoc := appendDocTags("/**\n"+indent+" */", tags, indent)
	leading.FreeFloating = append(leading.FreeFloating,
		&token.Token{ID: token.T_DOC_COMMENT, Value: []byte(newDoc)},
		&token.Token{ID: token.T_WHITESPACE, Value: []byte("\n" + indent)},
	)
}

// hasDocParam reports whether a doc comment already has a @param line for the
// named parameter, whatever its type or description.
func hasDocParam(doc, name string) bool {
	return regexp.MustCompile(`@param\b[^\n]*\$` + regexp.QuoteMeta(name) + `\b`).MatchString(doc)
}

// appendDocTags inserts tag lines right before the closing `*/`, turning a
// single-line doc comment into a multi-line one first.
func appendDocTags(doc string, tags []string, indent string) string {
	if !strings.Contains(doc, "\n") {
		content := strings.TrimSpace(strings.TrimSuffix(strings.TrimPrefix(doc, "/**"), "*/"))
		doc = "/**\n"
		if content != "" {
			doc += indent + " * " + content + "\n"
		}
		doc += indent + " */"
	}

	closing := strings.LastIndex(doc, "\n")
	var lines strings.Builder
	for _, tag := range tags {
		lines.WriteString("\n" + indent + " * " + tag)
	}
	return doc[:closing] + lines.String() + doc[closing:]
}

// indentation returns the whitespace after the last line break in the leading
// tokens of a function or method, which is its indentation.
func indentation(tokens []*token.Token) string {
	for _, free := range slices.Backward(tokens) {
		if free.ID != token.T_WHITESPACE {
			continue
		}
		value := string(free.Value)
		if index := strings.LastIndex(value, "\n"); index >= 0 {
			return value[index+1:]
		}
	}
	return ""
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
