<?php

namespace Dualize\UserMessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserBundle\Entity\User;
use Dualize\UserMessageBundle\Entity\Dialog;

/**
 * Message
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="Dualize\UserMessageBundle\Entity\MessageRepository")
 */
class Message
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\UserMessageBundle\Entity\Dialog", inversedBy="messages", fetch="LAZY")
     * @ORM\JoinColumn(name="dialog_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserMessageBundle\Entity\Dialog")
     * @Assert\Valid()
     */
    private $dialog;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\UserBundle\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserBundle\Entity\User")
     * @Assert\Valid()
     */
    private $sender;

    /**
     * @ORM\Column(name="content", type="string", length=8192)
     * @Assert\Length(min = "1", max = "8192")
     * * length also defined in messages.js
     */
    private $content;

    /**
     * @ORM\Column(name="createdat", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @ORM\Column(name="isnew", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $isNew;

    function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->isNew = true;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Dialog $dialog
     * @return Message
     */
    public function setDialog(Dialog $dialog = null)
    {
        $this->dialog = $dialog;

        return $this;
    }

    /**
     * @return Dialog
     */
    public function getDialog()
    {
        return $this->dialog;
    }

    /**
     * @param User $sender
     * @return Message
     */
    public function setSender(User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $content
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \DateTime $createdAt
     * @return Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param boolean $isNew
     * @return Message
     */
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->isNew;
    }

    /**
     * View of created date
     * @return string
     */
    public function getViewCreatedAt()
    {
        $now = new \DateTime();

        // If today than time
        if ($this->createdAt->format('d') == $now->format('d')) {
            return $this->createdAt->format('H:m');
        }

        // Else date
        return $this->createdAt->format('d.m.y');
    }

}
