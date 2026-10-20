<?php

namespace Tests\Unit;

use App\Imports\SpreadsheetInput;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SpreadsheetInputTest extends TestCase
{
    public function test_integer_input_accepts_non_negative_whole_numbers(): void
    {
        $this->assertSame(12, SpreadsheetInput::integer('12', 'Tool / jan'));
        $this->assertSame(0, SpreadsheetInput::integer(null, 'Tool / jan'));
    }

    public function test_integer_input_rejects_text_fraction_and_negative_values(): void
    {
        foreach (['12abc', '1.5', '-1', '2147483648'] as $value) {
            try {
                SpreadsheetInput::integer($value, 'Tool / jan');
                $this->fail('Expected an invalid integer to be rejected.');
            } catch (RuntimeException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_date_input_accepts_supported_date_formats(): void
    {
        $this->assertSame('2025-12-31', SpreadsheetInput::date('31/12/2025', 'Tool / date'));
        $this->assertSame('2025-12-31', SpreadsheetInput::date('2025-12-31', 'Tool / date'));
    }

    public function test_date_input_rejects_invalid_dates(): void
    {
        $this->expectException(RuntimeException::class);
        SpreadsheetInput::date('32/01/2025', 'Tool / date');
    }
}