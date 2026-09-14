package diff_test

import (
	"testing"

	"github.com/rectorphp/argtyper/internal/diff"
)

func TestLinesReportsChangedLine(t *testing.T) {
	before := "<?php\nfunction greet($who) {}"
	after := "<?php\nfunction greet(string $who) {}"

	got, changed := diff.Lines("src/A.php", before, after)
	if !changed {
		t.Fatal("expected a change")
	}

	want := "src/A.php\n  2\n  - function greet($who) {}\n  + function greet(string $who) {}\n"
	if got != want {
		t.Errorf("\n got: %q\nwant: %q", got, want)
	}
}

func TestLinesReportsNoChange(t *testing.T) {
	src := "<?php\nfunction greet($who) {}"

	got, changed := diff.Lines("src/A.php", src, src)
	if changed || got != "" {
		t.Errorf("expected no change, got %q", got)
	}
}
