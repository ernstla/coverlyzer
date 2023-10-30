<?php

declare(strict_types=1);

namespace Ernst\Coverlyzer;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\Directory;
use SebastianBergmann\CodeCoverage\Node\File;

class Analyzer
{
    public function __construct(private readonly CodeCoverage $coverage)
    {
    }

    public function getMissingCoverage(mixed $file): array
    {
        $shouldBeNewLine = true;

        $eachLine = function (array $array, array $tests, int $line) use (&$shouldBeNewLine): array {
            if ($tests !== []) {
                $shouldBeNewLine = true;

                return $array;
            }

            if ($shouldBeNewLine) {
                $array[] = (string)$line;
                $shouldBeNewLine = false;

                return $array;
            }

            $lastKey = count($array) - 1;

            if (array_key_exists($lastKey, $array) && str_contains((string)$array[$lastKey], '..')) {
                [$from] = explode('..', (string)$array[$lastKey]);
                $array[$lastKey] = $line > $from ? sprintf('%s..%s', $from, $line) : sprintf('%s..%s', $line, $from);

                return $array;
            }

            $array[$lastKey] = sprintf('%s..%s', $array[$lastKey], $line);

            return $array;
        };

        $array = [];

        foreach (array_filter($file->lineCoverageData(), 'is_array') as $line => $tests) {
            $array = $eachLine($array, $tests, $line);
        }

        return $array;
    }

    protected function getColor(float $percentage): string
    {
        //                             green                            red          yellow
        return $percentage == 100 ? "\033[32m" : ($percentage == 0 ? "\033[31m" : "\033[33m");
    }

    public function run(): void
    {
        print("Coverlyzer - PHPUnit coverage report:\n");

        /** @var Directory<File|Directory> $report */
        $report = $this->coverage->getReport();

        foreach ($report->getIterator() as $file) {
            if (! $file instanceof File) {
                continue;
            }
            $dirname = dirname($file->id());
            $basename = basename($file->id(), '.php');

            $name = $dirname === '.' ? $basename : implode(DIRECTORY_SEPARATOR, [
                $dirname,
                $basename,
            ]);

            $percentage = $file->numberOfExecutableLines() === 0
                ? '100.0'
                : number_format($file->percentageOfExecutedLines()->asFloat(), 1, '.', '');

            $uncoveredLines = '';

            $percentage = $file->percentageOfExecutedLines()->asFloat();

            if ($percentage > 0 && $percentage < 100) {
                $uncoveredLines = trim(implode(', ', $this->getMissingCoverage($file)));
            }

            $color = $this->getColor($percentage);
            printf(" {$color}% 6.02f%%\033[0m {$name}.php", $percentage);

            if  ($uncoveredLines) {
                print(" \033[90m{$uncoveredLines}\033[0m\n");
            } else {
                print("\n");
            }
        }

        $totalCoverage = $report->percentageOfExecutedLines()->asFloat();
        $color = $this->getColor($totalCoverage);

        printf("Summary: {$color}%.02f%%\033[0m covered lines\n", $totalCoverage);
    }
}
