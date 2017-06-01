<?php

namespace Dualize\SocioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relation - types of relations between people defined in the socionics
 *
 * @ORM\Table(name="relation")
 * @ORM\Entity
 */
class Relation
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

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

}
