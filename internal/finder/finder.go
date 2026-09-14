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
// plugins is included so a project's app and plugin call sites are collected in
// one pass, which is what lets a null argument passed from a plugin make an app
// method's parameter nullable.
var codeDirectories = []string{"app", "lib", "plugins", "src", "test", "tests"}

// skipDirectories hold third-party code that should never be typed.
var skipDirectories = map[string]bool{"vendor": true, "node_modules": true}

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

// VendorPHPFiles returns every .php file under the project's vendor directory,
// or nothing when there is no vendor directory. These are read only to learn the
// signatures of parent classes, never modified.
func VendorPHPFiles(projectPath string) ([]string, error) {
	root := filepath.Join(projectPath, "vendor")
	info, err := os.Stat(root)
	if err != nil || !info.IsDir() {
		return nil, nil
	}

	var files []string
	err = filepath.WalkDir(root, func(path string, entry fs.DirEntry, err error) error {
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
	return files, nil
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
			if entry.IsDir() && skipDirectories[entry.Name()] {
				return fs.SkipDir
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
