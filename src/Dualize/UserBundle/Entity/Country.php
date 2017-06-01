<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="country")
 * @ORM\Entity()
 */
class Country
{

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="name", type="string", length=128)
	 */
	private $name;

	/**
	 * @return integer 
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string 
	 */
	public function getName()
	{
		return $this->name;
	}

}