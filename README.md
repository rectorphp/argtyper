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

**Skips the ambiguous cases** - if two different types reach the same
parameter, it leaves the parameter alone rather than guessing:

```php
$this->handle(1);      // int
$this->handle("text"); // string  -> parameter is left untyped
```

<br>

## Install

```bash
go install github.com/rectorphp/argtyper@latest
```

## Usage

```bash
argtyper add-types [project-path]
```

`project-path` defaults to the current directory. The tool scans the `src`,
`lib`, `app`, `test` and `tests` directories.

<br>

## How it works

1. **Collect** - it walks every call site and records the type of each *literal*
   argument: `int`, `float`, `string`, `bool`, `array`, `null`, and `new X()`
   as an object.
2. **Resolve** - it groups the recorded types per parameter position and keeps
   only the unambiguous ones: a single type, or a single type plus `null`
   (which becomes nullable).
3. **Apply** - it adds each resolved type to the definitions that are still
   missing one, and reprints the file. Unchanged code keeps its exact
   formatting.

Rules it follows:

* Parameters that already have a type are left untouched.
* Multiple different types for one parameter are skipped as ambiguous.
* Magic methods are skipped, except `__construct`.
* Methods that might override a parent or interface are skipped, unless they
  are private or a constructor.

<br>

## Scope

ArgTyper reads the syntax tree only - it does not run full type inference. That
keeps it fast and dependency-free, and limits it to what can be resolved
statically:

* **Literal arguments** - `f(324)`, `f("x")`, `f([1, 2])`. Variables and
  expressions are skipped, because their type is unknown without inference.
* **Statically resolvable targets** - `new X()`, `X::method()`, `self::method()`,
  `$this->method()` and plain `function()` calls. A call on another variable,
  such as `$service->method(...)`, is skipped, because the class behind
  `$service` cannot be known from syntax alone.
* **Short class names** - classes are matched by their short name, not the fully
  qualified name.

It fills in the easy, safe cases and leaves the rest for you to complete based
on PHPStan or test feedback.

<br>

Happy coding!
