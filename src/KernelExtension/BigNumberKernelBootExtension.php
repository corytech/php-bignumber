<?php

declare(strict_types=1);

namespace Corytech\BigNumber\KernelExtension;

use Corytech\BigNumber\Doctrine\Type\DoctrineBigNumberType;
use Doctrine\DBAL\Types\Type;

class BigNumberKernelBootExtension
{
    public static function load()
    {
        if (Type::hasType(DoctrineBigNumberType::TYPE_NAME)) {
            return;
        }

        Type::addType(
            DoctrineBigNumberType::TYPE_NAME,
            DoctrineBigNumberType::class
        );
    }
}
