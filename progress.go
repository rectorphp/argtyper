package main

import (
	"fmt"
	"os"
	"strings"
)

// progressBarWidth is the number of cells in the rendered bar, matching the
// default width Symfony's progress bar uses.
const progressBarWidth = 28

// labelColumnWidth pads the phase label so the successive phases' bars line up
// in one column. It is the length of the widest label, "collecting".
const labelColumnWidth = 10

// stderrIsTerminal reports whether stderr is an interactive terminal. The live
// bar redraws in place with a carriage return, which only reads correctly on a
// terminal; a redirected or piped run stays plain.
var stderrIsTerminal = detectTerminal()

func detectTerminal() bool {
	fileInfo, err := os.Stderr.Stat()
	if err != nil {
		return false
	}
	return fileInfo.Mode()&os.ModeCharDevice != 0
}

// renderProgressBar draws a rector-style bar keyed by phase label, e.g.
//
//	collecting 1080/1659 [==============>-------------]  65%
//
// The leading carriage return rewrites the line in place on each tick.
func renderProgressBar(label string, done int, total int) string {
	percent := 0
	filled := 0
	if total > 0 {
		percent = done * 100 / total
		filled = done * progressBarWidth / total
	}

	var bar string
	if filled >= progressBarWidth {
		bar = strings.Repeat("=", progressBarWidth)
	} else {
		bar = strings.Repeat("=", filled) + ">" + strings.Repeat("-", progressBarWidth-filled-1)
	}
	return fmt.Sprintf("\r%-*s %d/%d [%s] %3d%%", labelColumnWidth, label, done, total, bar, percent)
}

// progressEach reads each file and hands its source to fn, drawing a live
// progress bar labeled label on stderr while it goes. The bar is skipped on a
// non-terminal run so piped output stays clean.
func progressEach(label string, files []string, fn func(file string, src []byte) error) error {
	total := len(files)
	for index, file := range files {
		src, err := os.ReadFile(file)
		if err != nil {
			return err
		}
		if err := fn(file, src); err != nil {
			return err
		}
		if stderrIsTerminal {
			fmt.Fprint(os.Stderr, renderProgressBar(label, index+1, total))
		}
	}
	if total > 0 && stderrIsTerminal {
		fmt.Fprintln(os.Stderr) // end the progress bar line
	}
	return nil
}
