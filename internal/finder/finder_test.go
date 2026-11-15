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
	write(t, root, "plugins/AcmeBundle/E.php")
	write(t, root, "src/notes.txt")
	write(t, root, "vendor/D.php") // vendor is not a code directory

	files, err := finder.PHPFiles(root)
	if err != nil {
		t.Fatal(err)
	}

	want := []string{
		filepath.Join(root, "plugins/AcmeBundle/E.php"),
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

func TestPHPFilesSkipsVendorAndNodeModules(t *testing.T) {
	root := t.TempDir()
	write(t, root, "src/A.php")
	write(t, root, "src/vendor/B.php")
	write(t, root, "src/node_modules/C.php")

	files, err := finder.PHPFiles(root)
	if err != nil {
		t.Fatal(err)
	}

	want := []string{filepath.Join(root, "src/A.php")}
	if len(files) != len(want) || files[0] != want[0] {
		t.Fatalf("got %v, want %v", files, want)
	}
}

func TestIsTest(t *testing.T) {
	tests := map[string]bool{
		"tests/FooTest.php":                      true,
		"app/bundles/Core/Tests/Unit/A.php":      true,
		"test/A.php":                             true,
		"src/Testing/A.php":                      false,
		"app/bundles/Core/Helper/TestHelper.php": false,
	}
	for path, want := range tests {
		if got := finder.IsTest(path); got != want {
			t.Errorf("IsTest(%q) = %v, want %v", path, got, want)
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
