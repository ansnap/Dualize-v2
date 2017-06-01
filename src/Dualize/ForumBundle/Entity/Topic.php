<?php

namespace Dualize\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\ForumBundle\Entity\Forum;
use Dualize\ForumBundle\Entity\Post;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Topic
 *
 * @ORM\Table(name="forumtopic")
 * @ORM\Entity
 */
class Topic
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
     * @ORM\ManyToOne(targetEntity="Dualize\ForumBundle\Entity\Forum", inversedBy="topics", fetch="LAZY")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\ForumBundle\Entity\Forum")
     * @Assert\Valid()
     */
    private $forum;

    /**
     * @ORM\OneToMany(targetEntity="Dualize\ForumBundle\Entity\Post", mappedBy="topic", fetch="LAZY", cascade={"persist", "remove"})
     * @Assert\All({
     * 		@Assert\Type(type="Dualize\ForumBundle\Entity\Post")
     * })
     * @Assert\Valid()
     */
    private $posts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
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
     * @return Topic
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
     * @param Forum $forum
     * @return Topic
     */
    public function setForum(Forum $forum = null)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * @param Post $post
     * @return Topic
     */
    public function addPost(Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * @param Post $post
     */
    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }

}
