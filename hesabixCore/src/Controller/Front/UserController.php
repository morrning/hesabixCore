<?php

namespace App\Controller\Front;
use App\Controller\CustomUserMessageAuthenticationException;
use App\Entity\Business;
use App\Entity\Permission;
use App\Form\UserRegisterType;
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
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken,
    Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }
    /**
     * function to generate random strings
     * @param int $length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString(int $length = 32): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    #[Route('/login/{msg}', name: 'front_user_login')]
    public function front_user_login(AuthenticationUtils $authenticationUtils, #[CurrentUser] ?User $user,EntityManagerInterface $entityManager,$msg = null): Response
    {
        if($user)
            return $this->redirectToRoute('general_home');
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render("/user/login.html.twig",[
        'last_username' => $lastUsername,
        'error'         => $error,
            'msg'=>$msg
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/logout', name: 'front_user_logout')]
    public function front_user_logout(): never
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/register', name: 'front_user_register')]
    public function front_user_register(Request $request,TranslatorInterface $translator, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //redirect to hesabix app register page
        return $this->redirect('https://app.hesabix.ir/user/register');
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $exist = $entityManager->getRepository(User::class)->findOneBy(['email'=>$form->get('email')->getData()]);
            if($exist){
                $error = new FormError($translator->trans('There is already an account with this email'));
                $form->get('email')->addError($error);

            }
            else{
                $user->setDateRegister(time());
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('noreplay@hesabix.ir', 'حسابیکس'))
                        ->to($user->getEmail())
                        ->subject('تایید عضویت در حسابیکس')
                        ->htmlTemplate('user/confirmation_email.html.twig')
                );
                // do anything else you need here, like send an email
                return $this->redirectToRoute('front_user_login',['msg'=>'success']);

            }

        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/success', name: 'app_register_success')]
    public function app_register_success(Request $request): Response
    {
        return $this->render('registration/register-success.html.twig', [
        ]);
    }
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'ایمیل شما تایید شد.');

        return $this->redirectToRoute('app_register');
    }

    #[Route('/login/by/token', name: 'app_login_by_token')]
    public function app_login_by_token(Request $request): Response
    {
        
        // log the user in on the current firewall
        $security->login($user);

        // if the firewall has more than one authenticator, you must pass it explicitly
        // by using the name of built-in authenticators...
        $security->login($user, 'form_login');
        // ...or the service id of custom authenticators
        $security->login($user, ExampleAuthenticator::class);

        // you can also log in on a different firewall
        $security->login($user, 'form_login', 'other_firewall');

        // use the redirection logic applied to regular login
        $redirectResponse = $security->login($user);
        return $redirectResponse;

        // or use a custom redirection logic (e.g. redirect users to their account page)
        // return new RedirectResponse('...');
    }
}
