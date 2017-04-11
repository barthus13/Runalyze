<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CalendarNoteRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return CalendarNote[]
     */
    public function findAllFor(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
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
