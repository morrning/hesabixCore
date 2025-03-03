<?php
// src/Security/AuthenticationFailureHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use App\Service\CaptchaService;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private CaptchaService $captchaService;
    private RequestStack $requestStack;

    public function __construct(CaptchaService $captchaService, RequestStack $requestStack)
    {
        $this->captchaService = $captchaService;
        $this->requestStack = $requestStack;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $session = $this->requestStack->getSession();
        $attemptKey = 'login_attempts';
        $this->captchaService->incrementAttempts($attemptKey);

        $attempts = $session->get($attemptKey, 0);
        $sessionId = $session->getId(); // برای چک کردن اینکه سشن ثابت می‌مونه یا نه

        return new JsonResponse([
            'error' => 'احراز هویت نامعتبر می باشد.',
            'captcha_required' => $this->captchaService->isCaptchaRequired($attemptKey),
            'attempts' => $attempts,
            'last_attempt_time' => $session->get($attemptKey . '_time', 0),
            'session_id' => $sessionId // برای دیباگ
        ], 401);
    }
}