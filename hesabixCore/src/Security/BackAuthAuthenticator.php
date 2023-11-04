<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserTokenRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BackAuthAuthenticator extends AbstractAuthenticator
{
    /**
     * @var UserTokenRepository
     */
    private UserTokenRepository $userTokenRepository;

    public function __construct(UserTokenRepository $userRepository)
    {
        $this->userTokenRepository = $userRepository;
    }
    public function supports(Request $request): ?bool
    {
        // TODO: Implement supports() method.
    }

    /**
     * @throws NonUniqueResultException
     */
    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->get('tokenID');

        if (null == $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $tk = $this->userTokenRepository->findByApiTokenID($apiToken);
        if (! $tk) {
            throw new UserNotFoundException();
        }

        // TODO: Implement authenticate() method.
        return new Passport(
            new UserBadge($apiToken, function() use ($apiToken) {
                $tk = $this->userTokenRepository->findByApiTokenID($apiToken);
                if (! $tk) {
                    throw new UserNotFoundException();
                }
                return $tk->getUser();
            }),
            new PasswordCredentials($tk->getUser()->getPassword()),
            [
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // TODO: Implement onAuthenticationSuccess() method.
        return new Response('1');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // TODO: Implement onAuthenticationFailure() method.
        return new Response('0');
    }

//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        /*
//         * If you would like this class to control what happens when an anonymous user accesses a
//         * protected page (e.g. redirect to /login), uncomment this method and make this class
//         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
//         *
//         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
//         */
//    }
}
