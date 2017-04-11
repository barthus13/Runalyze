<?php

namespace Runalyze\Profile\Calendar;

use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class CategoryProfile extends AbstractEnum
{
    use AbstractEnumFactoryTrait;

    /** @var int */
    const GENERIC = 0;

    /** @var int */
    const INJURY = 1;

    /** @var int */
    const ILLNESS  = 2;

}
