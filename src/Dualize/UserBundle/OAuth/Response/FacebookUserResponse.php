<?php

namespace Dualize\UserBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use Dualize\UserBundle\OAuth\OAuthProvider;

/**
 * VkontakteUserResponse
 */
class FacebookUserResponse extends PathUserResponse
{

    protected $paths = array(
        'photo' => 'picture.data.url',
        'gender' => 'gender',
        'birthday' => 'birthday',
        'city' => 'location.name',
    );

    public function getProfilePicture()
    {
        return $this->getValueForPath('photo');
    }

    public function getGender()
    {
        $gender = $this->getValueForPath('gender');

        return $gender ? ($gender == 'male' ? 'm' : 'f') : null;
    }

    public function getBirthday()
    {
        $birth = $this->getValueForPath('birthday');

        return $birth ? \DateTime::createFromFormat('m/d/Y', $birth) : null;
    }

    public function getCity()
    {
        $city = $this->getValueForPath('city');

        return OAuthProvider::yandexTranslate($city);
    }

}
