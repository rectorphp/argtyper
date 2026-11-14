package main

import (
	"os"
	"path/filepath"
	"strconv"
	"testing"
)

func TestProgressMapKeepsFileOrder(t *testing.T) {
	directory := t.TempDir()
	var files []string
	for index := range 100 {
		file := filepath.Join(directory, strconv.Itoa(index)+".php")
		if err := os.WriteFile(file, []byte(strconv.Itoa(index)), 0o644); err != nil {
			t.Fatal(err)
		}
		files = append(files, file)
	}

	results, err := progressMap("testing", files, func(_ string, src []byte) (string, error) {
		return string(src), nil
	})
	if err != nil {
		t.Fatal(err)
	}

	for index, result := range results {
		if result != strconv.Itoa(index) {
			t.Fatalf("result %d = %q, want %q", index, result, strconv.Itoa(index))
		}
	}
}

func TestProgressMapReturnsReadError(t *testing.T) {
	_, err := progressMap("testing", []string{filepath.Join(t.TempDir(), "missing.php")}, func(_ string, src []byte) (string, error) {
		return string(src), nil
	})
	if err == nil {
		t.Fatal("expected an error for a missing file")
	}
}
