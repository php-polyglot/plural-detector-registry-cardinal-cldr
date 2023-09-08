<?php

declare(strict_types=1);

namespace TestUnits\Polyglot\CldrCardinalPluralDetectorRegistry;

use PHPUnit\Framework\TestCase;
use Polyglot\CldrCardinalPluralDetectorRegistry\CldrCardinalPluralDetectorRegistry;
use Polyglot\Contract\PluralDetector\PluralCategory;
use Polyglot\Contract\PluralDetectorRegistry\Exception\LocaleNotSupported;

final class CldrCardinalPluralDetectorRegistryTest extends TestCase
{
    /**
     * @param string $expectedCategory
     * @param $number
     * @param string $locale
     * @return void
     * @throws LocaleNotSupported
     * @dataProvider provideDetect
     */
    public function testDetect(string $expectedCategory, $number, string $locale): void
    {
        $registry = new CldrCardinalPluralDetectorRegistry();
        $detector = $registry->get($locale);
        $this->assertSame($expectedCategory, $detector->detect($number));
    }

    /**
     * @return iterable<array{0:string, 1:mixed, 2:string}>
     */
    public function provideDetect(): iterable
    {
        foreach ($this->getAfLikeLocales() as $locale) {
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, '1', $locale];
            yield [PluralCategory::ONE, '1.0', $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, rand(2, PHP_INT_MAX), $locale];
        }

        foreach ($this->getAkLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, '1', $locale];
            yield [PluralCategory::ONE, '1.0', $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, rand(2, PHP_INT_MAX), $locale];
        }

        foreach ($this->getAmLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, '1', $locale];
            yield [PluralCategory::ONE, '1.0', $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, rand(0, 100) / 100, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, rand(2, PHP_INT_MAX), $locale];
        }

        foreach ($this->getArLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.0000', $locale];
            foreach ([0, 100, 1000] as $big) {
                for ($number = 3; $number <= 10; $number++) {
                    yield [PluralCategory::FEW, $big + $number, $locale];
                }
            }
            foreach ([0, 100, 1000] as $big) {
                for ($number = 11; $number <= 99; $number++) {
                    yield [PluralCategory::MANY, $big + $number, $locale];
                }
            }
            foreach ([100, 1000] as $big) {
                for ($number = 0; $number <= 2; $number++) {
                    yield [PluralCategory::OTHER, $big + $number, $locale];
                }
            }
        }

        foreach ($this->getAstLikeLocales() as $locale) {
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, '1', $locale];
            yield [PluralCategory::OTHER, '1.0', $locale];
            yield [PluralCategory::OTHER, 1.0, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, rand(2, PHP_INT_MAX), $locale];
        }

        foreach ($this->getBeLikeLocales() as $locale) {
            foreach ([0, 100, 1000] as $big) {
                yield [PluralCategory::ONE, $big + 1, $locale];
                for ($n = 21; $n <= 91; $n += 10) {
                    $number = $big + $n;
                    yield [PluralCategory::ONE, $number, $locale];
                    yield [PluralCategory::ONE, (float)$number, $locale];
                    yield [PluralCategory::ONE, sprintf('%d.00', $number), $locale];
                }
            }
            foreach ([0, 100, 1000] as $big) {
                for ($n = 20; $n <= 90; $n += 10) {
                    for ($m = 2; $m <= 4; $m++) {
                        $number = $big + $n + $m;
                        yield [PluralCategory::FEW, $number, $locale];
                        yield [PluralCategory::FEW, (float)$number, $locale];
                        yield [PluralCategory::FEW, sprintf('%d.00', $number), $locale];
                    }
                }
            }
            foreach ([0, 100, 1000] as $big) {
                yield [PluralCategory::MANY, $big, $locale];
                yield [PluralCategory::MANY, (float)$big, $locale];
                yield [PluralCategory::MANY, sprintf('%d.00', $big), $locale];
                for ($n = 5; $n <= 19; $n++) {
                    $number = $big + $n;
                    yield [PluralCategory::MANY, $number, $locale];
                    yield [PluralCategory::MANY, (float)$number, $locale];
                    yield [PluralCategory::MANY, sprintf('%d.00', $number), $locale];
                }
            }

            for ($n = 1; $n <= 9; $n++) {
                $number = $n / 10;
                yield [PluralCategory::OTHER, (float)$number, $locale];
                yield [PluralCategory::OTHER, (string)$number, $locale];
            }
        }

        foreach ($this->getBmLikeLocales() as $locale) {
            yield [PluralCategory::OTHER, rand(PHP_INT_MIN, PHP_INT_MAX), $locale];
        }

        foreach ($this->getBrLikeLocales() as $locale) {
            $map = [
                PluralCategory::ONE => [1],
                PluralCategory::TWO => [2],
                PluralCategory::FEW => [3, 4, 9],
            ];

            foreach ($map as $category => $ms) {
                foreach ([0, 100, 1000] as $big) {
                    for ($n = 0; $n <= 80; $n += 10) {
                        if ($n === 10 || $n === 70) {
                            continue;
                        }
                        foreach ($ms as $m) {
                            $number = $big + $n + $m;
                            yield [$category, $number, $locale];
                            yield [$category, (float)$number, $locale];
                            yield [$category, sprintf('%d.00', $number), $locale];
                        }
                    }
                }
            }
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, 1000000.0, $locale];
            yield [PluralCategory::MANY, '1000000.00', $locale];
            yield [PluralCategory::MANY, '1000000.000', $locale];
            yield [PluralCategory::MANY, '1000000.00000', $locale];

            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 0.5, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 1.5, $locale];
        }

        foreach ($this->getBsLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 100.1, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 22, $locale];
            yield [PluralCategory::FEW, 23, $locale];
            yield [PluralCategory::FEW, 24, $locale];
            yield [PluralCategory::FEW, 102, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 10004, $locale];
            yield [PluralCategory::FEW, .2, $locale];
            yield [PluralCategory::FEW, 1.3, $locale];
            yield [PluralCategory::FEW, 2.4, $locale];
            yield [PluralCategory::FEW, 100.2, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 2.5, $locale];
        }

        foreach ($this->getCaLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, '1c6', $locale];
            yield [PluralCategory::MANY, '2c6', $locale];
            yield [PluralCategory::MANY, '3c6', $locale];
            yield [PluralCategory::MANY, '4c6', $locale];
            yield [PluralCategory::MANY, '5c6', $locale];
            yield [PluralCategory::MANY, '6c6', $locale];
            yield [PluralCategory::MANY, '1.0000001c6', $locale];
            yield [PluralCategory::MANY, '1.1c6', $locale];
            yield [PluralCategory::MANY, '2.0000001c6', $locale];
            yield [PluralCategory::MANY, '2.1c6', $locale];
            yield [PluralCategory::MANY, '3.0000001c6', $locale];
            yield [PluralCategory::MANY, '3.1c6', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            for ($n = 2; $n <= 16; $n++) {
                yield [PluralCategory::OTHER, $n, $locale];
            }
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, '1c3', $locale];
            yield [PluralCategory::OTHER, '2c3', $locale];
            yield [PluralCategory::OTHER, '3c3', $locale];
            yield [PluralCategory::OTHER, '4c3', $locale];
            yield [PluralCategory::OTHER, '5c3', $locale];
            yield [PluralCategory::OTHER, '6c3', $locale];
        }

        foreach ($this->getCebLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 0.5, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 2, $locale];
            yield [PluralCategory::ONE, 3, $locale];
            yield [PluralCategory::ONE, 2.5, $locale];
            yield [PluralCategory::ONE, 5, $locale];
            yield [PluralCategory::ONE, 7, $locale];
            yield [PluralCategory::ONE, 8, $locale];
            yield [PluralCategory::ONE, 10, $locale];
            yield [PluralCategory::ONE, 11.5, $locale];
            yield [PluralCategory::ONE, 13, $locale];
            yield [PluralCategory::ONE, 15, $locale];
            yield [PluralCategory::ONE, 17, $locale];
            yield [PluralCategory::ONE, 18, $locale];
            yield [PluralCategory::ONE, 20, $locale];
            yield [PluralCategory::ONE, 20, $locale];
            yield [PluralCategory::ONE, 100, $locale];
            yield [PluralCategory::ONE, 1000, $locale];
            yield [PluralCategory::ONE, 10000, $locale];
            yield [PluralCategory::ONE, 100000, $locale];
            yield [PluralCategory::ONE, '10.0', $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, '16', $locale];
            yield [PluralCategory::OTHER, '26', $locale];
            yield [PluralCategory::OTHER, '104', $locale];
            yield [PluralCategory::OTHER, '2.4', $locale];
            yield [PluralCategory::OTHER, '1000.4', $locale];
        }

        foreach ($this->getCsLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::MANY, 0.5, $locale];
            yield [PluralCategory::MANY, 1.5, $locale];
            yield [PluralCategory::MANY, 10.0, $locale];
            yield [PluralCategory::MANY, 100.0, $locale];
            yield [PluralCategory::MANY, 1000.0, $locale];
            yield [PluralCategory::MANY, 10000.0, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
        }

        foreach ($this->getCyLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.00', $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, '3.00', $locale];
            yield [PluralCategory::MANY, 6, $locale];
            yield [PluralCategory::MANY, 6.0, $locale];
            yield [PluralCategory::MANY, '6.00', $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 21, $locale];
            yield [PluralCategory::OTHER, 36, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
        }

        foreach ($this->getDaLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, .1, $locale];
            yield [PluralCategory::ONE, 1.9, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
        }

        foreach ($this->getDsbLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 201, $locale];
            yield [PluralCategory::ONE, 301, $locale];
            yield [PluralCategory::ONE, 401, $locale];
            yield [PluralCategory::ONE, 501, $locale];
            yield [PluralCategory::ONE, 601, $locale];
            yield [PluralCategory::ONE, 701, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, .1, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 2.1, $locale];
            yield [PluralCategory::ONE, 3.1, $locale];
            yield [PluralCategory::ONE, 4.1, $locale];
            yield [PluralCategory::ONE, 5.1, $locale];
            yield [PluralCategory::ONE, 6.1, $locale];
            yield [PluralCategory::ONE, 7.1, $locale];
            yield [PluralCategory::ONE, 10.1, $locale];
            yield [PluralCategory::ONE, 100.1, $locale];
            yield [PluralCategory::ONE, 1000.1, $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 102, $locale];
            yield [PluralCategory::TWO, 202, $locale];
            yield [PluralCategory::TWO, 302, $locale];
            yield [PluralCategory::TWO, 402, $locale];
            yield [PluralCategory::TWO, 502, $locale];
            yield [PluralCategory::TWO, 602, $locale];
            yield [PluralCategory::TWO, 702, $locale];
            yield [PluralCategory::TWO, 1002, $locale];
            yield [PluralCategory::TWO, .2, $locale];
            yield [PluralCategory::TWO, 1.2, $locale];
            yield [PluralCategory::TWO, 2.2, $locale];
            yield [PluralCategory::TWO, 3.2, $locale];
            yield [PluralCategory::TWO, 4.2, $locale];
            yield [PluralCategory::TWO, 5.2, $locale];
            yield [PluralCategory::TWO, 6.2, $locale];
            yield [PluralCategory::TWO, 7.2, $locale];
            yield [PluralCategory::TWO, 10.2, $locale];
            yield [PluralCategory::TWO, 100.2, $locale];
            yield [PluralCategory::TWO, 1000.2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 103, $locale];
            yield [PluralCategory::FEW, 203, $locale];
            yield [PluralCategory::FEW, 303, $locale];
            yield [PluralCategory::FEW, 403, $locale];
            yield [PluralCategory::FEW, 503, $locale];
            yield [PluralCategory::FEW, 603, $locale];
            yield [PluralCategory::FEW, 703, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, .3, $locale];
            yield [PluralCategory::FEW, 1.3, $locale];
            yield [PluralCategory::FEW, 2.3, $locale];
            yield [PluralCategory::FEW, 3.3, $locale];
            yield [PluralCategory::FEW, 4.3, $locale];
            yield [PluralCategory::FEW, 5.3, $locale];
            yield [PluralCategory::FEW, 6.3, $locale];
            yield [PluralCategory::FEW, 7.3, $locale];
            yield [PluralCategory::FEW, 10.3, $locale];
            yield [PluralCategory::FEW, 100.3, $locale];
            yield [PluralCategory::FEW, 1000.3, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 0.5, $locale];
            yield [PluralCategory::OTHER, 0.6, $locale];
            yield [PluralCategory::OTHER, 0.7, $locale];
            yield [PluralCategory::OTHER, 0.8, $locale];
            yield [PluralCategory::OTHER, 0.9, $locale];
            yield [PluralCategory::OTHER, 1.0, $locale];
            yield [PluralCategory::OTHER, 1.5, $locale];
            yield [PluralCategory::OTHER, 1.6, $locale];
            yield [PluralCategory::OTHER, 1.7, $locale];
            yield [PluralCategory::OTHER, 1.8, $locale];
            yield [PluralCategory::OTHER, 1.9, $locale];
            yield [PluralCategory::OTHER, 2.0, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
        }

        foreach ($this->getEsLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, '1c6', $locale];
            yield [PluralCategory::MANY, '2c6', $locale];
            yield [PluralCategory::MANY, '3c6', $locale];
            yield [PluralCategory::MANY, '4c6', $locale];
            yield [PluralCategory::MANY, '5c6', $locale];
            yield [PluralCategory::MANY, '6c6', $locale];
            yield [PluralCategory::MANY, '1.0000001c6', $locale];
            yield [PluralCategory::MANY, '1.1c6', $locale];
            yield [PluralCategory::MANY, '2.0000001c6', $locale];
            yield [PluralCategory::MANY, '2.1c6', $locale];
            yield [PluralCategory::MANY, '3.0000001c6', $locale];
            yield [PluralCategory::MANY, '3.1c6', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 2.5, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, '1c3', $locale];
            yield [PluralCategory::OTHER, '2c3', $locale];
            yield [PluralCategory::OTHER, '3c3', $locale];
        }

        foreach ($this->getFfLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, 0.5, $locale];
            yield [PluralCategory::ONE, 0.9999, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 2.0, $locale];
            yield [PluralCategory::OTHER, 2.6, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 3.5, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 1000000, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
            yield [PluralCategory::OTHER, 10000.0, $locale];
            yield [PluralCategory::OTHER, 100000.0, $locale];
            yield [PluralCategory::OTHER, 1000000.0, $locale];
        }

        foreach ($this->getFilLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 2, $locale];
            yield [PluralCategory::ONE, 3, $locale];
            yield [PluralCategory::ONE, 5, $locale];
            yield [PluralCategory::ONE, 7, $locale];
            yield [PluralCategory::ONE, 8, $locale];
            yield [PluralCategory::ONE, 10, $locale];
            yield [PluralCategory::ONE, 11, $locale];
            yield [PluralCategory::ONE, 12, $locale];
            yield [PluralCategory::ONE, 13, $locale];
            yield [PluralCategory::ONE, 15, $locale];
            yield [PluralCategory::ONE, 17, $locale];
            yield [PluralCategory::ONE, 18, $locale];
            yield [PluralCategory::ONE, 20, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 100, $locale];
            yield [PluralCategory::ONE, 1000, $locale];
            yield [PluralCategory::ONE, 10000, $locale];
            yield [PluralCategory::ONE, 100000, $locale];
            yield [PluralCategory::ONE, 1000000, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 0.2, $locale];
            yield [PluralCategory::ONE, 0.3, $locale];
            yield [PluralCategory::ONE, 0.5, $locale];
            yield [PluralCategory::ONE, 0.7, $locale];
            yield [PluralCategory::ONE, 0.8, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 1.2, $locale];
            yield [PluralCategory::ONE, 1.3, $locale];
            yield [PluralCategory::ONE, 1.5, $locale];
            yield [PluralCategory::ONE, 1.7, $locale];
            yield [PluralCategory::ONE, 1.8, $locale];
            yield [PluralCategory::ONE, 100.0, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 24, $locale];
            yield [PluralCategory::OTHER, 26, $locale];
            yield [PluralCategory::OTHER, 29, $locale];
            yield [PluralCategory::OTHER, 104, $locale];
            yield [PluralCategory::OTHER, 1006, $locale];
            yield [PluralCategory::OTHER, 10009, $locale];
            yield [PluralCategory::OTHER, .4, $locale];
            yield [PluralCategory::OTHER, .6, $locale];
            yield [PluralCategory::OTHER, .9, $locale];
            yield [PluralCategory::OTHER, 1.4, $locale];
            yield [PluralCategory::OTHER, 1.6, $locale];
            yield [PluralCategory::OTHER, 1.9, $locale];
            yield [PluralCategory::OTHER, 2.4, $locale];
            yield [PluralCategory::OTHER, 2.6, $locale];
            yield [PluralCategory::OTHER, 2.9, $locale];
            yield [PluralCategory::OTHER, 10.4, $locale];
            yield [PluralCategory::OTHER, 100.6, $locale];
            yield [PluralCategory::OTHER, 1000.9, $locale];
        }

        foreach ($this->getFrLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, 0.5, $locale];
            yield [PluralCategory::ONE, 0.9999, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, '1c6', $locale];
            yield [PluralCategory::MANY, '2c6', $locale];
            yield [PluralCategory::MANY, '3c6', $locale];
            yield [PluralCategory::MANY, '4c6', $locale];
            yield [PluralCategory::MANY, '5c6', $locale];
            yield [PluralCategory::MANY, '6c6', $locale];
            yield [PluralCategory::MANY, '1.0000001c6', $locale];
            yield [PluralCategory::MANY, '1.1c6', $locale];
            yield [PluralCategory::MANY, '2.0000001c6', $locale];
            yield [PluralCategory::MANY, '2.1c6', $locale];
            yield [PluralCategory::MANY, '3.0000001c6', $locale];
            yield [PluralCategory::MANY, '3.1c6', $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, '1c3', $locale];
            yield [PluralCategory::OTHER, '2c3', $locale];
            yield [PluralCategory::OTHER, '3c3', $locale];
            yield [PluralCategory::OTHER, '4c3', $locale];
            yield [PluralCategory::OTHER, '5c3', $locale];
            yield [PluralCategory::OTHER, '6c3', $locale];
            yield [PluralCategory::OTHER, 2.0, $locale];
            yield [PluralCategory::OTHER, 2.1, $locale];
            yield [PluralCategory::OTHER, 2.2, $locale];
            yield [PluralCategory::OTHER, 2.3, $locale];
            yield [PluralCategory::OTHER, 2.4, $locale];
            yield [PluralCategory::OTHER, 2.5, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
            yield [PluralCategory::OTHER, 10000.0, $locale];
            yield [PluralCategory::OTHER, 100000.0, $locale];
            yield [PluralCategory::OTHER, 1000000.0, $locale];
            yield [PluralCategory::OTHER, '1.0001c3', $locale];
            yield [PluralCategory::OTHER, '1.1c3', $locale];
            yield [PluralCategory::OTHER, '2.0001c3', $locale];
            yield [PluralCategory::OTHER, '2.1c3', $locale];
            yield [PluralCategory::OTHER, '3.0001c3', $locale];
            yield [PluralCategory::OTHER, '3.1c3', $locale];
        }

        foreach ($this->getGaLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.00', $locale];
            yield [PluralCategory::TWO, '2.000', $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, '3.00', $locale];
            yield [PluralCategory::FEW, '3.000', $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 4.0, $locale];
            yield [PluralCategory::FEW, '4.00', $locale];
            yield [PluralCategory::FEW, '4.000', $locale];
            yield [PluralCategory::FEW, 5, $locale];
            yield [PluralCategory::FEW, 5.0, $locale];
            yield [PluralCategory::FEW, '5.00', $locale];
            yield [PluralCategory::FEW, '5.000', $locale];
            yield [PluralCategory::FEW, 6, $locale];
            yield [PluralCategory::FEW, 6.0, $locale];
            yield [PluralCategory::FEW, '6.00', $locale];
            yield [PluralCategory::FEW, '6.000', $locale];
            yield [PluralCategory::MANY, 7, $locale];
            yield [PluralCategory::MANY, 7.0, $locale];
            yield [PluralCategory::MANY, '7.00', $locale];
            yield [PluralCategory::MANY, '7.000', $locale];
            yield [PluralCategory::MANY, 8, $locale];
            yield [PluralCategory::MANY, 8.0, $locale];
            yield [PluralCategory::MANY, '8.00', $locale];
            yield [PluralCategory::MANY, '8.000', $locale];
            yield [PluralCategory::MANY, 9, $locale];
            yield [PluralCategory::MANY, 9.0, $locale];
            yield [PluralCategory::MANY, '9.00', $locale];
            yield [PluralCategory::MANY, '9.000', $locale];
            yield [PluralCategory::MANY, 10, $locale];
            yield [PluralCategory::MANY, 10.0, $locale];
            yield [PluralCategory::MANY, '10.00', $locale];
            yield [PluralCategory::MANY, '10.000', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
        }

        foreach ($this->getGdLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, 11, $locale];
            yield [PluralCategory::ONE, 11.0, $locale];
            yield [PluralCategory::ONE, '11.00', $locale];
            yield [PluralCategory::ONE, '11.000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.00', $locale];
            yield [PluralCategory::TWO, '2.000', $locale];
            yield [PluralCategory::TWO, 12, $locale];
            yield [PluralCategory::TWO, 12.0, $locale];
            yield [PluralCategory::TWO, '12.00', $locale];
            yield [PluralCategory::TWO, '12.000', $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, '3.00', $locale];
            yield [PluralCategory::FEW, '3.000', $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 4.0, $locale];
            yield [PluralCategory::FEW, '4.00', $locale];
            yield [PluralCategory::FEW, '4.000', $locale];
            yield [PluralCategory::FEW, 5, $locale];
            yield [PluralCategory::FEW, 5.0, $locale];
            yield [PluralCategory::FEW, '5.00', $locale];
            yield [PluralCategory::FEW, '5.000', $locale];
            yield [PluralCategory::FEW, 6, $locale];
            yield [PluralCategory::FEW, 6.0, $locale];
            yield [PluralCategory::FEW, '6.00', $locale];
            yield [PluralCategory::FEW, '6.000', $locale];
            yield [PluralCategory::FEW, 7, $locale];
            yield [PluralCategory::FEW, 7.0, $locale];
            yield [PluralCategory::FEW, '7.00', $locale];
            yield [PluralCategory::FEW, '7.000', $locale];
            yield [PluralCategory::FEW, 8, $locale];
            yield [PluralCategory::FEW, 8.0, $locale];
            yield [PluralCategory::FEW, '8.00', $locale];
            yield [PluralCategory::FEW, '8.000', $locale];
            yield [PluralCategory::FEW, 9, $locale];
            yield [PluralCategory::FEW, 9.0, $locale];
            yield [PluralCategory::FEW, '9.00', $locale];
            yield [PluralCategory::FEW, '9.000', $locale];
            yield [PluralCategory::FEW, 10, $locale];
            yield [PluralCategory::FEW, 10.0, $locale];
            yield [PluralCategory::FEW, '10.00', $locale];
            yield [PluralCategory::FEW, '10.000', $locale];
            yield [PluralCategory::FEW, 13, $locale];
            yield [PluralCategory::FEW, 13.0, $locale];
            yield [PluralCategory::FEW, '13.00', $locale];
            yield [PluralCategory::FEW, '13.000', $locale];
            yield [PluralCategory::FEW, 14, $locale];
            yield [PluralCategory::FEW, 14.0, $locale];
            yield [PluralCategory::FEW, '14.00', $locale];
            yield [PluralCategory::FEW, '14.000', $locale];
            yield [PluralCategory::FEW, 15, $locale];
            yield [PluralCategory::FEW, 15.0, $locale];
            yield [PluralCategory::FEW, '15.00', $locale];
            yield [PluralCategory::FEW, '15.000', $locale];
            yield [PluralCategory::FEW, 16, $locale];
            yield [PluralCategory::FEW, 16.0, $locale];
            yield [PluralCategory::FEW, '16.00', $locale];
            yield [PluralCategory::FEW, '16.000', $locale];
            yield [PluralCategory::FEW, 17, $locale];
            yield [PluralCategory::FEW, 17.0, $locale];
            yield [PluralCategory::FEW, '17.00', $locale];
            yield [PluralCategory::FEW, '17.000', $locale];
            yield [PluralCategory::FEW, 18, $locale];
            yield [PluralCategory::FEW, 18.0, $locale];
            yield [PluralCategory::FEW, '18.00', $locale];
            yield [PluralCategory::FEW, '18.000', $locale];
            yield [PluralCategory::FEW, 19, $locale];
            yield [PluralCategory::FEW, 19.0, $locale];
            yield [PluralCategory::FEW, '19.00', $locale];
            yield [PluralCategory::FEW, '19.000', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 21, $locale];
            yield [PluralCategory::OTHER, 22, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
        }

        foreach ($this->getGvLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 11, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 12, $locale];
            yield [PluralCategory::TWO, 22, $locale];
            yield [PluralCategory::TWO, 32, $locale];
            yield [PluralCategory::TWO, 42, $locale];
            yield [PluralCategory::TWO, 52, $locale];
            yield [PluralCategory::TWO, 62, $locale];
            yield [PluralCategory::TWO, 72, $locale];
            yield [PluralCategory::TWO, 102, $locale];
            yield [PluralCategory::TWO, 1002, $locale];
            yield [PluralCategory::FEW, 0, $locale];
            yield [PluralCategory::FEW, 20, $locale];
            yield [PluralCategory::FEW, 40, $locale];
            yield [PluralCategory::FEW, 60, $locale];
            yield [PluralCategory::FEW, 80, $locale];
            yield [PluralCategory::FEW, 100, $locale];
            yield [PluralCategory::FEW, 120, $locale];
            yield [PluralCategory::FEW, 140, $locale];
            yield [PluralCategory::FEW, 1000, $locale];
            yield [PluralCategory::FEW, 10000, $locale];
            yield [PluralCategory::FEW, 100000, $locale];
            yield [PluralCategory::FEW, 1000000, $locale];
            yield [PluralCategory::MANY, 0.0, $locale];
            yield [PluralCategory::MANY, 0.9, $locale];
            yield [PluralCategory::MANY, 1.3, $locale];
            yield [PluralCategory::MANY, 1.5, $locale];
            yield [PluralCategory::MANY, 10.0, $locale];
            yield [PluralCategory::MANY, 100.0, $locale];
            yield [PluralCategory::MANY, 1000.0, $locale];
            yield [PluralCategory::MANY, 10000.0, $locale];
            yield [PluralCategory::MANY, 100000.0, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 103, $locale];
            yield [PluralCategory::OTHER, 1003, $locale];
        }

        foreach ($this->getIsLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 2.1, $locale];
            yield [PluralCategory::ONE, 3.1, $locale];
            yield [PluralCategory::ONE, 4.1, $locale];
            yield [PluralCategory::ONE, 5.1, $locale];
            yield [PluralCategory::ONE, 6.1, $locale];
            yield [PluralCategory::ONE, 7.1, $locale];
            yield [PluralCategory::ONE, 10.1, $locale];
            yield [PluralCategory::ONE, 100.1, $locale];
            yield [PluralCategory::ONE, 1000.1, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 0.2, $locale];
            yield [PluralCategory::OTHER, 0.3, $locale];
            yield [PluralCategory::OTHER, 0.4, $locale];
            yield [PluralCategory::OTHER, 0.9, $locale];
            yield [PluralCategory::OTHER, 1.2, $locale];
            yield [PluralCategory::OTHER, 1.3, $locale];
            yield [PluralCategory::OTHER, 1.4, $locale];
            yield [PluralCategory::OTHER, 1.8, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
        }

        foreach ($this->getItLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, '1c6', $locale];
            yield [PluralCategory::MANY, '2c6', $locale];
            yield [PluralCategory::MANY, '3c6', $locale];
            yield [PluralCategory::MANY, '1.0000001c6', $locale];
            yield [PluralCategory::MANY, '1.1c6', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, .5, $locale];
            yield [PluralCategory::OTHER, 1.9, $locale];
            yield [PluralCategory::OTHER, 1.0009, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, '1c3', $locale];
            yield [PluralCategory::OTHER, '2c3', $locale];
        }

        foreach ($this->getIuLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.00', $locale];
            yield [PluralCategory::TWO, '2.000', $locale];
            yield [PluralCategory::TWO, '2.0000', $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 0.1, $locale];
            yield [PluralCategory::OTHER, 0.2, $locale];
            yield [PluralCategory::OTHER, 0.5, $locale];
            yield [PluralCategory::OTHER, 0.9, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, 1.9, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
            yield [PluralCategory::OTHER, 10000.0, $locale];
        }

        foreach ($this->getKshLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ZERO, '0.000', $locale];
            yield [PluralCategory::ZERO, '0.0000', $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 0.1, $locale];
            yield [PluralCategory::OTHER, 0.2, $locale];
            yield [PluralCategory::OTHER, 0.5, $locale];
            yield [PluralCategory::OTHER, 0.9, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, 1.9, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
            yield [PluralCategory::OTHER, 10000.0, $locale];
        }

        foreach ($this->getKwLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ZERO, '0.000', $locale];
            yield [PluralCategory::ZERO, '0.0000', $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 22, $locale];
            yield [PluralCategory::TWO, 42, $locale];
            yield [PluralCategory::TWO, 62, $locale];
            yield [PluralCategory::TWO, 82, $locale];
            yield [PluralCategory::TWO, 102, $locale];
            yield [PluralCategory::TWO, 122, $locale];
            yield [PluralCategory::TWO, 142, $locale];
            yield [PluralCategory::TWO, 1000, $locale];
            yield [PluralCategory::TWO, 10000, $locale];
            yield [PluralCategory::TWO, 100000, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, 22.0, $locale];
            yield [PluralCategory::TWO, 42.0, $locale];
            yield [PluralCategory::TWO, 62.0, $locale];
            yield [PluralCategory::TWO, 82.0, $locale];
            yield [PluralCategory::TWO, 102.0, $locale];
            yield [PluralCategory::TWO, 122.0, $locale];
            yield [PluralCategory::TWO, 142.0, $locale];
            yield [PluralCategory::TWO, 1000.0, $locale];
            yield [PluralCategory::TWO, 10000.0, $locale];
            yield [PluralCategory::TWO, 100000.0, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 23, $locale];
            yield [PluralCategory::FEW, 43, $locale];
            yield [PluralCategory::FEW, 63, $locale];
            yield [PluralCategory::FEW, 83, $locale];
            yield [PluralCategory::FEW, 103, $locale];
            yield [PluralCategory::FEW, 123, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, 23.0, $locale];
            yield [PluralCategory::FEW, 43.0, $locale];
            yield [PluralCategory::FEW, 63.0, $locale];
            yield [PluralCategory::FEW, 83.0, $locale];
            yield [PluralCategory::FEW, 103.0, $locale];
            yield [PluralCategory::FEW, 123.0, $locale];
            yield [PluralCategory::FEW, 1003.0, $locale];
            yield [PluralCategory::MANY, 21, $locale];
            yield [PluralCategory::MANY, 41, $locale];
            yield [PluralCategory::MANY, 61, $locale];
            yield [PluralCategory::MANY, 81, $locale];
            yield [PluralCategory::MANY, 101, $locale];
            yield [PluralCategory::MANY, 121, $locale];
            yield [PluralCategory::MANY, 161, $locale];
            yield [PluralCategory::MANY, 1001, $locale];
            yield [PluralCategory::MANY, 21.0, $locale];
            yield [PluralCategory::MANY, 41.0, $locale];
            yield [PluralCategory::MANY, 61.0, $locale];
            yield [PluralCategory::MANY, 81.0, $locale];
            yield [PluralCategory::MANY, 101.0, $locale];
            yield [PluralCategory::MANY, 121.0, $locale];
            yield [PluralCategory::MANY, 161.0, $locale];
            yield [PluralCategory::MANY, 1001.0, $locale];
            yield [PluralCategory::OTHER, 31, $locale];
            yield [PluralCategory::OTHER, 32, $locale];
            yield [PluralCategory::OTHER, 33, $locale];
            yield [PluralCategory::OTHER, 51, $locale];
            yield [PluralCategory::OTHER, 52, $locale];
            yield [PluralCategory::OTHER, 53, $locale];
            yield [PluralCategory::OTHER, 71, $locale];
            yield [PluralCategory::OTHER, 72, $locale];
            yield [PluralCategory::OTHER, 73, $locale];
            yield [PluralCategory::OTHER, 91, $locale];
            yield [PluralCategory::OTHER, 92, $locale];
            yield [PluralCategory::OTHER, 93, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, .1, $locale];
        }

        foreach ($this->getLagLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ZERO, '0.000', $locale];
            yield [PluralCategory::ZERO, '0.0000', $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 0.9, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 1.9, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 2.0, $locale];
            yield [PluralCategory::OTHER, 3.5, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
        }

        foreach ($this->getLtLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 21.0, $locale];
            yield [PluralCategory::ONE, 31.0, $locale];
            yield [PluralCategory::ONE, 41.0, $locale];
            yield [PluralCategory::ONE, 51.0, $locale];
            yield [PluralCategory::ONE, 61.0, $locale];
            yield [PluralCategory::ONE, 71.0, $locale];
            yield [PluralCategory::ONE, 81.0, $locale];
            yield [PluralCategory::ONE, 101.0, $locale];
            yield [PluralCategory::ONE, 1001.0, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 5, $locale];
            yield [PluralCategory::FEW, 6, $locale];
            yield [PluralCategory::FEW, 7, $locale];
            yield [PluralCategory::FEW, 8, $locale];
            yield [PluralCategory::FEW, 9, $locale];
            yield [PluralCategory::FEW, 22, $locale];
            yield [PluralCategory::FEW, 23, $locale];
            yield [PluralCategory::FEW, 24, $locale];
            yield [PluralCategory::FEW, 25, $locale];
            yield [PluralCategory::FEW, 26, $locale];
            yield [PluralCategory::FEW, 27, $locale];
            yield [PluralCategory::FEW, 28, $locale];
            yield [PluralCategory::FEW, 29, $locale];
            yield [PluralCategory::FEW, 102, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 2.0, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, 4.0, $locale];
            yield [PluralCategory::FEW, 5.0, $locale];
            yield [PluralCategory::FEW, 6.0, $locale];
            yield [PluralCategory::FEW, 7.0, $locale];
            yield [PluralCategory::FEW, 8.0, $locale];
            yield [PluralCategory::FEW, 9.0, $locale];
            yield [PluralCategory::FEW, 22.0, $locale];
            yield [PluralCategory::FEW, 23.0, $locale];
            yield [PluralCategory::FEW, 24.0, $locale];
            yield [PluralCategory::FEW, 25.0, $locale];
            yield [PluralCategory::FEW, 26.0, $locale];
            yield [PluralCategory::FEW, 27.0, $locale];
            yield [PluralCategory::FEW, 28.0, $locale];
            yield [PluralCategory::FEW, 29.0, $locale];
            yield [PluralCategory::FEW, 102.0, $locale];
            yield [PluralCategory::FEW, 1003.0, $locale];
            yield [PluralCategory::MANY, 0.1, $locale];
            yield [PluralCategory::MANY, 0.2, $locale];
            yield [PluralCategory::MANY, 0.3, $locale];
            yield [PluralCategory::MANY, 0.4, $locale];
            yield [PluralCategory::MANY, 0.9, $locale];
            yield [PluralCategory::MANY, 1.1, $locale];
            yield [PluralCategory::MANY, 1.9, $locale];
            yield [PluralCategory::MANY, 10.1, $locale];
            yield [PluralCategory::MANY, 100.1, $locale];
            yield [PluralCategory::MANY, 1000.1, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 30, $locale];
            yield [PluralCategory::OTHER, 40, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 1000000, $locale];
            yield [PluralCategory::OTHER, 10000000, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 11.0, $locale];
            yield [PluralCategory::OTHER, 12.0, $locale];
            yield [PluralCategory::OTHER, 13.0, $locale];
            yield [PluralCategory::OTHER, 14.0, $locale];
            yield [PluralCategory::OTHER, 15.0, $locale];
            yield [PluralCategory::OTHER, 16.0, $locale];
            yield [PluralCategory::OTHER, 17.0, $locale];
            yield [PluralCategory::OTHER, 18.0, $locale];
            yield [PluralCategory::OTHER, 19.0, $locale];
            yield [PluralCategory::OTHER, 20.0, $locale];
            yield [PluralCategory::OTHER, 30.0, $locale];
            yield [PluralCategory::OTHER, 40.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
            yield [PluralCategory::OTHER, 10000.0, $locale];
            yield [PluralCategory::OTHER, 100000.0, $locale];
            yield [PluralCategory::OTHER, 1000000.0, $locale];
            yield [PluralCategory::OTHER, 10000000.0, $locale];
        }

        foreach ($this->getLvLikeLocales() as $locale) {
            yield [PluralCategory::ZERO, 0, $locale];
            yield [PluralCategory::ZERO, 0.0, $locale];
            yield [PluralCategory::ZERO, '0.00', $locale];
            yield [PluralCategory::ZERO, '0.000', $locale];
            yield [PluralCategory::ZERO, '0.0000', $locale];
            yield [PluralCategory::ZERO, 10, $locale];
            yield [PluralCategory::ZERO, 11, $locale];
            yield [PluralCategory::ZERO, 12, $locale];
            yield [PluralCategory::ZERO, 13, $locale];
            yield [PluralCategory::ZERO, 14, $locale];
            yield [PluralCategory::ZERO, 15, $locale];
            yield [PluralCategory::ZERO, 16, $locale];
            yield [PluralCategory::ZERO, 17, $locale];
            yield [PluralCategory::ZERO, 18, $locale];
            yield [PluralCategory::ZERO, 19, $locale];
            yield [PluralCategory::ZERO, 20, $locale];
            yield [PluralCategory::ZERO, 30, $locale];
            yield [PluralCategory::ZERO, 40, $locale];
            yield [PluralCategory::ZERO, 50, $locale];
            yield [PluralCategory::ZERO, 60, $locale];
            yield [PluralCategory::ZERO, 70, $locale];
            yield [PluralCategory::ZERO, 80, $locale];
            yield [PluralCategory::ZERO, 90, $locale];
            yield [PluralCategory::ZERO, 100, $locale];
            yield [PluralCategory::ZERO, 1000, $locale];
            yield [PluralCategory::ZERO, 10000, $locale];
            yield [PluralCategory::ZERO, 100000, $locale];
            yield [PluralCategory::ZERO, 1000000, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 91, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, .1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 2.1, $locale];
            yield [PluralCategory::ONE, 3.1, $locale];
            yield [PluralCategory::ONE, 4.1, $locale];
            yield [PluralCategory::ONE, 10.1, $locale];
            yield [PluralCategory::ONE, 100.1, $locale];
            yield [PluralCategory::ONE, 1000.1, $locale];
            yield [PluralCategory::ONE, 10000.1, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 4, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 22, $locale];
            yield [PluralCategory::OTHER, 23, $locale];
            yield [PluralCategory::OTHER, 24, $locale];
            yield [PluralCategory::OTHER, 25, $locale];
            yield [PluralCategory::OTHER, 26, $locale];
            yield [PluralCategory::OTHER, 27, $locale];
            yield [PluralCategory::OTHER, 28, $locale];
            yield [PluralCategory::OTHER, 29, $locale];
            yield [PluralCategory::OTHER, 102, $locale];
            yield [PluralCategory::OTHER, 1002, $locale];
        }

        foreach ($this->getMkLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::ONE, .1, $locale];
            yield [PluralCategory::ONE, 1.1, $locale];
            yield [PluralCategory::ONE, 2.1, $locale];
            yield [PluralCategory::ONE, 3.1, $locale];
            yield [PluralCategory::ONE, 4.1, $locale];
            yield [PluralCategory::ONE, 5.1, $locale];
            yield [PluralCategory::ONE, 6.1, $locale];
            yield [PluralCategory::ONE, 7.1, $locale];
            yield [PluralCategory::ONE, 8.1, $locale];
            yield [PluralCategory::ONE, 10.1, $locale];
            yield [PluralCategory::ONE, 100.1, $locale];
            yield [PluralCategory::OTHER, 0, $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 3, $locale];
            yield [PluralCategory::OTHER, 54, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 0.2, $locale];
            yield [PluralCategory::OTHER, 1.0, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
            yield [PluralCategory::OTHER, 1000.0, $locale];
        }

        foreach ($this->getMoLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::FEW, 0, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 5, $locale];
            yield [PluralCategory::FEW, 6, $locale];
            yield [PluralCategory::FEW, 7, $locale];
            yield [PluralCategory::FEW, 8, $locale];
            yield [PluralCategory::FEW, 9, $locale];
            yield [PluralCategory::FEW, 10, $locale];
            yield [PluralCategory::FEW, 11, $locale];
            yield [PluralCategory::FEW, 12, $locale];
            yield [PluralCategory::FEW, 13, $locale];
            yield [PluralCategory::FEW, 14, $locale];
            yield [PluralCategory::FEW, 15, $locale];
            yield [PluralCategory::FEW, 16, $locale];
            yield [PluralCategory::FEW, 17, $locale];
            yield [PluralCategory::FEW, 18, $locale];
            yield [PluralCategory::FEW, 19, $locale];
            yield [PluralCategory::FEW, 0.0, $locale];
            yield [PluralCategory::FEW, 0.6, $locale];
            yield [PluralCategory::FEW, 1.5, $locale];
            yield [PluralCategory::FEW, 10.0, $locale];
            yield [PluralCategory::FEW, 100.0, $locale];
            yield [PluralCategory::FEW, 1000.0, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 30, $locale];
            yield [PluralCategory::OTHER, 40, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
        }

        foreach ($this->getMtLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::ONE, '1.0000', $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 2.0, $locale];
            yield [PluralCategory::TWO, '2.00', $locale];
            yield [PluralCategory::TWO, '2.000', $locale];
            yield [PluralCategory::TWO, '2.0000', $locale];
            yield [PluralCategory::FEW, 0, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 5, $locale];
            yield [PluralCategory::FEW, 6, $locale];
            yield [PluralCategory::FEW, 7, $locale];
            yield [PluralCategory::FEW, 8, $locale];
            yield [PluralCategory::FEW, 9, $locale];
            yield [PluralCategory::FEW, 10, $locale];
            yield [PluralCategory::FEW, 103, $locale];
            yield [PluralCategory::FEW, 104, $locale];
            yield [PluralCategory::FEW, 105, $locale];
            yield [PluralCategory::FEW, 106, $locale];
            yield [PluralCategory::FEW, 107, $locale];
            yield [PluralCategory::FEW, 108, $locale];
            yield [PluralCategory::FEW, 109, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 0.0, $locale];
            yield [PluralCategory::FEW, 3.0, $locale];
            yield [PluralCategory::FEW, 4.0, $locale];
            yield [PluralCategory::FEW, 5.0, $locale];
            yield [PluralCategory::FEW, 6.0, $locale];
            yield [PluralCategory::FEW, 10.0, $locale];
            yield [PluralCategory::FEW, 103.0, $locale];
            yield [PluralCategory::MANY, 11, $locale];
            yield [PluralCategory::MANY, 12, $locale];
            yield [PluralCategory::MANY, 13, $locale];
            yield [PluralCategory::MANY, 14, $locale];
            yield [PluralCategory::MANY, 15, $locale];
            yield [PluralCategory::MANY, 16, $locale];
            yield [PluralCategory::MANY, 17, $locale];
            yield [PluralCategory::MANY, 18, $locale];
            yield [PluralCategory::MANY, 19, $locale];
            yield [PluralCategory::MANY, 111, $locale];
            yield [PluralCategory::MANY, 112, $locale];
            yield [PluralCategory::MANY, 113, $locale];
            yield [PluralCategory::MANY, 114, $locale];
            yield [PluralCategory::MANY, 115, $locale];
            yield [PluralCategory::MANY, 116, $locale];
            yield [PluralCategory::MANY, 117, $locale];
            yield [PluralCategory::MANY, 1011, $locale];
            yield [PluralCategory::MANY, 11.0, $locale];
            yield [PluralCategory::MANY, 12.0, $locale];
            yield [PluralCategory::MANY, 13.0, $locale];
            yield [PluralCategory::MANY, 14.0, $locale];
            yield [PluralCategory::MANY, 15.0, $locale];
            yield [PluralCategory::MANY, 16.0, $locale];
            yield [PluralCategory::MANY, 111.0, $locale];
            yield [PluralCategory::MANY, 1011.0, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 21, $locale];
            yield [PluralCategory::OTHER, 22, $locale];
            yield [PluralCategory::OTHER, 23, $locale];
            yield [PluralCategory::OTHER, 24, $locale];
            yield [PluralCategory::OTHER, 25, $locale];
            yield [PluralCategory::OTHER, 26, $locale];
            yield [PluralCategory::OTHER, 27, $locale];
            yield [PluralCategory::OTHER, .1, $locale];
            yield [PluralCategory::OTHER, .9, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, 1.7, $locale];
            yield [PluralCategory::OTHER, 100.0, $locale];
        }

        foreach ($this->getPlLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 22, $locale];
            yield [PluralCategory::FEW, 33, $locale];
            yield [PluralCategory::FEW, 44, $locale];
            yield [PluralCategory::FEW, 102, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 10004, $locale];
            yield [PluralCategory::MANY, 0, $locale];
            yield [PluralCategory::MANY, 5, $locale];
            yield [PluralCategory::MANY, 6, $locale];
            yield [PluralCategory::MANY, 7, $locale];
            yield [PluralCategory::MANY, 8, $locale];
            yield [PluralCategory::MANY, 9, $locale];
            yield [PluralCategory::MANY, 10, $locale];
            yield [PluralCategory::MANY, 11, $locale];
            yield [PluralCategory::MANY, 12, $locale];
            yield [PluralCategory::MANY, 13, $locale];
            yield [PluralCategory::MANY, 14, $locale];
            yield [PluralCategory::MANY, 15, $locale];
            yield [PluralCategory::MANY, 16, $locale];
            yield [PluralCategory::MANY, 17, $locale];
            yield [PluralCategory::MANY, 18, $locale];
            yield [PluralCategory::MANY, 19, $locale];
            yield [PluralCategory::MANY, 20, $locale];
            yield [PluralCategory::MANY, 21, $locale];
            yield [PluralCategory::MANY, 30, $locale];
            yield [PluralCategory::MANY, 31, $locale];
            yield [PluralCategory::MANY, 41, $locale];
            yield [PluralCategory::MANY, 51, $locale];
            yield [PluralCategory::MANY, 61, $locale];
            yield [PluralCategory::MANY, 71, $locale];
            yield [PluralCategory::MANY, 81, $locale];
            yield [PluralCategory::MANY, 91, $locale];
            yield [PluralCategory::MANY, 101, $locale];
            yield [PluralCategory::MANY, 1001, $locale];
            yield [PluralCategory::MANY, 111, $locale];
            yield [PluralCategory::MANY, 1012, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 1.5, $locale];
        }

        foreach ($this->getPtLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, .5, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.9, $locale];
            yield [PluralCategory::ONE, 1.0009, $locale];
            yield [PluralCategory::MANY, 1000000, $locale];
            yield [PluralCategory::MANY, '1c6', $locale];
            yield [PluralCategory::MANY, '2c6', $locale];
            yield [PluralCategory::MANY, '3c6', $locale];
            yield [PluralCategory::MANY, '1.0000001c6', $locale];
            yield [PluralCategory::MANY, '1.1c6', $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 20, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, '1c3', $locale];
            yield [PluralCategory::OTHER, '2c3', $locale];
        }

        foreach ($this->getRuLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 21, $locale];
            yield [PluralCategory::ONE, 31, $locale];
            yield [PluralCategory::ONE, 41, $locale];
            yield [PluralCategory::ONE, 51, $locale];
            yield [PluralCategory::ONE, 61, $locale];
            yield [PluralCategory::ONE, 71, $locale];
            yield [PluralCategory::ONE, 81, $locale];
            yield [PluralCategory::ONE, 91, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::FEW, 2, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 22, $locale];
            yield [PluralCategory::FEW, 33, $locale];
            yield [PluralCategory::FEW, 44, $locale];
            yield [PluralCategory::FEW, 102, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 10004, $locale];
            yield [PluralCategory::MANY, 0, $locale];
            yield [PluralCategory::MANY, 5, $locale];
            yield [PluralCategory::MANY, 6, $locale];
            yield [PluralCategory::MANY, 7, $locale];
            yield [PluralCategory::MANY, 8, $locale];
            yield [PluralCategory::MANY, 9, $locale];
            yield [PluralCategory::MANY, 10, $locale];
            yield [PluralCategory::MANY, 11, $locale];
            yield [PluralCategory::MANY, 12, $locale];
            yield [PluralCategory::MANY, 13, $locale];
            yield [PluralCategory::MANY, 14, $locale];
            yield [PluralCategory::MANY, 15, $locale];
            yield [PluralCategory::MANY, 16, $locale];
            yield [PluralCategory::MANY, 17, $locale];
            yield [PluralCategory::MANY, 18, $locale];
            yield [PluralCategory::MANY, 19, $locale];
            yield [PluralCategory::MANY, 20, $locale];
            yield [PluralCategory::MANY, 30, $locale];
            yield [PluralCategory::MANY, 111, $locale];
            yield [PluralCategory::MANY, 1012, $locale];
            yield [PluralCategory::OTHER, 0.0, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
            yield [PluralCategory::OTHER, 1.5, $locale];
        }

        foreach ($this->getShiLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            for ($number = 2; $number <= 10; $number++) {
                yield [PluralCategory::FEW, $number, $locale];
                yield [PluralCategory::FEW, (float)$number, $locale];
                yield [PluralCategory::FEW, sprintf('%d.00', $number), $locale];
            }
            yield [PluralCategory::OTHER, 67, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
        }

        foreach ($this->getSiLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, 0.1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, 0.01, $locale];
            yield [PluralCategory::ONE, '0.000', $locale];
            yield [PluralCategory::ONE, '0.001', $locale];
            yield [PluralCategory::ONE, '1.000', $locale];
            yield [PluralCategory::OTHER, 2, $locale];
            yield [PluralCategory::OTHER, 67, $locale];
            yield [PluralCategory::OTHER, 1.1, $locale];
            yield [PluralCategory::OTHER, 10.0, $locale];
        }

        foreach ($this->getSlLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 101, $locale];
            yield [PluralCategory::ONE, 201, $locale];
            yield [PluralCategory::ONE, 301, $locale];
            yield [PluralCategory::ONE, 401, $locale];
            yield [PluralCategory::ONE, 501, $locale];
            yield [PluralCategory::ONE, 601, $locale];
            yield [PluralCategory::ONE, 701, $locale];
            yield [PluralCategory::ONE, 801, $locale];
            yield [PluralCategory::ONE, 901, $locale];
            yield [PluralCategory::ONE, 1001, $locale];
            yield [PluralCategory::TWO, 2, $locale];
            yield [PluralCategory::TWO, 102, $locale];
            yield [PluralCategory::TWO, 202, $locale];
            yield [PluralCategory::TWO, 302, $locale];
            yield [PluralCategory::TWO, 402, $locale];
            yield [PluralCategory::TWO, 502, $locale];
            yield [PluralCategory::TWO, 602, $locale];
            yield [PluralCategory::TWO, 702, $locale];
            yield [PluralCategory::TWO, 802, $locale];
            yield [PluralCategory::TWO, 902, $locale];
            yield [PluralCategory::TWO, 1002, $locale];
            yield [PluralCategory::FEW, 3, $locale];
            yield [PluralCategory::FEW, 103, $locale];
            yield [PluralCategory::FEW, 203, $locale];
            yield [PluralCategory::FEW, 303, $locale];
            yield [PluralCategory::FEW, 403, $locale];
            yield [PluralCategory::FEW, 503, $locale];
            yield [PluralCategory::FEW, 603, $locale];
            yield [PluralCategory::FEW, 703, $locale];
            yield [PluralCategory::FEW, 803, $locale];
            yield [PluralCategory::FEW, 903, $locale];
            yield [PluralCategory::FEW, 1003, $locale];
            yield [PluralCategory::FEW, 4, $locale];
            yield [PluralCategory::FEW, 104, $locale];
            yield [PluralCategory::FEW, 204, $locale];
            yield [PluralCategory::FEW, 304, $locale];
            yield [PluralCategory::FEW, 404, $locale];
            yield [PluralCategory::FEW, 504, $locale];
            yield [PluralCategory::FEW, 604, $locale];
            yield [PluralCategory::FEW, 704, $locale];
            yield [PluralCategory::FEW, 804, $locale];
            yield [PluralCategory::FEW, 904, $locale];
            yield [PluralCategory::FEW, 1004, $locale];
            yield [PluralCategory::FEW, 0.0, $locale];
            yield [PluralCategory::FEW, 1.0, $locale];
            yield [PluralCategory::FEW, 1.5, $locale];
            yield [PluralCategory::FEW, 10.0, $locale];
            yield [PluralCategory::FEW, 10.9, $locale];
            yield [PluralCategory::OTHER, 5, $locale];
            yield [PluralCategory::OTHER, 6, $locale];
            yield [PluralCategory::OTHER, 7, $locale];
            yield [PluralCategory::OTHER, 8, $locale];
            yield [PluralCategory::OTHER, 9, $locale];
            yield [PluralCategory::OTHER, 10, $locale];
            yield [PluralCategory::OTHER, 11, $locale];
            yield [PluralCategory::OTHER, 12, $locale];
            yield [PluralCategory::OTHER, 13, $locale];
            yield [PluralCategory::OTHER, 14, $locale];
            yield [PluralCategory::OTHER, 15, $locale];
            yield [PluralCategory::OTHER, 16, $locale];
            yield [PluralCategory::OTHER, 17, $locale];
            yield [PluralCategory::OTHER, 18, $locale];
            yield [PluralCategory::OTHER, 19, $locale];
            yield [PluralCategory::OTHER, 100, $locale];
            yield [PluralCategory::OTHER, 1000, $locale];
            yield [PluralCategory::OTHER, 10000, $locale];
            yield [PluralCategory::OTHER, 100000, $locale];
            yield [PluralCategory::OTHER, 1000000, $locale];
        }

        foreach ($this->getTzmLikeLocales() as $locale) {
            yield [PluralCategory::ONE, 0, $locale];
            yield [PluralCategory::ONE, 0.0, $locale];
            yield [PluralCategory::ONE, '0.00', $locale];
            yield [PluralCategory::ONE, 1, $locale];
            yield [PluralCategory::ONE, 1.0, $locale];
            yield [PluralCategory::ONE, '1.00', $locale];
            for ($number = 11; $number <= 99; $number++) {
                yield [PluralCategory::ONE, $number, $locale];
                yield [PluralCategory::ONE, (float)$number, $locale];
                yield [PluralCategory::ONE, sprintf('%d.00', $number), $locale];
            }
            for ($number = 2; $number <= 10; $number++) {
                yield [PluralCategory::OTHER, $number, $locale];
                yield [PluralCategory::OTHER, (float)$number, $locale];
                yield [PluralCategory::OTHER, sprintf('%d.00', $number), $locale];
            }
            for ($number = 100; $number <= 110; $number++) {
                yield [PluralCategory::OTHER, $number, $locale];
                yield [PluralCategory::OTHER, (float)$number, $locale];
                yield [PluralCategory::OTHER, sprintf('%d.00', $number), $locale];
            }
            yield [PluralCategory::OTHER, 1.5, $locale];
        }
    }

    /**
     * @return iterable<string>
     */
    private function getAfLikeLocales(): iterable
    {
        return [
            'af',
            'an',
            'asa',
            'az',
            'bal',
            'bem',
            'bez',
            'bg',
            'brx',
            'ce',
            'cgg',
            'chr',
            'ckb',
            'dv',
            'ee',
            'el',
            'eo',
            'eu',
            'fo',
            'fur',
            'gsw',
            'ha',
            'haw',
            'hu',
            'jgo',
            'jmc',
            'ka',
            'kaj',
            'kcg',
            'kk',
            'kkj',
            'kl',
            'ks',
            'ksb',
            'ku',
            'ky',
            'lb',
            'lg',
            'mas',
            'mgo',
            'ml',
            'mn',
            'mr',
            'nb',
            'ne',
            'nd',
            'nn',
            'nnh',
            'no',
            'nr',
            'ny',
            'nyn',
            'om',
            'or',
            'os',
            'pap',
            'ps',
            'rm',
            'rwk',
            'rof',
            'saq',
            'seh',
            'sd',
            'sdh',
            'sn',
            'so',
            'sq',
            'ss',
            'st',
            'ssy',
            'syr',
            'ta',
            'te',
            'teo',
            'tig',
            'tk',
            'tn',
            'tr',
            'ts',
            'ug',
            'uz',
            've',
            'vo',
            'vun',
            'wae',
            'xh',
            'xog',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getAkLikeLocales(): iterable
    {
        return [
            'ak',
            'bho',
            'ln',
            'mg',
            'nso',
            'pa',
            'ti',
            'wa',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getAmLikeLocales(): iterable
    {
        return [
            'am',
            'as',
            'bn',
            'doi',
            'fa',
            'gu',
            'hi',
            'kn',
            'pcm',
            'zu',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getArLikeLocales(): iterable
    {
        return [
            'ar',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getAstLikeLocales(): iterable
    {
        return [
            'ast',
            'de',
            'en',
            'et',
            'fi',
            'fy',
            'gl',
            'ia',
            'io',
            'ji',
            'lij',
            'nl',
            'sc',
            'scn',
            'sv',
            'sw',
            'ur',
            'yi',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getBeLikeLocales(): iterable
    {
        return [
            'be',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getBmLikeLocales(): iterable
    {
        return [
            'bm',
            'bo',
            'dz',
            'hnj',
            'ig',
            'ii',
            'id',
            'in',
            'ja',
            'jbo',
            'jv',
            'jw',
            'kea',
            'kde',
            'km',
            'ko',
            'lkt',
            'lo',
            'ms',
            'my',
            'nqo',
            'osa',
            'sah',
            'ses',
            'sg',
            'su',
            'th',
            'to',
            'tpi',
            'yue',
            'zh',
            'vi',
            'wo',
            'yo',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getBrLikeLocales(): iterable
    {
        return [
            'br',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getBsLikeLocales(): iterable
    {
        return [
            'bs',
            'hr',
            'sr',
            'sh',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getCaLikeLocales(): iterable
    {
        return [
            'ca',
            'vec',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getCebLikeLocales(): iterable
    {
        return [
            'ceb',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getCsLikeLocales(): iterable
    {
        return [
            'cs',
            'sk',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getCyLikeLocales(): iterable
    {
        return [
            'cy',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getDaLikeLocales(): iterable
    {
        return [
            'da',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getDsbLikeLocales(): iterable
    {
        return [
            'dsb',
            'hsb',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getEsLikeLocales(): iterable
    {
        return [
            'es',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getFfLikeLocales(): iterable
    {
        return [
            'ff',
            'hy',
            'kab',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getFilLikeLocales(): iterable
    {
        return [
            'fil',
            'tl',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getFrLikeLocales(): iterable
    {
        return [
            'fr',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getGaLikeLocales(): iterable
    {
        return [
            'ga',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getGdLikeLocales(): iterable
    {
        return [
            'gd',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getGvLikeLocales(): iterable
    {
        return [
            'gv',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getIsLikeLocales(): iterable
    {
        return [
            'is',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getItLikeLocales(): iterable
    {
        return [
            'it',
            'pt_PT',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getIuLikeLocales(): iterable
    {
        return [
            'iu',
            'iu',
            'naq',
            'sat',
            'se',
            'sma',
            'smj',
            'smn',
            'sms',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getKshLikeLocales(): iterable
    {
        return [
            'ksh',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getKwLikeLocales(): iterable
    {
        return [
            'kw',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getLagLikeLocales(): iterable
    {
        return [
            'lag',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getLtLikeLocales(): iterable
    {
        return [
            'lt',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getLvLikeLocales(): iterable
    {
        return [
            'lv',
            'prg',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getMkLikeLocales(): iterable
    {
        return [
            'mk',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getMoLikeLocales(): iterable
    {
        return [
            'mo',
            'ro',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getMtLikeLocales(): iterable
    {
        return [
            'mt',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getPlLikeLocales(): iterable
    {
        return [
            'pl',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getPtLikeLocales(): iterable
    {
        return [
            'pt',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getRuLikeLocales(): iterable
    {
        return [
            'ru',
            'uk',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getShiLikeLocales(): iterable
    {
        return [
            'shi',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getSiLikeLocales(): iterable
    {
        return [
            'si',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getSlLikeLocales(): iterable
    {
        return [
            'sl',
        ];
    }

    /**
     * @return iterable<string>
     */
    private function getTzmLikeLocales(): iterable
    {
        return [
            'tzm',
        ];
    }
}
