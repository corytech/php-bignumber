<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Validator;

use Corytech\BigNumber\BigNumber;
use Symfony\Component\Validator\Constraints\LessThanOrEqualValidator;

class BigNumberLessThanOrEqualValidator extends LessThanOrEqualValidator
{
    protected function compareValues(mixed $value1, mixed $value2): bool
    {
        /** @var BigNumber $value1 */
        return parent::compareValues($value1->asFloat(), $value2);
    }
}