<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileOptionsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('messageNotify', 'checkbox', array(
            'label' => 'Уведомления о новых сообщениях по email',
            'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\UserBundle\Entity\Options',
        ));
    }

    public function getName()
    {
        return 'profileOptions';
    }

}
