# QR CLI

No secrets here, she does exactly what she says.

## Requirements

- PHP 7.1 or later, with GD extension installed
- Composer

## Installation

```bash
# Clone
git clone https://github.com/friartuck6000/qr-cli.git && cd qr-cli

# Install dependencies
composer install
```

That's it, you're all set to go.

## Usage

```bash
$ bin/console create
```

Here's the help output:

```
Usage:
  create [options] [--] <url>

Arguments:
  url                   The URL to encode.

Options:
  -f, --format=FORMAT   Output formats (svg, png or jpg). [default: ["svg"]] (multiple values allowed)
  -o, --out=OUT         Output file path. Extension will be managed automatically based on output formats.
      --scale=SCALE     Output scale for raster formats; the size of a single block in pixels. [default: 10]
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  Generate a new QR code from the given URL.
```
