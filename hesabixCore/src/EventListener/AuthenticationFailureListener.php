<?php
// src/EventListener/AuthenticationFailureListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\AuthenticationFailureEvent;
use App\Service\CaptchaService;

class AuthenticationFailureListener
{
    private CaptchaService $captchaService;
    private SessionInterface $session;

    public function __construct(CaptchaService $captchaService, SessionInterface $session)
    {
        $this->captchaService = $captchaService;
        $this->session = $session;
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $attemptKey = 'login_attempts';
        $this->captchaService->incrementAttempts($this->session, $attemptKey);
    }
}