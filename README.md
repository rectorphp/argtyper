# Arg Typer: Fill Parameter Types based on Passed Values

There are often more known types in your project than meets the eye.  
This tool detects the **real** types passed into method and function calls using PHPStan.

```php
$this->hotelOverview->makeRoomAvailable(324);
```

Later in the code...

```php
public function roomDetail(int $roomNumber)
{
    $this->hotelOverview->makeRoomAvailable($roomNumber);
}
```

Later in tests...

```php
public function test(int $roomNumber): void
{
    $this->hotelOverview->makeRoomAvailable($roomNumber);
}
```

✅ Three times an `int` value is passed into `makeRoomAvailable()`.

<br>

Then [Rector](https://getrector.com) runs and fills in the missing type declarations:

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

That’s it.

<br>

## Install

```bash
composer require rector/argtyper --dev
```

<br>

## Usage

Run it in your project directory:

```bash
vendor/bin/argtyper .
```

<br>

Or on another project:

```bash
vendor/bin/argtyper project
```

To see more details during the process, add the `--debug` option.

<br>

## How It Works

At first, a set of custom PHPStan rules scans your code and records the argument types passed to method calls, static calls, `new` expressions, and function calls. It stores this data in temporary `*.json` files in the following format:

```json
[
    {
        "class": "HotelOverview",
        "method": "makeRoomAvailable",
        "position": 0,
        "type": "PHPStan\Type\IntegerType"
    }
]
```

<br>

Then, custom Rector rules go through the codebase and fill in the known parameter types based on the collected data — but only where they’re missing.

With a few exceptions:

* If multiple types are found → it’s skipped.
* If union or intersection types are found → it’s skipped as ambiguous.
* If a `float` parameter type is declared but only `int` arguments are passed (e.g. `30.0`) → it’s skipped to avoid losing decimal precision.

<br>

## Verify the Results

It’s not 100 % perfect, but in our tests it fills in about **95 %** of the data correctly and saves a huge amount of manual work.  
You can fix the remaining cases manually based on PHPStan or test feedback.

<br>

Happy coding!
