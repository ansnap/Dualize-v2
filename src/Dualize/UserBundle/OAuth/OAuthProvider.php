<?php

namespace Dualize\UserBundle\OAuth;

use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Dualize\UserBundle\Entity\User;
use Dualize\UserBundle\Entity\Photo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * OAuthProvider
 */
class OAuthProvider implements OAuthAwareUserProviderInterface
{

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $email = $response->getEmail();
        $name = $response->getRealName();
        $image = $response->getProfilePicture();
        $identifier = $response->getUsername();
        $gender = $response->getGender();
        $birthday = $response->getBirthday();
        $city_name = $response->getCity();
        $provider = $response->getResourceOwner()->getName();

        $user = $this->em->getRepository('DualizeUserBundle:User')->findOneBy(array('email' => $email));

        if (!$user) {
            // Basic user info
            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $user->getProfile()->setGender($gender);
            $user->getProfile()->setBirthday($birthday);

            switch ($provider) {
                case 'facebook':
                    $user->getProfile()->setFacebookId($identifier);
                    break;
                case 'vkontakte':
                    $user->getProfile()->setVkontakteId($identifier);
                    break;
            }

            // City
            $cities = $this->em->getRepository('DualizeUserBundle:City')->findBy(array('name' => $city_name));

            $main_countries = ['Россия', 'Украина', 'Беларусь'];

            foreach ($main_countries as $country) {
                foreach ($cities as $city) {
                    if ($city->getCountry()->getName() == $country) {
                        $user->getProfile()->setCity($city);
                        break 2;
                    }
                }
            }

            if ($user->getProfile()->getCity() === null && count($cities) > 0) {
                $user->getProfile()->setCity($cities[0]);
            }

            // Photo
            $photo = new Photo();
            $photo->setUser($user);
            $photo->setPosition(1);

            $tmp_file = tempnam(sys_get_temp_dir(), '');
            file_put_contents($tmp_file, file_get_contents($image));
            $photo->setImage(new UploadedFile($tmp_file, ''));

            $user->addPhoto($photo);

            // Save user
            $this->em->persist($user);
        } else {
            if ($provider == 'facebook' && $user->getProfile()->getFacebookId() == null) {
                $user->getProfile()->setFacebookId($identifier);
            }
            if ($provider == 'vkontakte' && $user->getProfile()->getVkontakteId() == null) {
                $user->getProfile()->setVkontakteId($identifier);
            }
        }

        $this->em->flush();

        return $user;
    }

    public static function yandexTranslate($text)
    {
        $key = 'trnsl.1.1.20140426T120935Z.b57a714b78a47bf5.46ec73fb94f12053806d22186dd81422d1bcbe5f';
        $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $key . '&lang=en-ru&text=' . urlencode($text);

        $json = file_get_contents($url);
        $data = json_decode($json);

        return $data->text[0];
    }

}
