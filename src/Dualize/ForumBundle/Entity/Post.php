<?php

namespace Dualize\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserBundle\Entity\User;
use Dualize\ForumBundle\Entity\Topic;

/**
 * Post
 *
 * @ORM\Table(name="forumpost")
 * @ORM\Entity
 */
class Post
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="content", type="string", length=4096)
     * @Assert\Length(min = "3", max = "4096")
     */
    private $content;

    /**
     * @ORM\Column(name="createdat", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\UserBundle\Entity\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserBundle\Entity\User")
     * @Assert\Valid()
     */
    private $poster;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\ForumBundle\Entity\Topic", inversedBy="posts", fetch="LAZY")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\ForumBundle\Entity\Topic")
     * @Assert\Valid()
     */
    private $topic;

    function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $content
     * @return Post
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
     * @return Post
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
     * @param User $poster
     * @return Post
     */
    public function setPoster(User $poster = null)
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * @return User
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * @param Topic $topic
     * @return Post
     */
    public function setTopic(Topic $topic = null)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

}
