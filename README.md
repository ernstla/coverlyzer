Coverlyzer
==========

PHP console tool to analyze PHPUnit test coverage reports in PHP format. 

## Installation

    composer require ernst/coverlyzer

## Usage

Generate a coverage report in PHP format:

    phpunit --coverage-php=path/to/file.php

Pass the report file as parameter to the coverlyzer script:

    ./vendor/bin/coverlyzer path/to/file.php

## License

Coverlyzer is released under the [MIT license](LICENSE.md). 
Parts of the code are from [Pest](https://github.com/pestphp/pest)
