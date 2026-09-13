// Package finder locates PHP source files in the common code directories of a
// project.
package finder

import (
	"io/fs"
	"os"
	"path/filepath"
	"sort"
	"strings"
)

// codeDirectories are the top-level directory names scanned for PHP files.
var codeDirectories = []string{"src", "lib", "app", "test", "tests"}

// CodeDirectories returns the code directories that exist in the project,
// relative to it, sorted by name.
func CodeDirectories(projectPath string) []string {
	var found []string
	for _, name := range codeDirectories {
		info, err := os.Stat(filepath.Join(projectPath, name))
		if err == nil && info.IsDir() {
			found = append(found, name)
		}
	}
	sort.Strings(found)
	return found
}

// PHPFiles returns every .php file under the project's code directories.
func PHPFiles(projectPath string) ([]string, error) {
	var files []string

	for _, dir := range CodeDirectories(projectPath) {
		root := filepath.Join(projectPath, dir)
		err := filepath.WalkDir(root, func(path string, entry fs.DirEntry, err error) error {
			if err != nil {
				return err
			}
			if !entry.IsDir() && strings.HasSuffix(path, ".php") {
				files = append(files, path)
			}
			return nil
		})
		if err != nil {
			return nil, err
		}
	}

	sort.Strings(files)
	return files, nil
}
