# Arg Typer - Fill param types, based on passed args

There are more known types in your project then it meets the eye. This tool finds real argument types in method calls and functions call in your PHP project. Then runs Rector to complete known param type declarations where missing. 

That's it.

<br>

## Install

```bash
composer require rector/argtyper --dev
```

## Usage

Run on your project directory:

```bash
vendor/bin/argtyper project
```

To see more process details, add `--debug` option.

<br>

## How it works?

First, couple PHPStan custom rules will go through the code and record all arguments types passed to method calls, static call, new and function calls.

It will store these data in temporary `*.json file` in this format:

```json
[
    {
        "class": "App\\SomePackage\\SomeService",
        "method": "addName",
        "position": 0,
        "type": "PHPStan\\Type\\StringType"
    }
]
```

Then, Rector custom rules will go through codebase and fill known parameter types based on collected data. E.g. if some method gets `100`, `326` and `5`, it will fill `int`.

With few exceptions:

* if multiple types are found, it will skip it
* if union/intersection types are found, it will skip it as ambiguous
 
```diff
 namespace App\SomePackage;

 class SomeService
 {
-    public function addName($name)
+    public function addName(string $name)
     {
     }
 }
```

That's it!

It's not perfect, but in our testing runs, it fills 95 % of data correctly and saves huge amount of work. 
The rest you can fix manually based on PHPStan/tests feedback.

<br>

Happy coding!
