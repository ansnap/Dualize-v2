<?php

namespace Dualize\SocioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SociotypeReference - connections between sociotypes and relations
 *
 * @ORM\Table(name="sociotypereference")
 * @ORM\Entity
 */
class SociotypeReference
{

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Sociotype", inversedBy="sociotypeReference")
	 * @ORM\JoinColumn(name="sociotype1_id", referencedColumnName="id")
	 */
	private $sociotype1;

	/**
	 * @ORM\ManyToOne(targetEntity="Sociotype")
	 * @ORM\JoinColumn(name="sociotype2_id", referencedColumnName="id")
	 */
	private $sociotype2;

	/**
	 * @ORM\ManyToOne(targetEntity="Relation")
	 * @ORM\JoinColumn(name="relation_id", referencedColumnName="id")
	 */
	private $relation;

	/**
	 * @return integer 
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \Dualize\SocioBundle\Entity\Sociotype 
	 */
	public function getSociotype1()
	{
		return $this->sociotype1;
	}

	/**
	 * @return \Dualize\SocioBundle\Entity\Sociotype 
	 */
	public function getSociotype2()
	{
		return $this->sociotype2;
	}

	/**
	 * @return \Dualize\SocioBundle\Entity\Relation 
	 */
	public function getRelation()
	{
		return $this->relation;
	}

}