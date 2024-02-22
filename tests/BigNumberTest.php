<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Tests;

use Corytech\BigNumber\BigNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BigNumberTest extends TestCase
{
    public function testShorten(): void
    {
        self::assertEquals(
            '1',
            BigNumber::of('1.00')->shorten(),
        );

        self::assertEquals(
            '1.01',
            BigNumber::of('1.01')->shorten(),
        );

        self::assertEquals(
            '0',
            BigNumber::of('0.00')->shorten(),
        );

        self::assertEquals(
            BigNumber::of('1')->div(37.5)->floor(BigNumber::COMPANY_SCALE)->shorten(),
            BigNumber::of('1')->div(37.5)->shorten(),
        );
        self::assertEquals(
            BigNumber::of('39.36338509465738677511379468959075365407365015803415089713632422672260161898285607853622276404104886139870773364148943421147558007')->shorten(),
            BigNumber::of('39.363385094657386775')->shorten(),
        );
    }

    public function testIsEqual(): void
    {
        $this->assertTrue(BigNumber::of('1.0')->eq(1.0));
        $this->assertTrue(BigNumber::of('1.0')->eq(1.0, 1));
        $this->assertTrue(BigNumber::of('1.0')->eq(1.7, 0));
        $this->assertTrue(BigNumber::of(1)->eq(1, 2));
        $this->assertTrue(BigNumber::of(1)->eq(1.001, 2));
        $this->assertFalse(BigNumber::of(1)->eq(2, 2));
    }

    public function testIsGreaterThen(): void
    {
        $this->assertTrue(BigNumber::of(2)->gt(1, 2));
        $this->assertTrue(BigNumber::of(1.01)->gt(1, 2));

        $this->assertFalse(BigNumber::of(1.01)->gt(1, 1));
        $this->assertFalse(BigNumber::of(0)->gt(0, 2));
    }

    public function testIsGreaterThenOrEqual(): void
    {
        $this->assertTrue(BigNumber::of(2)->gte(1, 2));
        $this->assertTrue(BigNumber::of(1.01)->gte(1, 2));
        $this->assertTrue(BigNumber::of(0.99)->gte(0.99, 2));

        $this->assertFalse(BigNumber::of(0.98)->gte(0.99, 2));
    }

    public function testIsLessThen(): void
    {
        $this->assertTrue(BigNumber::of(1)->lt(2, 2));
        $this->assertTrue(BigNumber::of(1)->lt(1.01, 2));

        $this->assertFalse(BigNumber::of(1.11)->lt(1.10, 1));
        $this->assertFalse(BigNumber::of(1)->lt(0, 2));
    }

    public function testIsLessThenOrEqual(): void
    {
        $this->assertTrue(BigNumber::of(1)->lte(2, 2));
        $this->assertTrue(BigNumber::of(1)->lte(1.01, 2));
        $this->assertTrue(BigNumber::of(0.99)->lte(0.99, 2));

        $this->assertFalse(BigNumber::of(0.99)->lte(0.98, 2));
    }

    public function testAdd(): void
    {
        $x = BigNumber::of(1.1)
            ->add(1);

        $this->assertTrue($x->eq(2.1, 2));

        $x = BigNumber::of(1.1)
            ->add(1.01, 1);

        $this->assertTrue($x->eq(2.1, 0));
        $this->assertTrue($x->eq(2.1, 256));

        $x = BigNumber::of(1.11)
            ->add(1.01, 1);

        $this->assertTrue($x->eq(2.1));
    }

    public function testSubtract(): void
    {
        $x = BigNumber::of(2.1)
            ->sub(1.00);

        $this->assertTrue($x->eq(1.1, 2));
    }

    public function testMultiply(): void
    {
        $x = BigNumber::of(2)
            ->mul(2);

        $this->assertTrue($x->eq(4, 2));
    }

    public function testDivide(): void
    {
        $x = BigNumber::of(2)
            ->div(2);

        $this->assertTrue($x->eq(1, 2));
    }

    public function testPow(): void
    {
        $x = BigNumber::of(2)
            ->pow(4);

        $this->assertTrue($x->eq(BigNumber::of(16), 2));

        $this->assertTrue(BigNumber::of(10)->pow(-1)->eq(0.1, 2));
    }

    public function testModulo(): void
    {
        $x = BigNumber::of(10)
            ->mod(2);

        $this->assertTrue($x->eq(BigNumber::of(0), 2));

        $x = BigNumber::of(10)
            ->mod(2.5);

        $this->assertTrue($x->eq(BigNumber::of(0), 2));

        $x = BigNumber::of(3)
            ->mod(2.5);

        $this->assertTrue($x->eq(BigNumber::of(0.5), 2));

        $x = BigNumber::of(100.11)
            ->mod(1);

        $this->assertTrue($x->eq(BigNumber::of(0.11), 2));
    }

    public static function roundProvider(): array
    {
        return [
            ['1.5', 0, 2],
            ['11111111111111111111111111111111111111111111111111111111.5', 0, '11111111111111111111111111111111111111111111111111111112'],
            ['-1.5', 0, -2], // why?!
            ['-1.5000000000000000000000000000000000000000000000000000001', 0, -2],
            ['-1.4', 0, -1],
            ['-1.4999999999999999999999999999999999999999999999999999999', 0, -1],
            ['1.499', 0, '1'],
            ['1.499', 1, '1.5'],
            ['1.499', 2, '1.5'],
            ['1.449', 2, '1.45'],
        ];
    }

    #[DataProvider('roundProvider')]
    public function testRound($input, $precision, $expected): void
    {
        $x = BigNumber::of($input)->round($precision);
        $this->assertTrue($x->eq(BigNumber::of($expected)));
    }

    public static function floorProvider(): \Generator
    {
        yield ['1.499', 0, '1'];
        yield ['1.9', 0, '1'];
        yield ['1.999999999999999999999999999999999999', 0, '1'];
        yield ['999.999999999999999999999999999999999999', 0, '999'];
        yield ['999.999999999999999999999999999999999999', 3, '999.999'];
        yield ['999.9', 1, '999.9'];
        yield ['-999.999999999999999999999999999999999999', 3, '-1000.000'];
        yield ['0.002397', 0, '0'];
    }

    #[DataProvider('floorProvider')]
    public function testFloor($input, $precision, $expected): void
    {
        $x = BigNumber::of($input)->floor($precision);
        $this->assertTrue($x->eq(BigNumber::of($expected)));

        $this->assertEquals(
            $expected,
            $x->value($precision)
        );
    }

    public static function ceilProvider(): array
    {
        return [
            ['1.499', 0, '2'],
            ['1.9', 0, '2'],
            ['1.999999999999999999999999999999999999', 0, '2'],
            ['999.999999999999999999999999999999999999', 0, '1000'],
            ['999.999999999999999999999999999999999999', 35, '1000'],
            ['999.9', 1, '999.9'],
            ['-999.999999999999999999999999999999999999', 3, '-999.999'],
        ];
    }

    #[DataProvider('ceilProvider')]
    public function testCeil($input, $precision, $expected): void
    {
        $x = BigNumber::of($input)->ceil($precision);
        $this->assertTrue($x->eq(BigNumber::of($expected)));
    }

    public function testUsage(): void
    {
        $x = BigNumber::of(0)
            // 1
            ->add(1)
            // 2.111
            ->add(BigNumber::of(1.111))
            // 2.110
            ->sub(0.001)
            // 4.220
            ->mul(2)
            // 1.055
            ->div('4.0000')
            // 2
            ->sub(-0.945)
            // 4096
            ->pow(12)
            // 4097
            ->add(1)
            // 1
            ->mod(256)
            ->value(2);

        $this->assertEquals('1.00', $x);
    }

    public function testFormat(): void
    {
        $this->assertEquals(
            '1111111111.111111111111',
            BigNumber::of('1111111111.111111111111')->value(12),
        );
        $this->assertEquals(
            '1111111111.111111111119',
            BigNumber::of('1111111111.111111111119')->value(12),
        );
        $this->assertEquals(
            '1111111111.000000000000',
            BigNumber::of('1111111111.000000000000')->value(12),
        );
        $this->assertEquals(
            '1111111111.999999999999',
            BigNumber::of('1111111111.999999999999')->value(12),
        );
        $this->assertEquals(
            '1111111112',
            BigNumber::of('1111111111.999999999999')->value(0),
        );
        $this->assertEquals(
            '1111111112.00000000000',
            BigNumber::of('1111111111.999999999999')->value(11),
        );

        $this->assertEquals(
            '10.12',
            BigNumber::of('10.1200000000')->shorten(),
        );

        $this->assertEquals(
            '10.12001',
            BigNumber::of('10.1200100000')->shorten(),
        );

        $this->assertEquals(
            '10.1200000001',
            BigNumber::of('10.1200000001')->shorten(),
        );

        $this->assertEquals(
            '1',
            BigNumber::of('1')->shorten(),
        );

        $this->assertEquals(
            '0.1',
            BigNumber::of('0.10000')->shorten(),
        );

        $this->assertEquals(
            '1',
            BigNumber::of('1.0')->shorten(),
        );

        $this->assertEquals(
            '0',
            BigNumber::of('0')->shorten(),
        );

        $this->assertEquals(
            '-0.1',
            BigNumber::of('-0.100')->shorten(),
        );

        $this->assertEquals(
            '0',
            BigNumber::of('0.00')->shorten(),
        );

        $this->assertEquals(
            '-0.001',
            BigNumber::of('-0.001')->shorten(),
        );

        $this->assertEquals(
            '10',
            BigNumber::of('10')->shorten(),
        );
    }

    public static function ofProvider(): array
    {
        return [
            // test int
            [1123, '1123'],
            [-1123, '-1123'],

            // test float
            [111111.3131231241, '111111.3131231241'],
            [-111111.3131231241, '-111111.3131231241'],
            [111100.3131231241, '111100.3131231241'],
            [-111100.3131231241, '-111100.3131231241'],

            // test string
            ['1123', '1123'],
            ['-1123', '-1123'],
            ['111111.3131231241', '111111.3131231241'],
            ['-111111.3131231241', '-111111.3131231241'],
            ['111100.3131231241', '111100.3131231241'],
            ['-111100.3131231241', '-111100.3131231241'],

            // test scientific notation
            [0.00000000034, '0.00000000034'],
            [-0.00000000034, '-0.00000000034'],
            ['0.00000000034', '0.00000000034'],
            ['-0.00000000034', '-0.00000000034'],
            [3.4E-10, '0.00000000034'],
            [-3.4E-10, '-0.00000000034'],
            [3.4e-10, '0.00000000034'],
            [-3.4e-10, '-0.00000000034'],
            ['3.4E-10', '0.00000000034'],
            ['-3.4E-10', '-0.00000000034'],
            ['3.4e-10', '0.00000000034'],
            ['-3.4e-10', '-0.00000000034'],
        ];
    }

    #[DataProvider('ofProvider')]
    public function testOf($input, $expected): void
    {
        $this->assertSame(
            bccomp(
                BigNumber::of($input)->value(),
                $expected
            ), 0
        );

        $this->assertSame(
            bccomp(
                BigNumber::of(BigNumber::of($input))->value(),
                $expected
            ), 0
        );
    }

    public function testOfShouldThrowOnNonNumericString(): void
    {
        $this->expectException(\DomainException::class);

        BigNumber::of('aaa');
    }

    public function testAsFloat(): void
    {
        self::assertEquals(1, BigNumber::of(1)->asFloat());
        self::assertEquals(0.111, BigNumber::of(0.111)->asFloat());
        self::assertEquals(1.0E-12, BigNumber::of(1)->div(BigNumber::of(10)->pow(12))->asFloat());
    }

    public function testAbs(): void
    {
        self::assertEquals(
            BigNumber::of('1')->shorten(),
            BigNumber::of(1)->abs()->shorten()
        );

        self::assertEquals(
            BigNumber::of('1')->shorten(),
            BigNumber::of(-1)->abs()->shorten()
        );

        self::assertEquals(
            BigNumber::of('0.0000001')->shorten(),
            BigNumber::of(0.0000001)->abs()->shorten()
        );

        self::assertEquals(
            BigNumber::of('0.0000001')->shorten(),
            BigNumber::of(-0.0000001)->abs()->shorten()
        );

        $n = BigNumber::of(1);

        self::assertNotSame($n, $n->abs());
    }
}
