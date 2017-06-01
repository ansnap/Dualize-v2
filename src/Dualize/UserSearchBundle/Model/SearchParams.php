<?php

namespace Dualize\UserSearchBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Dualize\UserSearchBundle\Validator\Constraints as DualizeAssert;
use Dualize\UserBundle\Model\Enums;

/**
 * Model for saving search parameters defined by users in the Profile entity in serialized form
 * @DualizeAssert\CorrectAgeRange
 */
class SearchParams implements \Serializable
{

	/**
	 * @Assert\Choice(callback = "getGenders")
	 */
	private $gender;

	/**
	 * @Assert\Type(type="Dualize\SocioBundle\Entity\Sociotype")
	 * @Assert\Valid()
	 */
	private $sociotype;

	/**
	 * Init only when object is deserialized
	 */
	private $sociotypeId;

	/**
	 * @Assert\Range(
	 *      min = 10,
	 *      max = 100
	 * )
	 */
	private $ageFrom;

	/**
	 * @Assert\Range(
	 * 		min = 10,
	 * 		max = 100
	 * )
	 */
	private $ageTo;

	/**
	 * @Assert\GreaterThanOrEqual(
	 *     value = 0
	 * )
	 */
	private $locationId;

	/**
	 * @Assert\Choice(choices={"city", "region", "country"})
	 */
	private $locationType;

	/**
	 * Format: Moscow, Russia
	 * @var string 
	 */
	private $locationTitle;

	/**
	 * @Assert\Type(type="bool")
	 */
	private $hasPhoto;

	public function getGender()
	{
		return $this->gender;
	}

	public function setGender($gender)
	{
		$this->gender = $gender;
	}

	public function getSociotypeId()
	{
		return $this->sociotypeId;
	}

	public function getSociotype()
	{
		return $this->sociotype;
	}

	public function setSociotype($sociotype)
	{
		$this->sociotype = $sociotype;
	}

	public function getAgeFrom()
	{
		return $this->ageFrom;
	}

	public function setAgeFrom($ageFrom)
	{
		$this->ageFrom = $ageFrom;
	}

	public function getAgeTo()
	{
		return $this->ageTo;
	}

	public function setAgeTo($ageTo)
	{
		$this->ageTo = $ageTo;
	}

	public function getLocationId()
	{
		return $this->locationId;
	}

	public function setLocationId($locationId)
	{
		$this->locationId = $locationId;
	}

	public function getLocationType()
	{
		return $this->locationType;
	}

	public function setLocationType($locationType)
	{
		$this->locationType = $locationType;
	}

	public function getLocationTitle()
	{
		return $this->locationTitle;
	}

	public function setLocationTitle($locationTitle)
	{
		$this->locationTitle = $locationTitle;
	}

	public function getHasPhoto()
	{
		return $this->hasPhoto;
	}

	public function setHasPhoto($hasPhoto)
	{
		$this->hasPhoto = $hasPhoto;
	}

	/**
	 * For validation
	 */
	public static function getGenders()
	{
		return array_keys(Enums::$gender);
	}

	/**
	 * Put into serialized view ID of real Entities
	 * AND
	 * Get from it only ID - then fill them in controller
	 */
	public function serialize()
	{
		return serialize(array(
			$this->gender,
			($this->sociotype ? $this->sociotype->getId() : 0),
			$this->ageFrom,
			$this->ageTo,
			$this->locationId,
			$this->locationType,
			$this->locationTitle,
			$this->hasPhoto,
		));
	}

	public function unserialize($serialized)
	{
		list (
				$this->gender,
				$this->sociotypeId,
				$this->ageFrom,
				$this->ageTo,
				$this->locationId,
				$this->locationType,
				$this->locationTitle,
				$this->hasPhoto,
				) = unserialize($serialized);
	}

}