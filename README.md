# ArgTyper

Add missing PHP parameter types based on the values you already pass in.

Your code often carries more type information than the signatures show. Every
time you call a method with a literal value, that value has a type. ArgTyper
reads those calls and writes the type back onto the parameter.

It is a single Go binary built on
[php-parser-in-go](https://github.com/rectorphp/php-parser-in-go) - no PHP,
Composer or PHPStan needed to run it.

<br>

## Example

Say you have a class with an untyped parameter, and a caller that passes an
`int` into it:

```php
// src/HotelOverview.php
final class HotelOverview
{
    public function makeRoomAvailable($roomNumber)
    {
    }

    public function openLobby()
    {
        $this->makeRoomAvailable(324);
    }
}
```

Run the tool:

```bash
argtyper add-types .
```

```
Code dirs found in ".": [src]

1. Collecting argument types...
   Found 1 arg types

2. Adding types to parameters...
   Finished! Added 1 new types
```

`324` is an `int`, so an `int` type is added to `$roomNumber`:

```diff
 final class HotelOverview
 {
-    public function makeRoomAvailable($roomNumber)
+    public function makeRoomAvailable(int $roomNumber)
     {
     }
 }
```

That's it.

<br>

## More of what it does

**Functions and constructors**, not just methods:

```diff
-function greet($name)
+function greet(string $name)
 {
 }

 greet("Tomas");
```

**Nullable** when a value and `null` are both passed:

```php
$this->setLocale("en");
$this->setLocale(null);
```

```diff
-public function setLocale($locale)
+public function setLocale(?string $locale)
 {
 }
```

**Union types** when two or more different types reach the same parameter -
instead of guessing or giving up, it writes them all:

```php
$this->handle(1);      // int
$this->handle("text"); // string
```

```diff
-public function handle($value)
+public function handle(int|string $value)
 {
 }
```

There is no cap on the number of union members, and they are sorted
alphabetically. A nullable union is written with a trailing `|null` (since `?`
only works for a single type). The one type it will not accept is `callable`
(a closure or arrow function) - that poisons the parameter and it is left
untyped.

**Default values** are typed too, even without a call site:

```diff
-public function paginate($page = 1)
+public function paginate(int $page = 1)
 {
 }
```

**More argument sources** than plain literals:

* `Foo::class` and enum cases (`Status::Active`) - resolved to `string` / the enum.
* project constants, class constants and `define()` values.
* a handful of built-in functions with a known return type, e.g. `strlen()`
  (`int`), `sprintf()` (`string`), `round()` (`float`).
* `new X()`, method chains on new objects (`(new X())->modify()` -> `X`), and
  `$expr ?? null` (contributes both sides, so it becomes nullable).

**Docblock cleanup** - once a real type is added, a now-redundant `@param` line
is removed, including `@param mixed`. Matching ignores union member order and
FQN vs short name, so `@param Foo|Bar` and `@param \App\Bar|\App\Foo` both drop.

**String literal docblocks** - when a parameter only ever receives a few plain
string literals (2 to 10 distinct ones), it gets `string` plus a `@param` with
the exact values:

```php
$this->compareScore(7, 'eq');
$this->compareScore(8, 'neq');
```

```diff
+/**
+ * @param 'eq'|'neq' $operator
+ */
-public function compareScore(int $score, $operator)
+public function compareScore(int $score, string $operator)
 {
 }
```

Any non-literal string (a constant, `sprintf()`, interpolation) or a value it
cannot resolve (a variable) skips the docblock, as does an existing `@param`
for that parameter.

**Colored, informative output** - a live progress bar per phase, colored `--dry`
diffs, and a summary of the added types grouped by category (scalar, object,
array, union). Colors respect `NO_COLOR` and disable on non-TTY output.

<br>

## Install

```bash
go install github.com/rectorphp/argtyper@latest
```

Or build from source:

```bash
git clone https://github.com/rectorphp/argtyper
cd argtyper
make build   # produces ./argtyper
```

## Usage

```bash
argtyper add-types [project-path]
```

`project-path` defaults to the current directory. The tool scans the `src`,
`lib`, `app`, `test` and `tests` directories.

Preview the changes without writing them with `--dry`:

```bash
argtyper add-types . --dry
```

It prints the diff of the types that would be added and leaves every file
untouched.

Only add the string literal docblocks, and no other types, with `--literals`:

```bash
argtyper add-types . --literals
```

<br>

## How it works

1. **Collect** - it walks every call site and records the type of each argument
   it can resolve: `int`, `float`, `string`, `bool`, `array`, `null`, `new X()`
   objects, `Foo::class`, enum cases, constants, and a few built-in function
   returns.
2. **Resolve** - it groups the recorded types per parameter position. A single
   type is used directly; two or more distinct types become a union; `null`
   becomes the nullable flag rather than a union member.
3. **Apply** - it adds each resolved type to the definitions that are still
   missing one, removes any now-redundant `@param` docblock, and reprints the
   file. Unchanged code keeps its exact formatting.

Rules it follows:

* Parameters that already have a type, and variadics, are left untouched.
* A `callable` argument poisons the parameter - it is left untyped.
* Magic methods are skipped, except `__construct`.
* Methods that might override a parent or interface are skipped, unless they
  are private or a constructor.

<br>

## Scope

ArgTyper reads the syntax tree only - it does not run full type inference. That
keeps it fast and dependency-free, and limits it to what can be resolved
statically:

* **Resolvable arguments** - literals (`f(324)`, `f("x")`, `f([1, 2])`), plus
  the extra sources above (`Foo::class`, enum cases, constants, `new X()`,
  known built-in returns). Free variables and arbitrary expressions are still
  skipped, because their type is unknown without inference.
* **Statically resolvable targets** - `new X()`, `X::method()`, `self::method()`,
  `static::method()`, `$this->method()` and plain `function()` calls. A call on
  another variable, such as `$service->method(...)`, is resolved only when the
  class behind `$service` is known from syntax - a typed parameter, a typed
  property, or a local assigned `new X()`. `parent::` is not resolved.
* **Short class names** - classes are matched by their short name, not the fully
  qualified name.

It fills in the easy, safe cases and leaves the rest for you to complete based
on PHPStan or test feedback.

<br>

Happy coding!
