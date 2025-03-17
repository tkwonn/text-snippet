<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Helpers\DateCalculator;
use Exception;

class DateCalculatorTest extends TestCase
{
    public function testGetExpirationDate()
    {
        // '1min'のテスト
        $result = DateCalculator::getExpirationDate('1min');
        $expected = date('Y-m-d H:i:s', strtotime('+1 minute'));
        $this->assertEquals($expected, $result);

        // '10min'のテスト
        $result = DateCalculator::getExpirationDate('10min');
        $expected = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $this->assertEquals($expected, $result);

        // '1hour'のテスト
        $result = DateCalculator::getExpirationDate('1hour');
        $expected = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $this->assertEquals($expected, $result);

        // '1day'のテスト
        $result = DateCalculator::getExpirationDate('1day');
        $expected = date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->assertEquals($expected, $result);

        // '1week'のテスト
        $result = DateCalculator::getExpirationDate('1week');
        $expected = date('Y-m-d H:i:s', strtotime('+1 week'));
        $this->assertEquals($expected, $result);

        // '1month'のテスト
        $result = DateCalculator::getExpirationDate('1month');
        $expected = date('Y-m-d H:i:s', strtotime('+1 month'));
        $this->assertEquals($expected, $result);

        // 'Never'のテスト
        $result = DateCalculator::getExpirationDate('Never');
        $expected = '9999-12-31 23:59:59';
        $this->assertEquals($expected, $result);

        // 無効なオプションのテスト
        $this->expectException(Exception::class);
        DateCalculator::getExpirationDate('invalid_option');
    }
}

?>
