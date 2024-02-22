<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Doctrine\Type;

use Corytech\BigNumber\BigNumber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DoctrineBigNumberType extends Type
{
    public const TYPE_NAME = 'big_number_type';
    private const TYPE_PRECISION = 30;
    private const TYPE_SCALE = 18;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['precision'] = self::TYPE_PRECISION;
        $column['scale'] = self::TYPE_SCALE;

        return $platform->getDecimalTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?BigNumber
    {
        if ($value === null) {
            return null;
        }

        return BigNumber::of($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return BigNumber::of($value)->shorten();
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }
}
