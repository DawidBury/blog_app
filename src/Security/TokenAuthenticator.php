<?php


namespace App\Security;


use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{
    /**
     * @param PreAuthenticationJWTUserToken $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return \Symfony\Component\Security\Core\User\UserInterface|void|null
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        $user = parent::getUser(
            $preAuthToken,
            $userProvider
        );

        if ($user->getPasswordChangeDate() && $preAuthToken->getPayload()['iat'] < $user->getPasswordChangeDate()) {
            throw new ExpiredTokenException();
        }

        return $user;
    }
}