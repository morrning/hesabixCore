<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\EmailHistory;
use App\Entity\Permission;
use App\Service\Extractor;
use App\Service\Provider;
use App\Service\SMS;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
use App\Service\registryMGR;
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
use function PHPUnit\Framework\throwException;

class UserController extends AbstractController
{
    /**
     * function to generate random strings
     * @param int $length 	number of characters in the generated string
     * @return 		string	a new string is created with random characters of the desired length
     */
    private function RandomString(int $length = 32, $justNumber = false): string
    {
        if ($justNumber)
            return substr(str_shuffle(str_repeat($x = '0123456789', ceil($length / strlen($x)))), 1, $length);

        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    #[Route('/api/user/login', name: 'api_login')]
    public function api_login(TranslatorInterface $translatorInterface, Extractor $extractor, Request $request, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        $params = $request->getPayload()->all();
        if (array_key_exists('standard', $params)) {
            if (null === $user) {
                return $this->json($extractor->operationFail(
                    $translatorInterface->trans('login_fail'),
                ));
            }
            $token = new UserToken();
            $token->setUser($user);
            $token->setToken($this->RandomString(254));
            $token->setTokenID($this->RandomString(254));
            $entityManager->persist($token);
            $entityManager->flush();
            return $this->json($extractor->operationSuccess([
                'user' => $user->getUserIdentifier(),
                'token' => $token->getToken(),
                'tokenID' => $token->getTokenID()
            ]));
        } else {
            if (null === $user) {
                return $this->json([
                    'message' => 'missing credentials',
                ], Response::HTTP_UNAUTHORIZED);
            }
            $token = new UserToken();
            $token->setUser($user);
            $token->setToken($this->RandomString(254));
            $token->setTokenID($this->RandomString(254));
            $entityManager->persist($token);
            $entityManager->flush();
            return $this->json([
                'user' => $user->getUserIdentifier(),
                'token' => $token->getToken(),
                'tokenID' => $token->getTokenID()
            ]);
        }

    }

    #[Route('/api/user/has/role/{id}', name: 'api_user_has_role')]
    public function api_user_has_role(Extractor $extractor,#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, $id): Response
    {
        if ($this->isGranted($id)) {
            return $this->json(
                $extractor->operationSuccess()
            );
        }
        return $this->json(
            $extractor->operationFail()
        );
    }
    #[Route('/api2/user/check/login', name: 'api2_user_check_login')]
    public function api2_user_check_login(Extractor $extractor, TranslatorInterface $translatorInterface, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json($extractor->operationFail(
                $translatorInterface->trans('not_loged_in')
            ));
        }
        return $this->json($extractor->operationSuccess([
            [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'active' => $user->isActive(),
                'name' => $user->getFullName(),
                'mobile' => $user->getMobile()
            ]
        ]));
    }
    #[Route('/api/user/check/login', name: 'api_user_check_login')]
    public function api_user_check_login(Extractor $extractor, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json(
                $extractor->operationFail('user not loged in')
            );
        }
        return $this->json(
            $extractor->operationSuccess(
                [
                    'email' => $user->getEmail(),
                    'active' => $user->isActive()
                ]
            )
        );
    }

    #[Route('/api/user/get/permissions', name: 'api_user_get_permissions')]
    public function api_user_get_permissions(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }
        return $this->json(
            ['is_login' => true]
        );
    }

    #[Route('/api/user/get/users/of/business/{bid}', name: 'api_user_get_of_business')]
    public function api_user_get_of_business($bid, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        $business = $entityManager->getRepository(Business::class)->find($bid);
        if (!$business)
            throw $this->createNotFoundException();
        $perms = $entityManager->getRepository(Permission::class)->findBy(['bid' => $business]);
        $out = [];
        foreach ($perms as $perm) {
            $temp = [];
            $temp['name'] = $perm->getUser()->getFullName();
            $temp['email'] = $perm->getUser()->getEmail();
            $temp['owner'] = $perm->isOwner();
            $out[] = $temp;
        }
        return $this->json($out);
    }

    #[Route('/api/user/current/info', name: 'api_user_current_info')]
    public function api_user_current_info(#[CurrentUser] ?User $user, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullname' => $user->getFullName(),
            'businessCount' => count($user->getBusinesses()),
            'hash_email' => $provider->gravatarHash($user->getEmail()),
            'mobile' => $user->getMobile()
        ]);
    }

    #[Route('/api2/user/current/info', name: 'api2_user_current_info')]
    public function api2_user_current_info(#[CurrentUser] ?User $user, Extractor $extractor, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        if ($user) {
            return $this->json($extractor->operationSuccess([
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'businessCount' => count($user->getBusinesses()),
                'hash_email' => $provider->gravatarHash($user->getEmail()),
                'mobile' => $user->getMobile(),
            ]));
        }
        return $this->json($extractor->operationFail('not loged in user'));
    }

    #[Route('/api/user/logout', name: 'api_user_logout')]
    public function api_user_logout(Security $security, EntityManagerInterface $entityManager, Request $request): Response
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
        if (!$tk) {
            throw new UserNotFoundException();
        }
        $entityManager->getRepository(UserToken::class)->remove($tk, true);
        return $this->json(['result' => true]);
    }


    #[Route('/api/user/update/info', name: 'api_user_update_info')]
    public function api_user_update_info(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $pameters = [];
        if ($content = $request->getContent()) {
            $pameters = json_decode($content, true);
        }
        $user->setFullName($pameters['fullname']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json(['result' => true]);
    }

    #[Route('/api/user/is_superadmin', name: 'api_user_is_super_admin')]
    public function api_user_is_super_admin(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->json(['result' => 1]);
    }

    #[Route('/api/user/change/password', name: 'api_user_change_password')]
    public function api_user_change_password(Extractor $extractor,#[CurrentUser] ?User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if ($params['pass'] == $params['repass']) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $params['pass']
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json($extractor->operationSuccess());
        }
        return $this->json($extractor->operationFail(
            'کلمات عبور یکسان نیست'
        ));
    }

    #[Route('/api/user/register', name: 'api_user_register')]
    public function api_user_register(Extractor $extractor, registryMGR $registryMGR, SMS $SMS, MailerInterface $mailer, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('name', $params) && array_key_exists('email', $params) && array_key_exists('mobile', $params) && array_key_exists('password', $params)) {
            if ($entityManager->getRepository(User::class)->findOneBy(['email' => trim($params['email'])])) {
                return $this->json($extractor->operationFail(
                    'پست الکترونیکی وارد شده قبلا ثبت شده است'
                ));
            } elseif ($entityManager->getRepository(User::class)->findOneBy(['mobile' => trim($params['mobile'])])) {
                return $this->json($extractor->operationFail(
                    'شماره تلفن وارد شده قبلا ثبت شده است'
                ));
            }
            $user = new User();
            $user->setEmail($params['email']);
            $user->setRoles(['ROLE_USER']);
            $user->setFullName($params['name']);
            $user->setMobile($params['mobile']);
            $user->setVerifyCodeTime(time() + 300);
            $user->setVerifyCode($this->RandomString(6, true));
            $user->setDateRegister(time());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $params['password']
                )
            );
            $user->setActive(false);
            $entityManager->persist($user);
            $entityManager->flush();
            $SMS->send(
                [$user->getVerifyCode()],
                $registryMGR->get('sms', 'f2a'),
                $user->getMobile()
            );
            try {
                $email = (new Email())
                    ->to($user->getEmail())
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject('تایید ایمیل در حسابیکس')
                    ->html(
                        $this->renderView('user/email/confrim-register.html.twig', [
                            'code' => $user->getVerifyCode()
                        ])
                    );

                $mailer->send($email);
            } catch (Exception $exception) {
            }
            return $this->json($extractor->operationSuccess([
                'id' => $user->getId()
            ]));
        }
        return $this->json($extractor->operationFail(
            'تمام موارد لازم را وارد کنید.'
        ));
    }

    #[Route('/api/user/active/code/info/{id}', name: 'api_user_active_code_info')]
    public function api_user_active_code_info(registryMGR $registryMGR, MailerInterface $mailer, SMS $SMS, string $id, #[CurrentUser] ?User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $send = false;
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user)
            throw $this->createNotFoundException('user not exist');
        if (!$user->getMobile())
            return $this->json(['id' => $user->getId(), 'active' => false, 'result' => 'mobilenotset']);
        if ($user->isActive())
            return $this->json(['id' => $user->getId(), 'active' => true]);
        $res = [];
        $res['id'] = $user->getId();
        $res['email'] = $user->getEmail();
        $res['time'] = time();
        $res['active'] = false;
        if ($user->getVerifyCodeTime()) {
            if (time() > $user->getVerifyCodeTime()) {
                $user->setVerifyCodeTime(time() + 300);
                $user->setVerifyCode($this->RandomString(6, true));
                $entityManager->persist($user);
                $entityManager->flush();
                $send = true;
            }
        } else {
            $user->setVerifyCodeTime(time() + 300);
            $user->setVerifyCode($this->RandomString(6, true));
            $entityManager->persist($user);
            $entityManager->flush();
            $send = true;
        }
        $res['cutDown'] = $user->getVerifyCodeTime();

        if ($send) {
            //send sms and email
            $SMS->send(
                [$user->getVerifyCode()],
                $registryMGR->get('sms', 'f2a'),
                $user->getMobile()
            );
            $email = (new Email())
                ->to($user->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('تایید ایمیل در حسابیکس')
                ->html(
                    $this->renderView('user/email/confrim-register.html.twig', [
                        'code' => $user->getVerifyCode()
                    ])
                );

            $mailer->send($email);
        }
        return $this->json($res);
    }

    #[Route('/api/user/reset/password/send-to-sms', name: 'api_user_forget_reset_password')]
    public function api_user_forget_reset_password(Extractor $extractor, registryMGR $registryMGR, MailerInterface $mailer, SMS $SMS, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (array_key_exists('code', $params) && array_key_exists('id', $params)) {
            $obj = $entityManager->getRepository(User::class)->find($params['id']);
            if ($obj) {
                if ($obj->getVerifyCodeTime() > time()) {
                    $obj = $entityManager->getRepository(User::class)->findOneBy(['id' => $params['id'], 'verifyCode' => $params['code']]);
                    if ($obj) {
                        //reset password
                        $password = $this->RandomString(12, true);
                        $obj->setPassword(
                            $userPasswordHasher->hashPassword(
                                $obj,
                                $password
                            )
                        );
                        $entityManager->persist($obj);
                        $entityManager->flush();

                        $SMS->send(
                            [$password],
                            $registryMGR->get('sms', 'changePassword'),
                            $obj->getMobile()
                        );
                        $email = (new Email())
                            ->to($obj->getEmail())
                            ->priority(Email::PRIORITY_HIGH)
                            ->subject('تغییر کلمه عبور')
                            ->html(
                                $this->renderView('user/email/reset-password.html.twig', [
                                    'code' => $password
                                ])
                            );
                        $mailer->send($email);
                        return $this->json($extractor->operationSuccess(
                            [],
                            'کلمه عبور جدید از طریق پیامک و پست الکترونیکی ارسال شد.'
                        ));
                    }
                    //code is incorrect
                    return $this->json($extractor->operationFail('کد احزار هویت اشتباه است!', 1));
                } else
                    return $this->json($extractor->operationFail(
                        'کد احراز هویت منقضی شده است لطفا مجددا درخواست خود را ارسال نمایید.',
                        2
                    ));
            }
        }
        throw $this->createAccessDeniedException();
    }

    #[Route('/api/user/active/account', name: 'api_user_active_account')]
    public function api_user_active_account(Extractor $extractor, MailerInterface $mailer, SMS $SMS, #[CurrentUser] ?User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('code', $params) || !array_key_exists('mobile', $params))
            return $this->json($extractor->paramsNotSend());

        $user = $entityManager->getRepository(User::class)->findOneBy(['mobile' => $params['mobile']]);
        if (!$user)
            return $this->json($extractor->operationFail('کاربری با این شماره تلفن یافت نشد'));
        if ($user->isActive())
            return $this->json($extractor->operationFail('این کاربر قبلا تایید هویت شده است.'));

        if ($user->getVerifyCode() == $params['code']) {
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json($extractor->operationSuccess(
                ['id'=>$user->getId()],
                'حساب کاربری شما فعال شد.هماکنون می‌توانید با اطلاعات ثبت نام خود به حساب کاربری وارد شوید.'
            ));
        }
        return $this->json($extractor->operationFail('کد ارسالی اشتباه است.'));
    }
    #[Route('/api/user/forget/password/send-code', name: 'api_user_forget_password_send_code')]
    public function api_user_forget_password_send_code(Extractor $extractor, registryMGR $registryMGR, #[CurrentUser] ?User $user, SMS $SMS, MailerInterface $mailer, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('mobile', $params)) {
            return $this->json($extractor->paramsNotSend());
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['mobile' => $params['mobile']]);
        if (!$user) {
            return $this->json(data: $extractor->operationFail(
                'کاربری با شماره تلفن وارد شده یافت نشد.',
                404
            ));
        }
        if ($user->getVerifyCodeTime() > time()) {
            return $this->json(data: $extractor->operationFail(
                'کد بازیابی رمز عبور اخیرا ارسال شده است.لطفا چند دقیقه دیگر مجددا درخواست خود را ارسال نمایید.',
                600
            ));
        }
        $user->setVerifyCode($this->RandomString(6, true));
        $user->setVerifyCodeTime(time() + 300);
        $entityManager->persist($user);
        $entityManager->flush();
        //send sms and email
        $SMS->send(
            [$user->getVerifyCode()],
            $registryMGR->get('sms', 'recPassword'),
            $user->getMobile()
        );
        $email = (new Email())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject('حسابیکس - فراموشی کلمه عبور')
            ->html(
                $this->renderView('user/email/confrim-forget-password.html.twig', [
                    'code' => $user->getVerifyCode()
                ])
            );

        $mailer->send($email);
        return $this->json($extractor->operationSuccess([
            'id' => $user->getId(),
        ]));
    }
    #[Route('/api/user/save/mobile-number', name: 'api_user_save_mobile_number')]
    public function api_user_save_mobile_number(MailerInterface $mailer, SMS $SMS, #[CurrentUser] ?User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('mobile', $params))
            throw $this->createAccessDeniedException('mobile not set');
        $user = $this->getUser();
        if (!$user->getMobile()) {
            $user->setMobile($params['mobile']);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json(['result' => 'ok']);
        }
        return $this->json(['result' => 'exist-before']);
    }

    #[Route('/api/user/register/resend-active-code', name: 'api_user_register_resend_code')]
    public function api_user_register_resend_code(Extractor $extractor, registryMGR $registryMGR, #[CurrentUser] ?User $user, SMS $SMS, MailerInterface $mailer, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
    {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if (!array_key_exists('mobile', $params)) {
            return $this->json($extractor->paramsNotSend());
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['mobile' => $params['mobile']]);
        if (!$user) {
            return $this->json(data: $extractor->operationFail(
                'کاربری با شماره تلفن وارد شده یافت نشد.',
                404
            ));
        }
        if (!$user->isActive()) {
            return $this->json(data: $extractor->operationFail(
                'حساب کاربری شما قبلا فعال شده است.می‌توانید به حساب کاربری خود وارد شوید.',
                404
            ));
        }
        if ($user->getVerifyCodeTime() > time()) {
            return $this->json(data: $extractor->operationFail(
                'کد بازیابی رمز عبور اخیرا ارسال شده است.لطفا دو دقیقه دیگر مجددا درخواست خود را ارسال نمایید.',
                $user->getVerifyCodeTime()
            ));
        }
        $user->setVerifyCode($this->RandomString(6, true));
        $user->setVerifyCodeTime(time() + 300);
        $entityManager->persist($user);
        $entityManager->flush();
        //send sms and email
        $SMS->send(
            [$user->getVerifyCode()],
            $registryMGR->get('sms', 'f2a'),
            $user->getMobile()
        );
        try {
            $email = (new Email())
                ->to($user->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('تایید ایمیل در حسابیکس')
                ->html(
                    $this->renderView('user/email/confrim-register.html.twig', [
                        'code' => $user->getVerifyCode()
                    ])
                );

            $mailer->send($email);
        } catch (Exception $exception) {
        }
        return $this->json($extractor->operationSuccess([
            'id' => $user->getId(),
        ]));
    }
}
