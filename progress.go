package main

import (
	"fmt"
	"os"
	"runtime"
	"strings"
	"sync"
	"sync/atomic"
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

// progressMap reads each file and hands its source to fn on a pool of
// runtime.NumCPU() workers, drawing a live progress bar labeled label on
// stderr while it goes. Results come back in file order, so the output stays
// deterministic; on failure the error of the first failing file is returned.
// The bar is skipped on a non-terminal run so piped output stays clean.
func progressMap[T any](label string, files []string, fn func(file string, src []byte) (T, error)) ([]T, error) {
	total := len(files)
	results := make([]T, total)
	errs := make([]error, total)

	var next atomic.Int64
	var mutex sync.Mutex
	done := 0

	var wait sync.WaitGroup
	for range min(runtime.NumCPU(), total) {
		wait.Go(func() {
			for {
				index := int(next.Add(1)) - 1
				if index >= total {
					return
				}
				src, err := os.ReadFile(files[index])
				if err == nil {
					results[index], err = fn(files[index], src)
				}
				errs[index] = err

				if stderrIsTerminal {
					mutex.Lock()
					done++
					fmt.Fprint(os.Stderr, renderProgressBar(label, done, total))
					mutex.Unlock()
				}
			}
		})
	}
	wait.Wait()

	if total > 0 && stderrIsTerminal {
		fmt.Fprintln(os.Stderr) // end the progress bar line
	}
	for _, err := range errs {
		if err != nil {
			return nil, err
		}
	}
	return results, nil
}
