<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dualize\UserBundle\Entity\Country;

/**
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity()
 */
class Region
{

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="name", type="string", length=128)
	 */
	private $name;

	/**
	 * @ORM\ManyToOne(targetEntity="Country")
	 * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
	 */
	private $country;

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

	/**
	 * @return Country 
	 */
	public function getCountry()
	{
		return $this->country;
	}

}