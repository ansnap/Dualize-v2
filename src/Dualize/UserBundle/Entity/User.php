<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Dualize\UserBundle\Entity\Photo;
use Dualize\UserBundle\Entity\Profile;
use Dualize\UserBundle\Entity\Options;
use Dualize\UserMessageBundle\Entity\Dialog;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity()
 */
class User implements UserInterface, \Serializable {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="email", type="string", length=40, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="name", type="string", length=40)
     * @Assert\NotBlank()
     * @Assert\Length(min = "2", max = "40")
     */
    private $name;

    /**
     * @ORM\Column(name="salt", type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(name="password", type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(name="plainpassword", type="string", length=30, nullable=true)
     * @Assert\Length(min = "6", max = "4096")
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="role", type="string", length=1)
     * @Assert\NotBlank()
     */
    private $role;

    /**
     * Roles for security system (keys up to 1 symbol)
     */
    private $role_list = array(
        'u' => 'ROLE_USER',
        'm' => 'ROLE_MODERATOR',
        'a' => 'ROLE_ADMIN',
    );

    /**
     * @ORM\Column(name="createdat", type="date")
     * @Assert\Date()
     */
    private $createdAt;

    /**
     * @ORM\Column(name="lastvisit", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $lastVisit;

    /* Extension fields */

    /**
     * @ORM\OneToMany(targetEntity="Dualize\UserBundle\Entity\Photo", mappedBy="user", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\OrderBy({"position" = "ASC"})
     * @Assert\All({
     * 		@Assert\Type(type="Dualize\UserBundle\Entity\Photo")
     * })
     * @Assert\Valid()
     */
    private $photos;

    /**
     * @ORM\OneToOne(targetEntity="Dualize\UserBundle\Entity\Profile", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Type(type="Dualize\UserBundle\Entity\Profile")
     * @Assert\Valid()
     */
    private $profile;

    /**
     * @ORM\OneToOne(targetEntity="Dualize\UserBundle\Entity\Options", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\JoinColumn(name="options_id", referencedColumnName="id", onDelete="SET NULL")
     * @Assert\Type(type="Dualize\UserBundle\Entity\Options")
     * @Assert\Valid()
     */
    private $options;

    /**
     * @ORM\ManyToMany(targetEntity="Dualize\UserMessageBundle\Entity\Dialog", mappedBy="users", fetch="LAZY")
     * @Assert\All({
     * 		@Assert\Type(type="Dualize\UserMessageBundle\Entity\Dialog")
     * })
     * @Assert\Valid()
     */
    private $dialogs;

    // TODO: UserSubscriber when all users from dialog deleted

    public function __construct() {

        $this->setPlainPassword($this->generatePassword());
        $this->setRole('ROLE_USER');
        $this->setProfile(new Profile());
        $this->setOptions(new Options());
        $this->setCreatedAt(new \DateTime());
        $this->photos = new ArrayCollection(); // Doctrine requirement
        $this->dialogs = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $salt
     * @return User
     */
    public function setSalt($salt) {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $plainPassword
     * @return User
     */
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * @param string $role
     * @return User
     */
    public function setRole($role) {
        $this->role = array_search($role, $this->role_list);

        if (!$this->role) {
            throw new \InvalidArgumentException('Not valid user role');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRole() {
        return $this->role_list[$this->role];
    }

    /**
     * @return array
     */
    public function getRoles() {
        return array($this->role_list[$this->role]);
    }

    public function eraseCredentials() {
        
    }

    public function getUsername() {
        return $this->email;
    }

    public function serialize() {
        return serialize(array(
            $this->id,
        ));
    }

    public function unserialize($serialized) {
        list (
                $this->id,
                ) = unserialize($serialized);
    }

    /**
     * Generate random password
     */
    public function generatePassword() {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    /**
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $lastVisit
     * @return User
     */
    public function setLastVisit($lastVisit) {
        $this->lastVisit = $lastVisit;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastVisit() {
        return $this->lastVisit;
    }

    /**
     * @param Photo $photos
     * @return User
     */
    public function addPhoto(Photo $photo) {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * @param Photo $photos
     */
    public function removePhoto(Photo $photo) {
        $this->photos->removeElement($photo);
    }

    /**
     * @return ArrayCollection
     */
    public function getPhotos() {
        return $this->photos;
    }

    /**
     * @param Profile $profile
     * @return User
     */
    public function setProfile(Profile $profile = null) {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Profile
     */
    public function getProfile() {
        return $this->profile;
    }

    /**
     * @param Options $options
     * @return User
     */
    public function setOptions(Options $options = null) {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Options
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param Dialog $dialog
     * @return User
     */
    public function addDialog(Dialog $dialog) {
        $this->dialogs[] = $dialog;

        return $this;
    }

    /**
     * @param Dialog $dialog
     */
    public function removeDialog(Dialog $dialog) {
        $this->dialogs->removeElement($dialog);
    }

    /**
     * @return ArrayCollection
     */
    public function getDialogs() {
        return $this->dialogs;
    }

    /**
     * Rusificated and adapted view of last visit
     * @return string
     */
    public function getViewLastVisit() {
        $now = new \DateTime();

        if (!$this->lastVisit) {
            return null;
        }

        if ($this->lastVisit > new \DateTime('-15 minute')) {
            return 'Online';
        }

        if ($this->lastVisit > new \Datetime('-1 day') && $this->lastVisit->format('d') == $now->format('d')) {
            return 'сегодня в ' . $this->lastVisit->format('H:m');
        }

        if ($this->lastVisit > new \Datetime('-2 day') && $this->lastVisit->format('d') == $now->format('d') - 1) {
            return 'вчера в ' . $this->lastVisit->format('H:m');
        }

        // Rusification of dates
        $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, $this->lastVisit->getTimezone()->getName());

        if ($this->lastVisit->format('y') == $now->format('y')) {
            $formatter->setPattern('d MMMM в HH:mm');
            return $formatter->format($this->lastVisit);
        }

        $formatter->setPattern('d MMMM y г. в HH:mm');
        return $formatter->format($this->lastVisit);
    }

}
