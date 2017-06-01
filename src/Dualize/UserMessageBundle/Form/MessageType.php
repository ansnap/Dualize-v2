<?php

namespace Dualize\UserMessageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MessageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'textarea', array(
                    'label' => 'Сообщение',
                    'attr' => array('placeholder' => 'Ваше сообщение (отправить Ctrl + Enter)'),
                ))
                ->add('send', 'submit', array(
                    'label' => 'Отправить',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\UserMessageBundle\Entity\Message'
        ));
    }

    public function getName()
    {
        return 'message';
    }

}
