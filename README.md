# Arg Typer

There are more known types in your project then it meets the eye. This tool finds real argument types in method calls and functions call in your PHP project. Then dumps Rector config to autocomplete them. That's it.

## Todo

* [ ] add function param type support
* [ ] handle New_ nodes

## Install

```bash
composer require rector/argtyper --dev
```

## Usage

1. First, run PHPStan to generate `phpstan-collected-data.json`

```bash
vendor/bin/phpstan analyse src tests --configuration vendor/tomasvotruba/argtyper/phpstan-data-collector.neon
```

or from non-root directory, but this project:

```bash
vendor/bin/phpstan analyse ../project/src --configuration config/phpstan-data-collector.neon --autoload-file ../project/vendor/autoload.php
```

2. Run Rector with rules that fill known types based on collected data:

```bash
vendor/bin/rector src tests --config vendor/tomasvotruba/argtyper/rector-arg-typer.php
```

or from non-root directory, but this project:

```bash
vendor/bin/rector p ../project/src --config rector-arg-typer.php
```

<br>

Happy coding!
