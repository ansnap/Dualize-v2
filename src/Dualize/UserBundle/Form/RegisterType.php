<?php

namespace Dualize\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;

class RegisterType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('email', 'email', array(
                    'label' => 'Ваш Email',
                    'attr' => array(
                        'placeholder' => 'Адрес электронной почты',
                    ),
                ))
                ->add('name', 'text', array(
                    'label' => 'Ваше имя',
                    'attr' => array(
                        'placeholder' => 'Отображаемое на сайте имя',
                    ),
                ))
                ->add('recaptcha', 'ewz_recaptcha', array(
                    'attr' => array(
                        'options' => array(
                            'theme' => 'light',
                            'type' => 'image'
                        )
                    ),
                    'label' => ' ',
                    'mapped' => false,
                    'constraints' => array(
                        new True()
                    ),
                ))
                ->add('register', 'submit', array(
                    'label' => 'Зарегистрироваться',
        ));
    }

    public function getName() {
        return 'register';
    }

}
