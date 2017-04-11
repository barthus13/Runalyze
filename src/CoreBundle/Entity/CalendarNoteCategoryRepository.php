<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CalendarNoteCategoryRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return CalendarNoteCategory[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    public function save(CalendarNoteCategory $calendarNoteCategory)
    {
        $this->_em->persist($calendarNoteCategory);
        $this->_em->flush();
    }

    public function remove(CalendarNoteCategory $calendarNoteCategory)
    {
        $this->_em->remove($calendarNoteCategory);
        $this->_em->flush();
    }
}
