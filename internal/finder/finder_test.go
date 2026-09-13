package finder_test

import (
	"os"
	"path/filepath"
	"testing"

	"github.com/rectorphp/argtyper/internal/finder"
)

func TestPHPFiles(t *testing.T) {
	root := t.TempDir()
	write(t, root, "src/A.php")
	write(t, root, "src/nested/B.php")
	write(t, root, "tests/C.php")
	write(t, root, "src/notes.txt")
	write(t, root, "vendor/D.php") // vendor is not a code directory

	files, err := finder.PHPFiles(root)
	if err != nil {
		t.Fatal(err)
	}

	want := []string{
		filepath.Join(root, "src/A.php"),
		filepath.Join(root, "src/nested/B.php"),
		filepath.Join(root, "tests/C.php"),
	}
	if len(files) != len(want) {
		t.Fatalf("got %v, want %v", files, want)
	}
	for i := range want {
		if files[i] != want[i] {
			t.Errorf("index %d: got %q want %q", i, files[i], want[i])
		}
	}
}

func write(t *testing.T, root, rel string) {
	t.Helper()
	path := filepath.Join(root, rel)
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte("<?php\n"), 0o644); err != nil {
		t.Fatal(err)
	}
}
