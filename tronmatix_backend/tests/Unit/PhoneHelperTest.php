<?php

namespace Tests\Unit;

use App\Support\PhoneHelper;
use Tests\TestCase;

class PhoneHelperTest extends TestCase
{
    /** @dataProvider phoneProvider */
    public function test_normalize_cambodian_numbers(string $input, ?string $expected): void
    {
        $this->assertSame($expected, PhoneHelper::normalize($input));
    }

    /** @dataProvider phoneProvider */
    public function test_toE164_cambodian_numbers(string $input, ?string $expected): void
    {
        $this->assertSame($expected ? '+' . $expected : null, PhoneHelper::toE164($input));
    }

    public static function phoneProvider(): array
    {
        return [
            // User's actual number
            ['012949139', '85512949139'],

            // Local formats with spaces
            ['012 949 139', '85512949139'],
            ['012-949-139', '85512949139'],

            // E.164 with plus sign
            ['+85512949139', '85512949139'],
            ['+855 12 949 139', '85512949139'],

            // Already normalized digits
            ['85512949139', '85512949139'],

            // 00 prefix
            ['0085512949139', '85512949139'],

            // 9-digit local number
            ['0129491395', '855129491395'],

            // Staff/admin number from memory
            ['067 114 814', '85567114814'],

            // Leading zero stripped automatically
            ['012949139', '85512949139'],

            // Invalid — too short
            ['12345', null],

            // Invalid — empty
            ['', null],
        ];
    }
}
