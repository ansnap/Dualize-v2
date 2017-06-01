<?php

namespace Dualize\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MoveTopicsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('forum', 'entity', array(
                    'class' => 'DualizeForumBundle:Forum',
                    'property' => 'title',
                    'mapped' => false,
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

    public function getName()
    {
        return 'topics_move';
    }

}
