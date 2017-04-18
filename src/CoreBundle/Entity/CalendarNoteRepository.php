<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CalendarNoteRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return CalendarNote[]
     */
    public function findAllFor(Account $account, $limit = null)
    {
        return $this->findBy(
            ['account' => $account->getId()],
            ['startDate' => 'DESC'],
            $limit
        );
    }

    /**
     * @param CalendarNoteCategory $calendarNoteCategory
     * @return CalendarNote[]
     */
    public function findByCategory(CalendarNoteCategory $calendarNoteCategory, $limit = null)
    {
         return $this->findBy(
             ['category' => $calendarNoteCategory],
             ['startDate' => 'DESC'],
             $limit);
    }

    public function save(CalendarNote $calendarNote)
    {
        $this->_em->persist($calendarNote);
        $this->_em->flush();
    }

    public function remove(CalendarNote $calendarNote)
    {
        $this->_em->remove($calendarNote);
        $this->_em->flush();
    }
}
