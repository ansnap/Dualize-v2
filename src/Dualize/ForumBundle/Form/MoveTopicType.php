<?php

namespace Dualize\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoveTopicType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('forum', 'entity', array(
                    'class' => 'DualizeForumBundle:Forum',
                    'property' => 'title',
                ))
                ->add('send', 'submit', array(
                    'label' => 'Переместить',
                    'attr' => array(
                        'data-loading-text' => 'Перемещение...',
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
        return 'topic_move';
    }

}
