<?php

namespace Dualize\UserSearchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('gender', 'choice', array(
					'required' => false,
					'choices' => array('m' => 'Парня / Мужчину', 'f' => 'Девушку / Женщину'),
					'label' => 'Найти',
				))
				->add('sociotype', 'entity', array(
					'required' => false,
					'class' => 'DualizeSocioBundle:Sociotype',
					'property' => 'title',
					'label' => 'Социотип',
				))
				->add('ageFrom', 'integer', array(
					'required' => false,
					'label' => 'Возраст от',
				))
				->add('ageTo', 'integer', array(
					'required' => false,
					'label' => 'до',
				))
				->add('locationId', 'text', array(
					'required' => false,
					'label' => 'Город, регион или страна',
				))
				->add('locationType', 'hidden', array(
					'required' => false,
				))
				->add('locationTitle', 'hidden', array(
					'required' => false,
				))
				->add('hasPhoto', 'checkbox', array(
					'required' => false,
					'label' => 'С фото',
				))
				->add('save', 'submit', array(
					'label' => 'Поиск',
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Dualize\UserSearchBundle\Model\SearchParams'
		));
	}

	public function getName()
	{
		// if return only 'search' symfony adds additional field input type='search'
		return 'userSearch';
	}

}

