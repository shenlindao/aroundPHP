<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers HandleEventsCommand
 */
class HandleEventsCommandTest extends TestCase
{
    /**
     * @dataProvider eventDtoDataProvider
     */
    public function testShouldEventBeRanReceiveEventDtoAndReturnCorrectBool(array $event, bool $shouldEventBeRan): void
    {

        $handleEventsCommand = new \App\Commands\HandleEventsCommand(new \App\Application(dirname(path: __DIR__)));

        $result = $handleEventsCommand->shouldEventBeRan($event);

        self::assertEquals($result, $shouldEventBeRan);

        self::assertIsBool($result);

        self::assertTrue($result);

        self::assertFalse(!$result);

        self::assertIsString($event['minute']);

        self::assertIsString($event['hour']);

        self::assertIsString($event['day']);

        self::assertIsString($event['month']);

        self::assertIsString($event['day_of_week']);

        self::assertGreaterThanOrEqual(0, (int)$event['minute']);
        self::assertLessThanOrEqual(59, (int)$event['minute']);

        self::assertGreaterThanOrEqual(0, (int)$event['hour']);
        self::assertLessThanOrEqual(23, (int)$event['hour']);

        self::assertGreaterThanOrEqual(1, (int)$event['day']);
        self::assertLessThanOrEqual(31, (int)$event['day']);

        self::assertGreaterThanOrEqual(1, (int)$event['month']);
        self::assertLessThanOrEqual(12, (int)$event['month']);

        self::assertGreaterThanOrEqual(0, (int)$event['day_of_week']);
        self::assertLessThanOrEqual(6, (int)$event['day_of_week']);
    }

    public static function eventDtoDataProvider(): array
    {
        return [
            [
                [
                    'minute' => date("i"),
                    'hour' => date("H"),
                    'day' => date("d"),
                    'month' => date("m"),
                    'day_of_week' => date("w"),
                ],
                true
            ],
        ];
    }
}
