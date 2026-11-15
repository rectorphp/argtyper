package phpast

import (
	"regexp"
	"strings"

	"github.com/rectorphp/php-parser-in-go/pkg/ast"
	"github.com/rectorphp/php-parser-in-go/pkg/visitor"
)

// entityTag matches a Doctrine `@Entity` or `@ORM\Entity` docblock annotation,
// but not a longer tag such as `@ORM\EntityListeners`.
var entityTag = regexp.MustCompile(`@(?:\w+\\)*Entity(?:[^\w\\]|$)`)

// IsEntity reports whether a class is marked as a Doctrine entity, by an
// `#[ORM\Entity]` attribute or an `@ORM\Entity` annotation.
func IsEntity(class ast.Vertex) bool {
	typed, ok := class.(*ast.StmtClass)
	if !ok {
		return false
	}
	for _, groupNode := range typed.AttrGroups {
		group, ok := groupNode.(*ast.AttributeGroup)
		if !ok {
			continue
		}
		for _, attributeNode := range group.Attrs {
			if attribute, ok := attributeNode.(*ast.Attribute); ok && strings.EqualFold(ShortName(attribute.Name), "Entity") {
				return true
			}
		}
	}
	return entityTag.MatchString(visitor.GetDocCommentText(typed))
}
