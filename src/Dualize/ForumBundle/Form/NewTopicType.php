<?php

namespace Dualize\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dualize\ForumBundle\Form\PostType;

class NewTopicType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array(
                    'label' => 'Название темы',
                    'attr' => array(
                        'placeholder' => 'Название темы',
                    ),
                ))
                ->add('posts', 'collection', array(
                    'type' => new PostType(),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dualize\ForumBundle\Entity\Topic',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'topic_new';
    }

}
