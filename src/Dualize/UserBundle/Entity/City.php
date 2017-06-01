<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dualize\UserBundle\Entity\Region;
use Dualize\UserBundle\Entity\Country;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity()
 */
class City
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
	 * @ORM\ManyToOne(targetEntity="Country")
	 * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
	 */
	private $country;

	/**
	 * @ORM\ManyToOne(targetEntity="Region")
	 * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
	 */
	private $region;

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

	/**
	 * @return Region 
	 */
	public function getRegion()
	{
		return $this->region;
	}

}