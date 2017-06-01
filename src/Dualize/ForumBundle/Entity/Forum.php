<?php

namespace Dualize\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Dualize\ForumBundle\Entity\Topic;

/**
 * Forum
 *
 * @ORM\Table(name="forum")
 * @ORM\Entity
 */
class Forum
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(min = "3", max = "255")
     */
    private $title;

    /**
     * @ORM\Column(name="description", type="string", length=255)
     * @Assert\Length(min = "3", max = "255")
     */
    private $description;

    /**
     * @ORM\Column(name="position", type="integer")
     * @Assert\Type(type="integer")
     */
    private $position;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\ForumBundle\Entity\Forum")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\ForumBundle\Entity\Forum")
     * @Assert\Valid()
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Dualize\ForumBundle\Entity\Topic", mappedBy="forum", fetch="LAZY")
     * @Assert\All({
     * 		@Assert\Type(type="Dualize\ForumBundle\Entity\Topic")
     * })
     * @Assert\Valid()
     */
    private $topics;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     * @return Forum
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     * @return Forum
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param integer $position
     * @return Forum
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param Forum $parent
     * @return Forum
     */
    public function setParent(Forum $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Forum
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Topic $topic
     * @return Forum
     */
    public function addTopic(Topic $topic)
    {
        $this->topics[] = $topic;

        return $this;
    }

    /**
     * @param Topic $topic
     */
    public function removeTopic(Topic $topic)
    {
        $this->topics->removeElement($topic);
    }

    /**
     * @return ArrayCollection
     */
    public function getTopics()
    {
        return $this->topics;
    }

}
