<?php

namespace Dualize\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'textarea', array(
                    'label' => 'Сообщение',
                    'attr' => array(
                        'placeholder' => 'Ваше сообщение (отправить Ctrl + Enter)',
                    ),
                ))
                ->add('send', 'submit', array(
                    'label' => 'Отправить',
                    'attr' => array(
                        'data-loading-text' => 'Отправка...',
                        'autocomplete' => 'off',
                    ),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\ForumBundle\Entity\Post'
        ));
    }

    public function getName()
    {
        return 'post';
    }

}
