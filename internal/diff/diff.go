// Package diff renders the changed lines between two versions of a file.
package diff

import (
	"fmt"
	"slices"
	"strings"
)

type opKind int

const (
	opEqual opKind = iota
	opDelete
	opInsert
)

type op struct {
	kind opKind
	line string
}

// Lines returns a line diff between before and after, prefixed with the file
// path. Each block of changed lines starts with its line number in before,
// followed by removed "- " and added "+ " lines. The second return value
// reports whether anything differs.
func Lines(path, before, after string) (string, bool) {
	ops := editScript(strings.Split(before, "\n"), strings.Split(after, "\n"))

	var b strings.Builder
	changed := false
	beforeLine := 1
	for i := 0; i < len(ops); {
		if ops[i].kind == opEqual {
			beforeLine++
			i++
			continue
		}

		if !changed {
			fmt.Fprintf(&b, "%s\n", path)
			changed = true
		}
		fmt.Fprintf(&b, "  %d\n", beforeLine)

		var removed, added []string
		for ; i < len(ops) && ops[i].kind != opEqual; i++ {
			if ops[i].kind == opDelete {
				removed = append(removed, ops[i].line)
				beforeLine++
			} else {
				added = append(added, ops[i].line)
			}
		}
		for _, line := range removed {
			fmt.Fprintf(&b, "  - %s\n", line)
		}
		for _, line := range added {
			fmt.Fprintf(&b, "  + %s\n", line)
		}
	}

	return b.String(), changed
}

// editScript returns the shortest list of operations turning a into b, using
// the Myers diff algorithm.
func editScript(a, b []string) []op {
	n, m := len(a), len(b)
	limit := n + m
	v := make([]int, 2*limit+2)
	var trace [][]int

	for d := 0; d <= limit; d++ {
		trace = append(trace, slices.Clone(v))
		for k := -d; k <= d; k += 2 {
			var x int
			if k == -d || (k != d && v[limit+k-1] < v[limit+k+1]) {
				x = v[limit+k+1]
			} else {
				x = v[limit+k-1] + 1
			}
			y := x - k
			for x < n && y < m && a[x] == b[y] {
				x++
				y++
			}
			v[limit+k] = x
			if x >= n && y >= m {
				return backtrack(a, b, trace, limit)
			}
		}
	}

	return nil
}

// backtrack walks the recorded Myers frontiers from the end back to the start.
func backtrack(a, b []string, trace [][]int, limit int) []op {
	var ops []op
	x, y := len(a), len(b)
	for d, v := range slices.Backward(trace) {
		k := x - y
		var prevK int
		if k == -d || (k != d && v[limit+k-1] < v[limit+k+1]) {
			prevK = k + 1
		} else {
			prevK = k - 1
		}
		prevX := v[limit+prevK]
		prevY := prevX - prevK

		for x > prevX && y > prevY {
			x--
			y--
			ops = append(ops, op{opEqual, a[x]})
		}
		if d == 0 {
			break
		}
		if x == prevX {
			y--
			ops = append(ops, op{opInsert, b[y]})
		} else {
			x--
			ops = append(ops, op{opDelete, a[x]})
		}
	}

	slices.Reverse(ops)
	return ops
}
