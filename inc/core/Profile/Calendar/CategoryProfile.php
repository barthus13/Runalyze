<?php

namespace Runalyze\Profile\Calendar;

use Runalyze\Bundle\CoreBundle\Entity\CalendarNoteCategory;
use Runalyze\Util\AbstractEnum;
use Runalyze\Util\AbstractEnumFactoryTrait;

class CategoryProfile extends AbstractEnum
{
    /** @var int */
    const GENERIC = 0;

    /** @var int */
    const INJURY = 1;

    /** @var int */
    const ILLNESS  = 2;

    /**
     * @param int $id id from internal enum
     * @return string
     */
    public static function stringFor($id)
    {
        switch ($id) {
            case self::INJURY:
                return __('Injury');
            case self::ILLNESS:
                return __('Illness');
            default:
                throw new \InvalidArgumentException('Invalid note category id "'.$id.'".');
        }
    }

    /**
     * @param int $id id from internal enum
     * @return CalendarNoteCategory
     */
    public static function objectFor($id)
    {
        switch ($id) {
            case self::INJURY:
                return self::injuryObject();
            case self::ILLNESS:
                return self::illnessObject();
            default:
                throw new \InvalidArgumentException('Invalid note category id "'.$id.'".');
        }
    }

    /**
     * @return CalendarNoteCategory
     */
    public static function injuryObject() {
        return (new CalendarNoteCategory())
                    ->setColor('2442ae')
                    ->setName(self::stringFor(self::INJURY))
                    ->setInternalId(self::INJURY);
    }

    /**
     * @return CalendarNoteCategory
     */
    public static function illnessObject() {
        return (new CalendarNoteCategory())
            ->setColor('2442ae')
            ->setName(self::stringFor(self::ILLNESS))
            ->setInternalId(self::ILLNESS);
    }
}
