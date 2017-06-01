<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('image', 'file', array(
			'required' => false,
			'label' => 'Загрузить фото',
			'attr' => array(
				'multiple' => true,
			),
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Dualize\UserBundle\Entity\Photo'
		));
	}

	public function getName()
	{
		return 'photo';
	}

}

