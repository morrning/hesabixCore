<?php

namespace App\Controller;
use App\Service\Provider;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\UserToken;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserController extends AbstractController
{
    /**
     * function to generate random strings
     * @param int $length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString(int $length = 32): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    #[Route('/api/user/login', name: 'api_login')]
    public function api_login(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $tokenString = $this->RandomString(254); // somehow create an API token for $user
        $token = new UserToken();
        $token->setUser($user);
        $token->setToken($tokenString);
        $entityManager->persist($token);
        $entityManager->flush();
        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $tokenString,
        ]);
    }

    #[Route('/api/user/check/login', name: 'api_user_check_login')]
    public function api_user_check_login(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return $this->json(
            ['result'=>true]
        );
    }

    #[Route('/api/user/get/permissions', name: 'api_user_get_permissions')]
    public function api_user_get_permissions(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return $this->json(
            ['is_login'=>true]
        );
    }

    #[Route('/api/user/current/info', name: 'api_user_current_info')]
    public function api_user_current_info(#[CurrentUser] ?User $user,Provider $provider,EntityManagerInterface $entityManager): Response
    {
        return $this->json([
            'email'=>$user->getEmail(),
            'fullname'=>$user->getFullName(),
            'businessCount'=>count($user->getBusinesses()),
            'hash_email'=> $provider->gravatarHash($user->getEmail())
        ]);
    }


    #[Route('/api/user/logout', name: 'api_user_logout')]
    public function api_user_logout(Security $security,EntityManagerInterface $entityManager,Request $request): Response
    {
        // logout the user in on the current firewall
        $security->logout(false);
        $apiToken = $request->headers->get('X-AUTH-TOKEN');

        if (null == $apiToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $tk = $entityManager->getRepository(UserToken::class)->findByApiToken($apiToken);
        if (! $tk) {
            throw new UserNotFoundException();
        }
        $entityManager->getRepository(UserToken::class)->remove($tk,true);
        return $this->json(['result'=>true]);
    }


    #[Route('/api/user/update/info', name: 'api_user_update_info')]
    public function api_user_update_info(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager,Request $request): Response
    {
        $pameters = [];
        if ($content = $request->getContent()) {
            $pameters = json_decode($content, true);
        }
        $user->setFullName($pameters['fullname']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json(['result'=>true]);
    }

    #[Route('/api/user/is_superadmin', name: 'api_user_is_super_admin')]
    public function api_user_is_super_admin(#[CurrentUser] ?User $user,EntityManagerInterface $entityManager,Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->json(['result'=>1]);
    }

    #[Route('/api/user/change/password', name: 'api_user_change_password')]
    public function api_user_change_password(#[CurrentUser] ?User $user,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if($params['pass'] == $params['repass']){
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $params['pass']
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json(['result'=>true]);
        }
        return $this->json(['result'=>false]);
    }

    #[Route('/api/user/register', name: 'api_user_register')]
    public function api_user_register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail('alizadeh.babak@gmail.com1');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setFullName('babak');
        $user->setDateRegister(time());
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                '123456'
            )
        );
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['ok']);
    }
}
