// Command argtyper fills missing parameter types from the literal values passed
// into local method, constructor and function calls.
package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"

	"github.com/rectorphp/argtyper/internal/aggregate"
	"github.com/rectorphp/argtyper/internal/apply"
	"github.com/rectorphp/argtyper/internal/collect"
	"github.com/rectorphp/argtyper/internal/diff"
	"github.com/rectorphp/argtyper/internal/finder"
	"github.com/rectorphp/argtyper/internal/inherit"
	"github.com/rectorphp/argtyper/internal/phpast"
	"github.com/rectorphp/argtyper/internal/symbols"
)

const usage = `Usage: argtyper add-types [project-path] [--dry] [--literals] [--objects]

Find literal values passed into local method/function calls and add them as
parameter type declarations. Defaults to the current directory.

  --dry        Print the diff of the types that would be added, without writing.
  --literals   Only add string types with a @param 'a'|'b' literal docblock.
  --objects    Only add object types.

--literals and --objects can be combined.`

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
	options := apply.Options{}
	projectPath := ""
	for _, arg := range args[1:] {
		switch {
		case arg == "--dry":
			dry = true
		case arg == "--literals":
			options.Literals = true
		case arg == "--objects":
			options.Objects = true
		case strings.HasPrefix(arg, "-"):
			fmt.Println(usage)
			return fmt.Errorf("unknown option %q", arg)
		case projectPath != "":
			fmt.Println(usage)
			return fmt.Errorf("only one project path is allowed, got %q and %q", projectPath, arg)
		default:
			projectPath = arg
		}
	}
	if projectPath == "" {
		projectPath = "."
	}

	files, err := finder.PHPFiles(projectPath)
	if err != nil {
		return err
	}

	fmt.Printf("Code dirs found in %q: %v\n\n", projectPath, finder.CodeDirectories(projectPath))

	// gather project symbols (enums, constants) so argument values that
	// reference them resolve to a type just like literals do, and build the
	// inheritance table from the project and its vendor directory, so a method
	// that overrides an ancestor (including one in vendor) is left alone. Each
	// project file is parsed once for both; per-file tables merge in file order.
	type scanned struct {
		symbols     *symbols.Table
		inheritance *inherit.Table
	}
	scans, err := progressMap("scanning", files, func(_ string, src []byte) (scanned, error) {
		result := scanned{symbols.New(), inherit.New()}
		if root, err := phpast.Parse(src); err == nil && root != nil {
			result.symbols.Collect(root)
			result.inheritance.Collect(root)
		}
		return result, nil
	})
	if err != nil {
		return err
	}
	table := symbols.New()
	inheritance := inherit.New()
	for _, scan := range scans {
		table.Merge(scan.symbols)
		inheritance.Merge(scan.inheritance)
	}

	vendorFiles, err := finder.VendorPHPFiles(projectPath)
	if err != nil {
		return err
	}
	vendorInheritances, err := progressMap("parsing", vendorFiles, func(_ string, src []byte) (*inherit.Table, error) {
		vendorInheritance := inherit.New()
		vendorInheritance.CollectSource(src)
		return vendorInheritance, nil
	})
	if err != nil {
		return err
	}
	for _, vendorInheritance := range vendorInheritances {
		inheritance.Merge(vendorInheritance)
	}

	// 1. collect literal argument types across the whole project
	fmt.Println("1. Collecting argument types...")
	fileRecords, err := progressMap("collecting", files, func(_ string, src []byte) ([]collect.Record, error) {
		return collect.FromSource(src, table), nil
	})
	if err != nil {
		return err
	}
	var records []collect.Record
	for _, recordsOfFile := range fileRecords {
		records = append(records, recordsOfFile...)
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
	type applied struct {
		added []string
		patch string
	}
	applies, err := progressMap("applying", files, func(file string, src []byte) (applied, error) {
		output, added, changed := apply.Source(src, types, table, inheritance, options)
		if !changed {
			return applied{}, nil
		}

		if dry {
			result := applied{added: added}
			if patch, ok := diff.Lines(file, string(src), output); ok {
				result.patch = patch
			}
			return result, nil
		}

		return applied{added: added}, os.WriteFile(file, []byte(output), 0o644)
	})
	if err != nil {
		return err
	}
	var addedTypes []string
	for _, result := range applies {
		addedTypes = append(addedTypes, result.added...)
		if result.patch != "" {
			fmt.Print(colorizePatch(result.patch))
		}
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
