<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dualize\UserBundle\Model\Enums;

class ProfileType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('gender', 'choice', array(
					'choices' => Enums::$gender,
					'required' => false,
					'label' => 'Пол',
				))
				->add('birthday', 'birthday', array(
					'required' => false,
					'label' => 'Дата рождения',
					'years' => range(date("Y") - 10, date("Y") - 100),
					'format' => 'dd MMMM yyyy',
				))
				->add('sociotype', 'entity', array(
					'required' => false,
					'label' => 'Социотип',
					'class' => 'DualizeSocioBundle:Sociotype',
					'property' => 'title',
				))
				->add('city', 'city_selector', array(
					'required' => false,
					'label' => 'Город',
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Dualize\UserBundle\Entity\Profile'
		));
	}

	public function getName()
	{
		return 'profile';
	}

}

