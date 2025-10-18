# Arg Typer - Fill param types, based on passed args

There are more known types in your project then it meets the eye:

```php
$this->hotelOverview->makeRoomVailable(324);
```

Later in the code...

```php
public function roomDetail(int $roomNumber)
{
    $this->hotelOverview->makeRoomVailable($int);
}
```

Later in tests...

```php
public function test(int $roomNumber): void
{
    $this->hotelOverview->makeRoomVailable($roomNumber);
}
```

This tool detect real argument types in method and function calls using PHPStan. Then runs Rector to complete them where missing:

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

## Install

```bash
composer require rector/argtyper --dev
```

<br>

## Usage

Run on your project directory:

```bash
vendor/bin/argtyper project
```

To see more process details, add `--debug` option.

<br>

## How it works?

First, couple PHPStan custom rules will go through the code and record all arguments types passed to method calls, static call, new and function calls. It will store these data in temporary `*.json` file in this format:

```json
[
    {
        "class": "HotelOverview",
        "method": "makeRoomAvailable",
        "position": 0,
        "type": "PHPStan\\Type\\IntegerType"
    }
]
```

Then, Rector custom rules will go through codebase and fill known parameter types based on collected data where missing.

With few exceptions:

* if multiple types are found, it will skip it
* if union/intersection types are found, it will skip it as ambiguous
* if `float` param type is used and `int` arg is passed (even as `30.0`), to avoid loosing decimal part

<br>

## Verify Results
 
It's not 100 % perfect, but in our testing runs it fills 95 % of data correctly and saves huge amount of work. 
The rest you can fix manually based on PHPStan/tests feedback.

<br>

Happy coding!
