<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserMessageBundle\Entity\Message;

/**
 * Options
 *
 * @ORM\Table(name="options")
 * @ORM\Entity()
 */
class Options
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="searchparams", type="string", length=512, nullable=true)
     * @var serialized array
     */
    private $searchParams;

    /**
     * @ORM\Column(name="messagenotify", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $messageNotify;

    /**
     * @ORM\OneToOne(targetEntity="Dualize\UserMessageBundle\Entity\Message", fetch="LAZY")
     * @ORM\JoinColumn(name="messagenotified_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserMessageBundle\Entity\Message")
     * @Assert\Valid()
     */
    private $messageNotified;

    /**
     * @ORM\Column(name="messagenotifiedat", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $messageNotifiedAt;

    function __construct()
    {
        $this->messageNotify = true;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param serialized array
     * @return Options
     */
    public function setSearchParams($searchParams)
    {
        $this->searchParams = $searchParams;

        return $this;
    }

    /**
     * @return serialized array
     */
    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * @param boolean $messageNotify
     * @return Options
     */
    public function setMessageNotify($messageNotify)
    {
        $this->messageNotify = $messageNotify;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getMessageNotify()
    {
        return $this->messageNotify;
    }

    /**
     * @param Message $messageNotified
     * @return Options
     */
    public function setMessageNotified(Message $messageNotified = null)
    {
        $this->messageNotified = $messageNotified;

        return $this;
    }

    /**
     * @return Message
     */
    public function getMessageNotified()
    {
        return $this->messageNotified;
    }

    /**
     * @param \DateTime $messageNotifiedAt
     * @return Options
     */
    public function setMessageNotifiedAt($messageNotifiedAt)
    {
        $this->messageNotifiedAt = $messageNotifiedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getMessageNotifiedAt()
    {
        return $this->messageNotifiedAt;
    }

}
