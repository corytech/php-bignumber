<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Validator;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BigNumberGreaterThanOrEqual extends GreaterThanOrEqual
{
}
