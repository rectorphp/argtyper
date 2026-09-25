// Command argtyper fills missing parameter types from the literal values passed
// into local method, constructor and function calls.
package main

import (
	"fmt"
	"os"
	"strconv"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/diff"
	"github.com/rectorphp/argtyper/internal/finder"
	"github.com/rectorphp/argtyper/internal/inherit"
	"github.com/rectorphp/argtyper/internal/symbols"
)

const usage = `Usage: argtyper add-types [project-path] [--dry] [--literals]

Find literal values passed into local method/function calls and add them as
parameter type declarations. Defaults to the current directory.

  --dry        Print the diff of the types that would be added, without writing.
  --literals   Only add string types with a @param 'a'|'b' literal docblock.`

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
	literalsOnly := false
	projectPath := "."
	for _, arg := range args[1:] {
		if arg == "--dry" {
			dry = true
			continue
		}
		if arg == "--literals" {
			literalsOnly = true
			continue
		}
		projectPath = arg
	}

	files, err := finder.PHPFiles(projectPath)
	if err != nil {
		return err
	}

	fmt.Printf("Code dirs found in %q: %v\n\n", projectPath, finder.CodeDirectories(projectPath))

	// gather project symbols (enums, constants) so argument values that
	// reference them resolve to a type just like literals do
	table := symbols.New()
	if err := progressEach("scanning", files, func(_ string, src []byte) error {
		table.CollectSource(src)
		return nil
	}); err != nil {
		return err
	}

	// build the inheritance table from the project and its vendor directory, so
	// a method that overrides an ancestor (including one in vendor) is left alone
	inheritance := inherit.New()
	vendorFiles, err := finder.VendorPHPFiles(projectPath)
	if err != nil {
		return err
	}
	allFiles := append(append([]string{}, files...), vendorFiles...)
	if err := progressEach("parsing", allFiles, func(_ string, src []byte) error {
		inheritance.CollectSource(src)
		return nil
	}); err != nil {
		return err
	}

	// 1. collect literal argument types across the whole project
	fmt.Println("1. Collecting argument types...")
	var records []collect.Record
	if err := progressEach("collecting", files, func(_ string, src []byte) error {
		records = append(records, collect.FromSource(src, table)...)
		return nil
	}); err != nil {
		return err
	}
	resolvedCount := 0
	for _, record := range records {
		if record.Type != "" {
			resolvedCount++
		}
	}
	fmt.Printf("   Found %d arg types\n\n", resolvedCount)

	types := aggregate.Resolve(records)

	// 2. add the resolved types to parameter declarations
	if dry {
		fmt.Println("2. Types that would be added (dry run)...")
	} else {
		fmt.Println("2. Adding types to parameters...")
	}
	var addedTypes []string
	if err := progressEach("applying", files, func(file string, src []byte) error {
		output, added, changed := apply.Source(src, types, table, inheritance, literalsOnly)
		if !changed {
			return nil
		}
		addedTypes = append(addedTypes, added...)

		if dry {
			if patch, ok := diff.Lines(file, string(src), output); ok {
				fmt.Print(colorizePatch(patch))
			}
			return nil
		}

		if err := os.WriteFile(file, []byte(output), 0o644); err != nil {
			return err
		}
		return nil
	}); err != nil {
		return err
	}

	if len(addedTypes) == 0 {
		fmt.Println("   No new types added. Is your code that good?")
		return nil
	}

	printOverview(addedTypes)

	if dry {
		fmt.Printf("\n   Dry run: %s types would be added\n", paint(ansiBold, strconv.Itoa(len(addedTypes))))
		return nil
	}

	fmt.Printf("\n   %s\n", paint(ansiBold+ansiGreen, fmt.Sprintf("Finished! Added %d new types", len(addedTypes))))
	return nil
}
