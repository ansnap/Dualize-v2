<?php

namespace Dualize\UserBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Dualize\UserBundle\Entity\City;

class CityToIdTransformer implements DataTransformerInterface
{

	/**
	 * @var ObjectManager
	 */
	private $om;

	/**
	 * @param ObjectManager $om
	 */
	public function __construct(ObjectManager $om)
	{
		$this->om = $om;
	}

	/**
	 * Transforms an object (City) to a string (id).
	 *
	 * @param  City|null $city
	 * @return string
	 */
	public function transform($city)
	{
		if (null === $city) {
			return null;
		}

		return $city->getId();
	}

	/**
	 * Transforms a string (id) to an object (City).
	 *
	 * @param  string $id
	 *
	 * @return City|null
	 *
	 * @throws TransformationFailedException if object (City) is not found.
	 */
	public function reverseTransform($id)
	{
		if (!$id) {
			return null;
		}

		$city = $this->om
				->getRepository('DualizeUserBundle:City')
				->findOneBy(array('id' => $id));

		if (null === $city) {
			throw new TransformationFailedException(sprintf(
					'Город с ID "%s" не найден', $id
			));
		}

		return $city;
	}

}