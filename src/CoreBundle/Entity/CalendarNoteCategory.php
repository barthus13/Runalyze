<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarNoteCategory
 *
 * @ORM\Table(name="calendar_note_category", uniqueConstraints={@ORM\UniqueConstraint(name="unique_internal_id", columns={"accountid", "internal_id"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\CalendarNoteCategoryRepository")
 */
class CalendarNoteCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=10, nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null see \Runalyze\Profile\Calendar\CategoryProfile
     *
     * @ORM\Column(name="internal_id", nullable=true, columnDefinition="tinyint NULL")
     */
    private $internalId = null;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="color",type="string", length=6, options={"fixed" = true})
     */
    protected $color;

    /**
     * @var bool
     *
     * @ORM\Column(name="privacy", type="boolean", columnDefinition="tinyint unsigned NOT NULL DEFAULT 1")
     */
    private $privacy = true;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;


    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $internalId
     *
     * @return $this
     */
    public function setInternalId($internalId)
    {
        $this->internalId = $internalId;

        return $this;
    }

    /**
     * @return int
     */
    public function getInternalId()
    {
        return $this->internalId;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }


    /**
     * @param bool $private
     *
     * @return $this
     */
    public function setPrivacy($private)
    {
        $this->privacy = $private;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }


    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

}

