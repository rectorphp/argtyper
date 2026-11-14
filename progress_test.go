package main

import (
	"os"
	"path/filepath"
	"strconv"
	"sync/atomic"
	"testing"
	"time"
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

func TestProgressMapCapsSourceInFlight(t *testing.T) {
	directory := t.TempDir()
	var files []string
	for index := range 20 {
		file := filepath.Join(directory, strconv.Itoa(index)+".php")
		// every other file alone exceeds the cap, so it must run on its own
		size := maxSourceInFlight/4 + index%2*maxSourceInFlight
		if err := os.WriteFile(file, make([]byte, size), 0o644); err != nil {
			t.Fatal(err)
		}
		files = append(files, file)
	}

	var inFlight, peak atomic.Int64
	_, err := progressMap("testing", files, func(_ string, src []byte) (int, error) {
		current := inFlight.Add(int64(len(src)))
		for {
			seen := peak.Load()
			if current <= seen || peak.CompareAndSwap(seen, current) {
				break
			}
		}
		time.Sleep(time.Millisecond)
		inFlight.Add(-int64(len(src)))
		return len(src), nil
	})
	if err != nil {
		t.Fatal(err)
	}

	if limit := int64(maxSourceInFlight + maxSourceInFlight/4); peak.Load() > limit {
		t.Fatalf("peak source in flight = %d, want at most %d", peak.Load(), limit)
	}
}
