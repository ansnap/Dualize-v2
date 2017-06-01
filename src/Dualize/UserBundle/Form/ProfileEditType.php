<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dualize\UserBundle\Form\ProfileType;

class ProfileEditType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', array(
					'label' => 'Ваше имя',
				))
				->add('profile', new ProfileType())
				->add('save', 'submit', array(
					'label' => 'Сохранить',
				));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Dualize\UserBundle\Entity\User'
		));
	}

	public function getName()
	{
		return 'profileEdit';
	}

}

