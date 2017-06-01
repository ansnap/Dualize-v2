<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dualize\UserBundle\Form\ProfileOptionsType;

class ProfileSetOptionsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'password', array(
                    'required' => false,
                    'label' => 'Новый пароль',
                ))
                ->add('options', new ProfileOptionsType(), array(
                    'label' => ' ',
                ))
                ->add('save', 'submit', array(
                    'label' => 'Сохранить',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\UserBundle\Entity\User',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'profileSetOptions';
    }

}
