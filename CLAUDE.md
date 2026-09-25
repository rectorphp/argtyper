# ArgTyper

A single Go binary that adds missing PHP parameter types from the literal values
passed into local calls. No PHP, Composer or PHPStan needed at runtime. Built on
[php-parser-in-go](https://github.com/rectorphp/php-parser-in-go).

## What it does

Reads every call site, sees the type of each literal argument (`f(324)` -> `int`),
and writes that type back onto the untyped parameter. It works on the syntax tree
only - no full type inference - so it stays fast and covers the safe cases:
literals into statically resolvable targets (`new X()`, `X::method()`,
`self::method()`, `$this->method()`, plain functions). Everything else is skipped.

## How it works

The run in `main.go` is a three-stage pipeline over the project's PHP files:

1. **Collect** - record the type of each literal argument at every call site.
2. **Resolve** (aggregate) - group records per parameter position, keep only the
   unambiguous ones: a single type, or a single type plus `null` (nullable).
3. **Apply** - add each resolved type to declarations still missing one and
   reprint the file, leaving untouched code byte-for-byte.

Skip rules: parameters that already have a type; parameters with conflicting
types (ambiguous); magic methods except `__construct`; methods that may override
a parent or interface (including vendor), unless private or a constructor.

## Packages (`internal/`)

- `finder` - locate project and vendor PHP files, and the scanned code dirs
  (`src`, `lib`, `app`, `test`, `tests`).
- `phpast` - parse PHP source and traverse the AST.
- `symbols` - collect project enums and constants so argument values referencing
  them resolve to a type.
- `valuetype` - map a literal expression to its type.
- `collect` - walk call sites and produce the argument-type records.
- `inherit` - inheritance table used to skip overriding methods.
- `aggregate` - resolve records into one type per parameter, dropping ambiguous.
- `apply` - add resolved types to parameter declarations and reprint.
- `diff` - unified diff, used by `--dry`.

## Commands

```bash
argtyper add-types [project-path] [--dry] [--literals] [--objects]
```

`project-path` defaults to `.`. `--dry` prints the diff without writing.
`--literals` only adds string types with a `@param 'a'|'b'` literal docblock.
`--objects` only adds object types, including unions made only of objects.
Both can be combined.

Dev tasks via `Makefile`:

- `make build` - compile the `argtyper` binary
- `make test` - `go test -race ./...`
- `make check` - vet + build + test

## CI

`.github/workflows/build.yaml` runs four required jobs, mirroring
php-parser-in-go and pinned to Go `1.26.6`:

- `build` - vet, build, test -race, `go mod tidy` check
- `format` - `gofmt -s` and `go fix`
- `analysis` - gopls modernize and golangci-lint
- `security` - govulncheck
