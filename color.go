package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

// ANSI SGR codes for the summary and diff output. Emitted only when
// colorEnabled is true, so a redirected or piped run stays plain text.
const (
	ansiReset   = "\x1b[0m"
	ansiBold    = "\x1b[1m"
	ansiDim     = "\x1b[2m"
	ansiRed     = "\x1b[31m"
	ansiGreen   = "\x1b[32m"
	ansiYellow  = "\x1b[33m"
	ansiMagenta = "\x1b[35m"
	ansiCyan    = "\x1b[36m"
)

// stdoutIsTerminal reports whether stdout is an interactive terminal. The diff
// and summary print to stdout, so color keys off it; a redirected run gets
// plain text and the NO_COLOR convention also disables it.
var stdoutIsTerminal = detectStdout()

var colorEnabled = stdoutIsTerminal && os.Getenv("NO_COLOR") == ""

func detectStdout() bool {
	fileInfo, err := os.Stdout.Stat()
	if err != nil {
		return false
	}
	return fileInfo.Mode()&os.ModeCharDevice != 0
}

// paint wraps text in an ANSI code when color is enabled, untouched otherwise.
func paint(code string, text string) string {
	if !colorEnabled {
		return text
	}
	return code + text + ansiReset
}

// classifyType buckets a written type declaration ("int", "?string", "\Foo",
// "int|null") into a category for the summary.
func classifyType(text string) string {
	base := strings.TrimPrefix(text, "?")
	base = strings.TrimSuffix(base, "|null")
	if strings.Contains(base, "|") {
		return "union"
	}
	switch base {
	case "int", "float", "string", "bool":
		return "scalar"
	case "array", "iterable":
		return "array"
	}
	if strings.HasPrefix(base, "\\") {
		return "object"
	}
	return "other"
}

// category rows are printed in this fixed order, each with its own color.
var categoryOrder = []string{"scalar", "object", "array", "union", "other"}

var categoryLabel = map[string]string{
	"scalar": "scalar types",
	"object": "object types",
	"array":  "array types",
	"union":  "union types",
	"other":  "other types",
}

var categoryColor = map[string]string{
	"scalar": ansiCyan,
	"object": ansiGreen,
	"array":  ansiYellow,
	"union":  ansiMagenta,
	"other":  ansiDim,
}

// printOverview prints a colored breakdown of the added types by category.
func printOverview(addedTypes []string) {
	counts := map[string]int{}
	for _, text := range addedTypes {
		counts[classifyType(text)]++
	}

	fmt.Println("\n   Added types by category:")
	for _, key := range categoryOrder {
		count := counts[key]
		if count == 0 {
			continue
		}
		label := fmt.Sprintf("%-13s", categoryLabel[key])
		fmt.Printf("     %s %s\n", paint(categoryColor[key], label), paint(ansiBold, strconv.Itoa(count)))
	}
}

// colorizePatch colors a dry-run diff: added lines green, removed lines red.
func colorizePatch(patch string) string {
	if !colorEnabled {
		return patch
	}
	lines := strings.Split(patch, "\n")
	for index, line := range lines {
		switch {
		case strings.HasPrefix(line, "  + "):
			lines[index] = paint(ansiGreen, line)
		case strings.HasPrefix(line, "  - "):
			lines[index] = paint(ansiRed, line)
		}
	}
	return strings.Join(lines, "\n")
}
