<?php

namespace Dualize\UserBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use Dualize\UserBundle\OAuth\OAuthProvider;

/**
 * VkontakteUserResponse
 */
class VkontakteUserResponse extends PathUserResponse
{

    protected $paths = array(
        'id' => 'response.0.id',
        'photo' => 'response.0.photo_max',
        'gender' => 'response.0.sex',
        'birthday' => 'response.0.bdate',
        'city' => 'response.0.city.title',
    );

    public function getUsername()
    {
        return $this->getValueForPath('id');
    }

    public function getEmail()
    {
        return $this->oAuthToken->getRawToken()['email'];
    }

    public function getProfilePicture()
    {
        return $this->getValueForPath('photo');
    }

    public function getGender()
    {
        $gender = $this->getValueForPath('gender');

        return $gender ? ($gender == 2 ? 'm' : 'f') : null;
    }

    public function getBirthday()
    {
        $birth = $this->getValueForPath('birthday');

        return $birth ? \DateTime::createFromFormat('d.m.Y', $birth) : null;
    }

    public function getCity()
    {
        $city = $this->getValueForPath('city');

        return OAuthProvider::yandexTranslate($city);
    }

}
