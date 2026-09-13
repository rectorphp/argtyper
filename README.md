# Fill Parameter Types based on Passed Values

There are often more known types in your project than meets the eye.
This tool detects the types of **literal values** passed into method, constructor and function calls, then adds them as parameter type declarations.

<br>

```php
final class HotelOverview
{
    public function makeRoomAvailable($roomNumber)
    {
    }

    public function bookLobby()
    {
        $this->makeRoomAvailable(324);
    }
}
```

✅ An `int` value is passed into `makeRoomAvailable()`.

<br>

The tool fills in the missing type declaration:

```diff
 final class HotelOverview
 {
-    public function makeRoomAvailable($roomNumber)
+    public function makeRoomAvailable(int $roomNumber)
     {
     }
 }
```

✅ An `int` parameter type is added to the `makeRoomAvailable()` method.

<br>

That's it.

<br>

## Install

```bash
go install github.com/rectorphp/argtyper@latest
```

<br>

## Usage

Run it in your project directory:

```bash
argtyper add-types .
```

Or on another project:

```bash
argtyper add-types /path/to/project
```

It scans the `src`, `lib`, `app`, `test` and `tests` directories.

<br>

## How It Works

It is built on [php-parser-in-go](https://github.com/rectorphp/php-parser-in-go).

1. It walks every call site and records the type of each **literal** argument - `int`, `float`, `string`, `bool`, `array`, `null` and `new X()` (as `object`).
2. It groups the recorded types per parameter position.
3. It adds the type to each definition that is still missing one.

With a few exceptions:

* If multiple different types are found for one parameter -> it is skipped as ambiguous.
* If a single type plus `null` is found -> a nullable type is added.
* Parameters that already have a type are left untouched.
* Magic methods (except `__construct`) are skipped.
* Methods that may override a parent or interface are skipped, unless they are private or a constructor.

<br>

## Scope

The tool relies only on the parsed syntax tree, not on full type inference, so it works with values it can resolve statically:

* **Literal arguments** - `f(324)`, `f("x")`, `f([1, 2])`. Variables and expressions are skipped.
* **Statically resolvable call targets** - `new X()`, `X::method()`, `self::method()`, `$this->method()` and plain `function()` calls. Calls on other variables (`$service->method()`) are skipped, because the class cannot be known without type inference.
* **Short class names** - classes are matched by their short name, not the fully qualified name.

This catches the easy, unambiguous cases and leaves the rest for you to fill manually based on PHPStan or test feedback.

<br>

Happy coding!
