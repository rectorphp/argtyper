// Command argtyper fills missing parameter types from the literal values passed
// into local method, constructor and function calls.
package main

import (
	"fmt"
	"os"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/diff"
	"github.com/rectorphp/argtyper/internal/finder"
)

const usage = `Usage: argtyper add-types [project-path] [--dry]

Find literal values passed into local method/function calls and add them as
parameter type declarations. Defaults to the current directory.

  --dry   Print the diff of the types that would be added, without writing.`

func main() {
	if err := run(os.Args[1:]); err != nil {
		fmt.Fprintln(os.Stderr, "Error: "+err.Error())
		os.Exit(1)
	}
}

func run(args []string) error {
	if len(args) == 0 || args[0] != "add-types" {
		fmt.Println(usage)
		if len(args) == 0 {
			return nil
		}
		return fmt.Errorf("unknown command %q", args[0])
	}

	dry := false
	projectPath := "."
	for _, arg := range args[1:] {
		if arg == "--dry" {
			dry = true
			continue
		}
		projectPath = arg
	}

	files, err := finder.PHPFiles(projectPath)
	if err != nil {
		return err
	}

	fmt.Printf("Code dirs found in %q: %v\n\n", projectPath, finder.CodeDirectories(projectPath))

	// 1. collect literal argument types across the whole project
	fmt.Println("1. Collecting argument types...")
	var records []collect.Record
	for _, file := range files {
		src, err := os.ReadFile(file)
		if err != nil {
			return err
		}
		records = append(records, collect.FromSource(src)...)
	}
	fmt.Printf("   Found %d arg types\n\n", len(records))

	types := aggregate.Resolve(records)

	// 2. add the resolved types to parameter declarations
	if dry {
		fmt.Println("2. Types that would be added (dry run)...")
	} else {
		fmt.Println("2. Adding types to parameters...")
	}
	added := 0
	for _, file := range files {
		src, err := os.ReadFile(file)
		if err != nil {
			return err
		}

		output, count, changed := apply.Source(src, types)
		if !changed {
			continue
		}

		if dry {
			if patch, ok := diff.Lines(file, string(src), output); ok {
				fmt.Print(patch)
			}
			added += count
			continue
		}

		if err := os.WriteFile(file, []byte(output), 0o644); err != nil {
			return err
		}
		added += count
	}

	if added == 0 {
		fmt.Println("   No new types added. Is your code that good?")
		return nil
	}

	if dry {
		fmt.Printf("\n   Dry run: %d types would be added\n", added)
		return nil
	}

	fmt.Printf("   Finished! Added %d new types\n", added)
	return nil
}
