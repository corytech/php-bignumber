<?php

declare(strict_types=1);

namespace Corytech\BigNumber;

final readonly class BigNumber implements \Stringable
{
    public const COMPANY_SCALE = 18;

    private const INTERNAL_SCALE = 128;

    private function __construct(
        private string $value
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function ofOrNull(self|float|int|string|null $number): ?self
    {
        if ($number === null) {
            return null;
        }

        return self::of($number);
    }

    public static function of(self|float|int|string $number): self
    {
        if (!is_numeric($number) && !($number instanceof self)) {
            throw new \DomainException(sprintf('Numeric value is expected, "%s" provided', $number));
        }

        if ($number instanceof self) {
            return new self($number->value);
        }

        if (\is_string($number) || is_numeric($number)) {
            $numberUpperString = strtoupper((string) $number);
            $separator = 'E';

            if (str_contains($numberUpperString, $separator)) {
                return self::of(10)
                    ->pow(explode($separator, $numberUpperString)[1])
                    ->mul(explode($separator, $numberUpperString)[0]);
            }

            return new self($numberUpperString);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unexpected type %s',
                \gettype($number)
            )
        );
    }

    public function abs(): self
    {
        return $this->lt(0)
            ? $this->mul(-1)
            : new self($this->value);
    }

    public function asFloat(): float
    {
        return (float) $this->shorten();
    }

    public function value(int $scale = self::COMPANY_SCALE): string
    {
        return $this
            ->round($scale)
            ->value;
    }

    public function shorten(): string
    {
        $parts = explode('.', bcadd($this->value, '0', self::INTERNAL_SCALE));

        $integer = $parts[0];
        $fractional = substr($parts[1], 0, self::COMPANY_SCALE);

        return rtrim(sprintf('%s.%s', $integer, rtrim($fractional, '0')), '.');
    }

    public function eq(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): bool
    {
        return bccomp(
            $this->value,
            self::of($number)->__toString(),
            $scale
        ) === 0;
    }

    public function gt(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): bool
    {
        return bccomp(
            $this->value,
            self::of($number)->__toString(),
            $scale
        ) === 1;
    }

    public function gte(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): bool
    {
        return $this->eq($number, $scale) || $this->gt($number, $scale);
    }

    public function lt(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): bool
    {
        return bccomp(
            $this->value,
            self::of($number)->__toString(),
            $scale
        ) === -1;
    }

    public function lte(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): bool
    {
        return $this->eq($number, $scale) || $this->lt($number, $scale);
    }

    public function add(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcadd(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function sub(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcsub(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function mul(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcmul(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function div(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcdiv(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function pow(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcpow(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function mod(self|float|int|string $number, int $scale = self::INTERNAL_SCALE): self
    {
        return new self(
            bcmod(
                $this->value,
                self::of($number)->__toString(),
                $scale
            )
        );
    }

    public function round(int $scale = 0): self
    {
        return new self(
            self::bcround(
                $this->value,
                $scale
            )
        );
    }

    public function ceil(int $scale = 0): self
    {
        if ($scale === 0) {
            return self::of(self::bcceil($this->value));
        }
        $pow = self::of('10')->pow($scale);

        return $this->mul($pow)->ceil(0)->div($pow);
    }

    public function floor(int $scale = 0): self
    {
        if ($scale === 0) {
            return self::of(self::bcfloor($this->value));
        }
        $pow = self::of('10')->pow($scale);

        return $this->mul($pow)->floor(0)->div($pow);
    }

    private static function bcround(string $number, int $scale = 0): string
    {
        if (str_contains($number, '.')) {
            if (!self::of($number)->lt(0)) {
                return bcadd($number, '0.'.str_repeat('0', $scale).'5', $scale);
            }

            return bcsub($number, '0.'.str_repeat('0', $scale).'5', $scale);
        }

        if ($scale === 0) {
            return $number;
        }

        return $number.'.'.str_repeat('0', $scale);
    }

    private static function bcceil(string $number): string
    {
        if (self::of($number)->lt(0)) {
            $v = $number ? self::bcfloor(substr($number, 1)) : self::bcfloor('');

            return $v ? "-$v" : $v;
        }

        return bcadd(strtok($number, '.'), strtok('.') != '0' ? '1' : '0');
    }

    private static function bcfloor(string $number): string
    {
        return self::of($number)->lt(0) ? '-'.self::bcceil(substr($number, 1)) : strtok($number, '.');
    }
}
