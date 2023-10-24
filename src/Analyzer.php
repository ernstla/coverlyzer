<?php

declare(strict_types=1);

namespace Ernst\Coverlyzer;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class Analyzer
{
    public function __construct(private readonly CodeCoverage $coverage)
    {
    }

    public function run(): void
    {
        $totalCoverage = $this->coverage->getReport()->percentageOfExecutedLines();
        echo($totalCoverage->asFloat() === 0.0
            ? '0.0'
            : number_format($totalCoverage->asFloat(), 1, '.', '')) . PHP_EOL;
    }
}
