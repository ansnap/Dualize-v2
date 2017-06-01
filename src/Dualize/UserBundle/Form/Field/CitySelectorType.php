<?php

namespace Dualize\UserBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dualize\UserBundle\Form\DataTransformer\CityToIdTransformer;

class CitySelectorType extends AbstractType
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

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$transformer = new CityToIdTransformer($this->om);
		$builder->addModelTransformer($transformer);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'invalid_message' => 'Выбранный город не найден в базе',
		));
	}

	public function getParent()
	{
		return 'integer';
	}

	public function getName()
	{
		return 'city_selector';
	}

}
