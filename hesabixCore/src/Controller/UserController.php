<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\EmailHistory;
use App\Entity\Permission;
use App\Service\CaptchaService;
use App\Service\Extractor;
use App\Service\Provider;
use App\Service\SMS;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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

    #[Route('/api/user/login', name: 'api_login', methods: ['POST'])]
    public function api_login(
        TranslatorInterface $translatorInterface,
        Extractor $extractor,
        Request $request,
        CaptchaService $captchaService,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $params = $request->getPayload()->all();
        $captchaAnswer = $params['captcha_answer'] ?? '';
        $attemptKey = 'login_attempts';

        // بررسی نیاز به کپچا
        $captchaRequired = $captchaService->isCaptchaRequired($attemptKey);
        if ($captchaRequired) {
            if (!$captchaService->validateCaptcha($captchaAnswer)) {
                return $this->json($extractor->operationFail(
                    empty($captchaAnswer) ? 'کپچا لازم است' : 'کپچا اشتباه است',
                    400,
                    ['captcha_required' => true]
                ));
            }
        }

        // چون #[CurrentUser] فقط در صورت احراز هویت موفق کاربر رو برمی‌گردونه،
        // اینجا فقط شرایط بعد از احراز هویت موفق بررسی می‌شه
        if (null === $user) {
            // این خط عملاً اجرا نمی‌شه چون Security Bundle شکست رو مدیریت می‌کنه
            // اما برای اطمینان نگهش داشتم
            $captchaService->incrementAttempts($attemptKey);
            return $this->json($extractor->operationFail(
                $translatorInterface->trans('login_fail'),
                400,
                ['captcha_required' => $captchaService->isCaptchaRequired($attemptKey)]
            ));
        }

        // بررسی وضعیت کاربر
        if ($user->isActive() == false) {
            $captchaService->incrementAttempts($attemptKey);
            return $this->json($extractor->operationFail(
                'حساب کاربری شما فعال نیست. لطفا با پشتیبانی تماس بگیرید',
                506,
                [
                    'user' => $user->getUserIdentifier(),
                    'active' => $user->isActive(),
                    'captcha_required' => $captchaService->isCaptchaRequired($attemptKey)
                ]
            ));
        }

        // ورود موفق
        $token = new UserToken();
        $token->setUser($user);
        $token->setToken($this->RandomString(254));
        $token->setTokenID($this->RandomString(254));
        $entityManager->persist($token);
        $entityManager->flush();

        $captchaService->resetAttempts($attemptKey);
        return $this->json($extractor->operationSuccess([
            'user' => $user->getUserIdentifier(),
            'token' => $token->getToken(),
            'tokenID' => $token->getTokenID(),
            'active' => $user->isActive(),
            'captcha_required' => false
        ]));
    }


    #[Route('/api/user/has/role/{id}', name: 'api_user_has_role')]
    public function api_user_has_role(Extractor $extractor, #[CurrentUser] ?User $user, EntityManagerInterface $entityManager, $id): Response
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
            $temp['mobile'] = $perm->getUser()->getMobile();
            $temp['owner'] = $perm->isOwner();
            $out[] = $temp;
        }
        return $this->json($out);
    }

    #[Route('/api/user/current/info', name: 'api_user_current_info')]
    public function api_user_current_info(#[CurrentUser] ?User $user, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        $result = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullname' => $user->getFullName(),
            'businessCount' => count($user->getBusinesses()),
            'hash_email' => $provider->gravatarHash($user->getEmail()),
            'mobile' => $user->getMobile(),
        ];
        if (!$user->getInvateCode()) {
            $user->setInvateCode($this->RandomString(7));
            $entityManager->persist($user);
            $entityManager->flush();
        }
        $result['invateCode'] = $user->getInvateCode();
        return $this->json($result);
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
    public function api_user_change_password(Extractor $extractor, #[CurrentUser] ?User $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Request $request): Response
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

    #[Route('/api/user/register', name: 'api_user_register', methods: ['POST'])]
    public function api_user_register(
        Extractor $extractor,
        registryMGR $registryMGR,
        SMS $SMS,
        MailerInterface $mailer,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        CaptchaService $captchaService
    ): Response {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        // چک کردن کپچا
        $captchaAnswer = $params['captcha_answer'] ?? '';
        if (!$captchaService->validateCaptcha($captchaAnswer)) {
            return $this->json($extractor->operationFail(
                empty($captchaAnswer) ? 'کپچا لازم است' : 'کپچا اشتباه است',
                400,
                ['captcha_required' => true]
            ));
        }

        // بررسی پارامترهای ورودی
        if (
            !array_key_exists('name', $params) || !array_key_exists('email', $params) ||
            !array_key_exists('mobile', $params) || !array_key_exists('password', $params)
        ) {
            return $this->json($extractor->operationFail(
                'تمام موارد لازم را وارد کنید.',
                400,
                ['captcha_required' => true]
            ));
        }

        // بررسی تکراری بودن ایمیل یا موبایل
        if ($entityManager->getRepository(User::class)->findOneBy(['email' => trim($params['email'])])) {
            return $this->json($extractor->operationFail(
                'پست الکترونیکی وارد شده قبلا ثبت شده است',
                400,
                ['captcha_required' => true]
            ));
        }
        if ($entityManager->getRepository(User::class)->findOneBy(['mobile' => trim($params['mobile'])])) {
            return $this->json($extractor->operationFail(
                'شماره تلفن وارد شده قبلا ثبت شده است',
                400,
                ['captcha_required' => true]
            ));
        }

        // ایجاد کاربر جدید
        $user = new User();
        $user->setEmail($params['email']);
        $user->setFullName($params['name']);
        $user->setMobile($params['mobile']);
        $user->setDateRegister(time());
        $user->setPassword($userPasswordHasher->hashPassword($user, $params['password']));

        // بررسی اینکه آیا این اولین کاربر است
        $isFirstUser = $entityManager->getRepository(User::class)->count([]) === 0;
        if ($isFirstUser) {
            $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']); // اعطای نقش ادمین
            $user->setActive(true); // فعال کردن کاربر
        } else {
            $user->setRoles(['ROLE_USER']);
            $user->setActive(false); // به طور پیش‌فرض غیرفعال
            $user->setVerifyCode($this->RandomString(6, true));
            $user->setVerifyCodeTime(time() + 300);
        }

        // چک کردن کد معرف
        $inviteCode = $params['inviteCode'] ?? '0';
        if ($inviteCode !== '0') {
            $inviter = $entityManager->getRepository(User::class)->findOneBy(['invateCode' => $inviteCode]);
            if ($inviter) {
                $user->setInvitedBy($inviter);
            }
        }

        $entityManager->persist($user);
        $entityManager->flush();

        // بررسی کلید verifyMobileViaSms
        $verifyMobileViaSms = filter_var($registryMGR->get('system', 'verifyMobileViaSms'), FILTER_VALIDATE_BOOLEAN);

        if ($isFirstUser) {
            // اولین کاربر نیازی به تأیید ندارد
            return $this->json($extractor->operationSuccess([
                'id' => $user->getId(),
                'message' => 'حساب شما با موفقیت ایجاد و فعال شد. لطفاً وارد شوید.',
                'redirect' => '/user/login'
            ]));
        } elseif ($verifyMobileViaSms) {
            // ارسال کد تأیید از طریق پیامک و ایمیل
            $SMS->send(
                [$user->getVerifyCode()],
                $registryMGR->get('sms', 'f2a'),
                $user->getMobile()
            );
            try {
                $email = (new Email())
                    ->to($user->getEmail())
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject('تایید ایمیل')
                    ->html(
                        $this->renderView('user/email/confrim-register.html.twig', [
                            'code' => $user->getVerifyCode()
                        ])
                    );
                $mailer->send($email);
            } catch (Exception $exception) {
                // خطای ارسال ایمیل را می‌توان لاگ کرد
            }
            return $this->json($extractor->operationSuccess([
                'id' => $user->getId(),
                'message' => 'لطفاً کد تأیید ارسال‌شده را وارد کنید.'
            ]));
        } else {
            // اگر تأیید پیامک غیرفعال باشد، حساب فعال می‌شود
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json($extractor->operationSuccess([
                'id' => $user->getId(),
                'message' => 'حسابت فعال شده است. لطفاً وارد شوید.',
                'redirect' => '/user/login'
            ]));
        }
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
                ->subject('تایید ایمیل  ')
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
                ['id' => $user->getId()],
                'حساب کاربری شما فعال شد.هماکنون می‌توانید با اطلاعات ثبت نام خود به حساب کاربری وارد شوید.'
            ));
        }
        return $this->json($extractor->operationFail('کد ارسالی اشتباه است.'));
    }

    #[Route('/api/user/forget/password/send-code', name: 'api_user_forget_password_send_code', methods: ['POST'])]
    public function api_user_forget_password_send_code(
        Extractor $extractor,
        registryMGR $registryMGR,
        #[CurrentUser] ?User $user,
        SMS $SMS,
        MailerInterface $mailer,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        Request $request,
        CaptchaService $captchaService
    ): Response {
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }

        // همیشه کپچا رو چک می‌کنیم
        $captchaAnswer = $params['captcha_answer'] ?? '';
        if (!$captchaService->validateCaptcha($captchaAnswer)) {
            return $this->json($extractor->operationFail(
                empty($captchaAnswer) ? 'کپچا لازم است' : 'کپچا اشتباه است',
                400,
                ['captcha_required' => true]
            ));
        }

        // ادامه منطق بازیابی
        if (!array_key_exists('mobile', $params)) {
            return $this->json($extractor->paramsNotSend());
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['mobile' => $params['mobile']]);
        if (!$user) {
            return $this->json($extractor->operationFail(
                'کاربری با شماره تلفن وارد شده یافت نشد.',
                404,
                ['captcha_required' => true]
            ));
        }
        if ($user->getVerifyCodeTime() > time()) {
            return $this->json($extractor->operationFail(
                'کد بازیابی رمز عبور اخیرا ارسال شده است. لطفا چند دقیقه دیگر مجددا درخواست خود را ارسال نمایید.',
                600,
                ['captcha_required' => true]
            ));
        }

        $user->setVerifyCode($this->RandomString(6, true));
        $user->setVerifyCodeTime(time() + 300);
        $entityManager->persist($user);
        $entityManager->flush();

        // ارسال SMS و ایمیل
        $SMS->send(
            [$user->getVerifyCode()],
            $registryMGR->get('sms', 'recPassword'),
            $user->getMobile()
        );
        $email = (new Email())
            ->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject('فراموشی کلمه عبور')
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
                ->subject('تایید ایمیل  ')
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

    #[Route('/api/user/check-register-status', name: 'app_check_register_status', methods: ['GET'])]
    public function checkRegisterStatus(registryMGR $registryMGR): JsonResponse
    {
        $rootSystem = 'system_settings';
        $canRegister = filter_var($registryMGR->get($rootSystem, 'can_register'), FILTER_VALIDATE_BOOLEAN);

        return new JsonResponse([
            'result' => 1,
            'canRegister' => $canRegister
        ]);
    }

    #[Route('/api/admin/user/change-password/{userId}', name: 'change_user_password', methods: ['POST'])]
    public function changePassword(
        int $userId,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        registryMGR $registryMGR,
        MailerInterface $mailer,
        SMS $SMS
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? null;
        $notifyUser = $data['notifyUser'] ?? false;

        if (!$password) {
            return new JsonResponse(['error' => 'Password is required'], 400);
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $entityManager->persist($user);
        $entityManager->flush();

        if ($notifyUser) {
            $SMS->send(
                [$password],
                $registryMGR->get('sms', 'changePassword'),
                $user->getMobile()
            );
            $email = (new Email())
                ->to($user->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('تغییر کلمه عبور')
                ->html(
                    $this->renderView('user/email/reset-password.html.twig', [
                        'code' => $password
                    ])
                );
            $mailer->send($email);
        }

        return new JsonResponse(['message' => 'Password changed successfully']);
    }
}
