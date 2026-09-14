// Package diff renders the changed lines between two versions of a file.
package diff

import (
	"fmt"
	"strings"
)

// Lines returns a per-line diff of the lines that differ between before and
// after, prefixed with the file path. ArgTyper only edits parameter lines in
// place, so line counts match and a line-by-line comparison is enough. The
// second return value reports whether anything differs.
func Lines(path, before, after string) (string, bool) {
	beforeLines := strings.Split(before, "\n")
	afterLines := strings.Split(after, "\n")

	var b strings.Builder
	changed := false
	for i := 0; i < len(beforeLines) && i < len(afterLines); i++ {
		if beforeLines[i] == afterLines[i] {
			continue
		}
		if !changed {
			fmt.Fprintf(&b, "%s\n", path)
			changed = true
		}
		fmt.Fprintf(&b, "  %d\n", i+1)
		fmt.Fprintf(&b, "  - %s\n", beforeLines[i])
		fmt.Fprintf(&b, "  + %s\n", afterLines[i])
	}

	return b.String(), changed
}
