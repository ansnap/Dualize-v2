<?php

namespace Dualize\UserMessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserBundle\Entity\User;
use Dualize\UserMessageBundle\Entity\Message;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Dialog
 *
 * @ORM\Table(name="dialog")
 * @ORM\Entity
 */
class Dialog
{

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToMany(targetEntity="Dualize\UserBundle\Entity\User", inversedBy="dialogs")
	 * @ORM\JoinTable(name="dialog_user",
	 *      joinColumns={@ORM\JoinColumn(name="dialog_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 * )
	 * @Assert\All({
	 * 		@Assert\Type(type="Dualize\UserBundle\Entity\User")
	 * })
	 * @Assert\Valid()
	 */
	private $users;

	/**
	 * @ORM\OneToMany(targetEntity="Dualize\UserMessageBundle\Entity\Message", mappedBy="dialog", cascade={"persist", "remove"}, fetch="LAZY")
	 * @Assert\All({
	 * 		@Assert\Type(type="Dualize\UserMessageBundle\Entity\Message")
	 * })
	 * @Assert\Valid()
	 */
	private $messages;

	public function __construct()
	{
		$this->users = new ArrayCollection();
		$this->messages = new ArrayCollection();
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param User $user
	 * @return Dialog
	 */
	public function addUser(User $user)
	{
		$this->users[] = $user;

		return $this;
	}

	/**
	 * @param User $user
	 */
	public function removeUser(User $user)
	{
		$this->users->removeElement($user);
	}

	/**
	 * @return ArrayCollection
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param Message $message
	 * @return Dialog
	 */
	public function addMessage(Message $message)
	{
		$this->messages[] = $message;

		return $this;
	}

	/**
	 * @param Message $message
	 */
	public function removeMessage(Message $message)
	{
		$this->messages->removeElement($message);
	}

	/**
	 * @return ArrayCollection
	 */
	public function getMessages()
	{
		return $this->messages;
	}

}