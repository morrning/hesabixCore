<?php
// src/Controller/CaptchaController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CaptchaService;

class CaptchaController extends AbstractController
{
    #[Route('/api/captcha/image', name: 'api_captcha_image', methods: ['GET'])]
    public function generateCaptchaImage(CaptchaService $captchaService): Response
    {
        return $captchaService->createCaptchaImage();
    }
}