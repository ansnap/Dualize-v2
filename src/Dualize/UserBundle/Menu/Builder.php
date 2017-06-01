<?php

namespace Dualize\UserBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Dualize\UserMessageBundle\Controller\MessageController;

class Builder extends ContainerAware
{

    public function mainGuestMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Search', array(
            'route' => 'user_search',
            'label' => '.icon-user Поиск',
        ));


        $menu->addChild('Forums', array(
            'route' => 'forums_index',
            'label' => '.icon-list-alt Форум',
        ));

        $menu->addChild('Registration', array(
            'route' => 'register',
            'label' => '.icon-check Регистрация',
        ));

        return $menu;
    }

    public function mainUserMenu(FactoryInterface $factory, array $options)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->container->get('doctrine')->getManager();

        if (count($user->getPhotos()) > 0) {
            $avatar_src = $this->container->get('liip_imagine.cache.manager')
                    ->getBrowserPath($user->getPhotos()->first()->getFullPath(), 'icon');
        } else {
            $avatar_src = $this->container->get('templating.helper.assets')->getUrl('bundles/dualizeuser/img/no_photo.png');
        }
        $user_avatar_html = '<img src="' . $avatar_src . '" class="img-thumbnail navbar-icon" />';

        $menu = $factory->createItem('root');

        $menu->addChild('Search', array(
            'route' => 'user_search',
            'label' => '.icon-user Поиск',
        ));

        $menu->addChild('Forums', array(
            'route' => 'forums_index',
            'label' => '.icon-list-alt Форум',
        ));

        $menu['Forums']->addChild('Forum view', array(
            'route' => 'forum_view',
        ))->setDisplay(false);

        $menu['Forums']->addChild('Forum topic', array(
            'route' => 'forum_topic',
        ))->setDisplay(false);

        $menu->addChild('User menu', array(
            'label' => $user_avatar_html . $user->getName(),
            'extras' => array(
                'safe_label' => true,
            ),
        ));

        $menu['User menu']->addChild('Profile view', array(
            'route' => 'profile_view',
            'routeParameters' => array('id' => $user->getId()),
            'label' => '.icon-user Мой профиль',
        ));

        $menu['User menu']->addChild('Profile edit', array(
            'route' => 'profile_edit',
            'routeParameters' => array('id' => $user->getId()),
            'label' => '.icon-pencil Редактировать профиль',
        ));

        $menu['User menu']->addChild('Profile photos', array(
            'route' => 'profile_photo',
            'routeParameters' => array('id' => $user->getId()),
            'label' => '.icon-picture Мои фотографии',
        ));

        $menu->addChild('Messages', array(
            'route' => 'dialogs',
            'label' => '.icon-envelope Сообщения <span class="badge message-counter">' . MessageController::countNewMessages($em, $user) . '</span>',
            'extras' => array(
                'safe_label' => true,
            ),
        ));

        $menu->addChild('Options', array(
            'route' => 'profile_options',
            'routeParameters' => array('id' => $user->getId()),
            'label' => '.icon-cog Настройки',
        ));

        $menu->addChild('Logout', array(
            'route' => 'logout',
            'label' => '.icon-log-out Выйти',
        ));

        return $menu;
    }

}
