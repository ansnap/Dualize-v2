<?php

namespace Dualize\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserBundle\Model\Enums;
use Dualize\UserBundle\Entity\City;

/**
 * Profile
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity()
 */
class Profile {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     * @Assert\Choice(callback = "getGenders")
     */
    private $gender;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Assert\Date()
     */
    private $birthday;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\SocioBundle\Entity\Sociotype")
     * @ORM\JoinColumn(name="sociotype_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\SocioBundle\Entity\Sociotype")
     * @Assert\Valid()
     */
    private $sociotype;

    /**
     * @ORM\ManyToOne(targetEntity="Dualize\UserBundle\Entity\City", fetch="LAZY")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * @Assert\Type(type="Dualize\UserBundle\Entity\City")
     * @Assert\Valid()
     */
    private $city;

    /**
     * @ORM\Column(name="vkontakteid", type="string", length=40, nullable=true)
     * @Assert\Length(min = "1", max = "40")
     */
    private $vkontakteId;

    /**
     * @ORM\Column(name="facebookid", type="string", length=40, nullable=true)
     * @Assert\Length(min = "1", max = "40")
     */
    private $facebookId;

    /**
     * @ORM\Column(name="stypedetermined", type="string", length=500, nullable=true)
     * @Assert\Length(min = "0", max = "500")
     */
    private $sTypeDetermined;

    /**
     * @ORM\Column(name="stypeconfidence", type="smallint")
     * @Assert\Range(min = 0, max = 100)
     */
    private $sTypeConfidence;

    /**
     * @ORM\Column(name="sownlevel", type="smallint")
     * @Assert\Range(min = 0, max = 100)
     */
    private $sOwnLevel;

    /**
     * @ORM\Column(name="stypingothers", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $sTypingOthers;

    /**
     * @ORM\Column(name="interests", type="string", length=500, nullable=true)
     * @Assert\Length(min = "0", max = "500")
     */
    private $interests;

    /**
     * @ORM\Column(name="occupation", type="string", length=500, nullable=true)
     * @Assert\Length(min = "0", max = "500")
     */
    private $occupation;

    /**
     * @ORM\Column(name="aboutme", type="string", length=500, nullable=true)
     * @Assert\Length(min = "0", max = "500")
     */
    private $aboutme;

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $gender
     * @return Profile
     */
    public function setGender($gender) {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @param \DateTime $birthday
     * @return Profile
     */
    public function setBirthday($birthday) {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday() {
        return $this->birthday;
    }

    /**
     * @param Dualize\SocioBundle\Entity\Sociotype $sociotype
     * @return Profile
     */
    public function setSociotype($sociotype) {
        $this->sociotype = $sociotype;

        return $this;
    }

    /**
     * @return Dualize\SocioBundle\Entity\Sociotype
     */
    public function getSociotype() {
        return $this->sociotype;
    }

    /**
     * @param \Dualize\UserBundle\Entity\City $city
     * @return Profile
     */
    public function setCity(\Dualize\UserBundle\Entity\City $city = null) {
        $this->city = $city;

        return $this;
    }

    /**
     * @return \Dualize\UserBundle\Entity\City
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $vkontakteId
     * @return Profile
     */
    public function setVkontakteId($vkontakteId) {
        $this->vkontakteId = $vkontakteId;

        return $this;
    }

    /**
     * @return string
     */
    public function getVkontakteId() {
        return $this->vkontakteId;
    }

    /**
     * @param string $facebookId
     * @return Profile
     */
    public function setFacebookId($facebookId) {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId() {
        return $this->facebookId;
    }

    /**
     * For validation
     */
    public static function getGenders() {
        return array_keys(Enums::$gender);
    }

    /**
     * For display in twig
     */
    public function getViewGender() {
        return Enums::$gender[$this->gender];
    }

    public function getAge() {
        if ($this->getBirthday()) {
            $now = new \DateTime();
            $age = $now->diff($this->getBirthday())->y;
            $t1 = $age % 10;
            $t2 = $age % 100;
            $append = ($t1 == 1 && $t2 != 11 ? "год" : ($t1 >= 2 && $t1 <= 4 && ($t2 < 10 || $t2 >= 20) ? "года" : "лет"));
            return $age . ' ' . $append;
        }
    }

}
