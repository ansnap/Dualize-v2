<?php

namespace Dualize\SocioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Sociotype - types of people defined in the socionics
 *
 * @ORM\Table(name="sociotype")
 * @ORM\Entity
 */
class Sociotype
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SociotypeReference", mappedBy="sociotype1")
     */
    private $sociotypeReference;

    public function __construct()
    {
        $this->sociotypeReference = new ArrayCollection();
    }

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSociotypeReferences()
    {
        return $this->sociotypeReference;
    }

}
