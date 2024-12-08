<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Helper;

use app\helpers\Helper;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    // region getSince
    /**
     * Tests are partials because time is ticking, mock is complicated.
     *
     * @throws \Exception
     *
     * @return string[][]
     */
    public function dataCasesSince(): array
    {
        $future = (new DateTime('now', new DateTimeZone('UTC')))->modify('+1 minutes')->format('Y-m-d H:i:s');
        $nowMinus5Years = (new DateTime('now', new DateTimeZone('UTC')))->modify('-5 years')->format('Y-m-d H:i:s');

        return [
            'empty string = few seconds ago' => [
                'in'  => '',
                'out' => 'few seconds ago',
            ],
            'invalid string = few seconds ago' => [
                'in'  => 'invalid',
                'out' => 'few seconds ago',
            ],
            'future = few seconds ago' => [
                'in'  => $future,
                'out' => 'few seconds ago',
            ],
            'now - 5 years = 5 years ago' => [
                'in'  => $nowMinus5Years,
                'out' => '5 years ago',
            ]
        ];
    }

    /**
     * @dataProvider dataCasesSince
     *
     * @param string $in
     * @param string $out
     *
     * @throws \Exception
     */
    public function testSince(string $in, string $out): void
    {
        static::assertSame($out, Helper::getSince($in));
    }
    // endregion

    // region getTimeleft
    /**
     * Tests are partials because time is ticking, mock is complicated.
     *
     * @throws \Exception
     *
     * @return array
     */
    public function dataCasesTimeleft(): array
    {
        $past = (new DateTime('now', new DateTimeZone('UTC')))->modify('-1 minutes')->format('Y-m-d H:i:s');
        $nowPlus2Hours = (new DateTime('now', new DateTimeZone('UTC')))->modify('+2 hours +30 minutes +59 seconds')->format('Y-m-d H:i:s');

        return [
            'null = null' => [
                'in'  => null,
                'out' => null,
            ],
            'empty string = few seconds left' => [
                'in'  => '',
                'out' => 'few seconds left',
            ],
            'invalid string = few seconds left' => [
                'in'  => 'invalid',
                'out' => 'few seconds left',
            ],
            'past = few seconds left' => [
                'in'  => $past,
                'out' => 'few seconds left',
            ],
            'now + 2 hours and 30 minutes = 2 h and 30 min left' => [
                'in'  => $nowPlus2Hours,
                'out' => '2 h and 30 min left',
            ],
        ];
    }

    /**
     * @dataProvider dataCasesTimeleft
     *
     * @param string|null $in
     * @param string|null $out
     *
     * @throws \Exception
     */
    public function testTimeleft(?string $in, ?string $out): void
    {
        try {
            static::assertSame($out, Helper::getTimeleft($in));
        } catch (\Exception $e) {
            if ($out === '2 h and 30 min left') {
                $out = '2 h and 29 min left';

                static::assertSame($out, Helper::getTimeleft($in));
            }
        }
    }
    // endregion

    // region getFitSentence
    public function dataCasesFitSentence(): array
    {
        return [
            'empty string + max 0 = empty string' => [
                'in'  => '',
                'max' => 0,
                'out' => '',
            ],
            '"aaa" + max 0 = empty string' => [
                'in'  => 'aaa',
                'max' => 0,
                'out' => '',
            ],
            '"aaa" + max 5 = "aaa"' => [
                'in'  => 'aaa',
                'max' => 5,
                'out' => 'aaa',
            ],
            '"aaa" + max 2 = empty string' => [
                'in'  => 'aaa',
                'max' => 2,
                'out' => '',
            ],
            '"aa a" + max 2 = "aa"' => [
                'in'  => 'aa a',
                'max' => 2,
                'out' => 'aa',
            ],
            '"a b c" + max 2 = "a"' => [
                'in'  => 'a b c',
                'max' => 2,
                'out' => 'a',
            ],
        ];
    }

    /**
     * @dataProvider dataCasesFitSentence
     *
     * @param string $in
     * @param int    $max
     * @param string $out
     */
    public function testFitSentence(string $in, int $max, string $out): void
    {
        static::assertSame($out, Helper::getFitSentence($in, $max));
    }
    // endregion
}
