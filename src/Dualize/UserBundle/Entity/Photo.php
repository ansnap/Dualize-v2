<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserBundle\Entity\User;

/**
 * Photo
 *
 * @ORM\Table(name="photo")
 * @ORM\Entity()
 */
class Photo
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="photos", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserBundle\Entity\User")
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(name="imagename", type="string", length=255)
     * @var string $imageName
     */
    private $imageName;

    /**
     * @ORM\Column(name="position", type="integer")
     * @var integer $position
     */
    private $position;

    /**
     * @Assert\Image(
     * 		maxSize = "4M",
     * 		mimeTypes = {"image/png", "image/jpeg", "image/pjpeg", "image/gif"}
     * )
     * @var UploadedFile $image
     */
    private $image;

    public function __construct()
    {
        $this->setImageName(uniqid() . '.jpg');
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
     * @return Photo
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     * @return Photo
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Photo
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set image
     *
     * @param UploadedFile $image
     * @return Photo
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Image path in format: user_id/image_name
     * @return string
     */
    public function getSubPath()
    {
        return $this->getUser()->getId() . '/' . $this->getImageName();
    }

    /**
     * Images upload path
     * @return string
     */
    public function getUploadPath()
    {
        return 'uploads/images/profile';
    }

    /**
     * Image full path (after 'web' directory)
     * @return string
     */
    public function getFullPath()
    {
        return $this->getUploadPath() . '/' . $this->getSubPath();
    }

}
