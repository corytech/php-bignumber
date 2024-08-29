<?php

declare(strict_types=1);

namespace Corytech\BigNumber\Validator;

use Corytech\BigNumber\BigNumber;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RangeValidator;

class BigNumberRangeValidator extends RangeValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var BigNumber $value */
        parent::validate($value->asFloat(), $constraint);
    }
}
