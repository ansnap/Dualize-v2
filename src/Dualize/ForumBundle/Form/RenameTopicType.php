<?php

namespace Dualize\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RenameTopicType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text')
                ->add('send', 'submit', array(
                    'label' => 'Переименовать',
                    'attr' => array(
                        'data-loading-text' => 'Переименование...',
                        'autocomplete' => 'off',
                    ),
                ))
                ->add('cancel', 'button', array(
                    'label' => 'Отмена',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\ForumBundle\Entity\Topic',
        ));
    }

    public function getName()
    {
        return 'topic_rename';
    }

}
