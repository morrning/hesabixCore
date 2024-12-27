<?php
namespace App\Controller\Front;

use App\Security\BackAuthAuthenticator;
use App\Service\twigFunctions;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\UserToken;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{

    #[Route('/login/by/token{route}', name: 'app_login_by_token' , requirements: ['route' => '.+'])]
    public function app_login_by_token(string $route,AuthenticationUtils $authenticationUtils,twigFunctions $functions,Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, BackAuthAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $token = $entityManager->getRepository(UserToken::class)->findOneBy(['tokenID'=>$request->get('tokenID')]);
        if(!$token){
            $token = $entityManager->getRepository(UserToken::class)->findOneBy(['token'=>$request->get('tokenID')]);
            if(!$token)
                throw $this->createNotFoundException('توکن معتبر نیست');
        }

        $userAuthenticator->authenticateUser(
            $token->getUser(),
            $authenticator,
            $request
        );
        return $this->redirect($functions->systemSettings()->getAppSite() . $route);
    }

    /**
     * @throws Exception
     */
    #[Route('/logout/by/token{route}', name: 'app_logout_by_token' , requirements: ['route' => '.+'])]
    public function app_logout_by_token(string $route,twigFunctions $functions,Request $request,Security $security, EntityManagerInterface $entityManager): Response
    {
        try {
            $security->logout(false);
        } catch (Exception $e){}
       return $this->redirect($functions->systemSettings()->getAppSite() . $route);
    }
}
