<?php
// src/Service/CaptchaService.php
namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CaptchaService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function createCaptchaImage(): Response
    {
        $session = $this->requestStack->getSession();

        // تولید کپچا که با صفر شروع نشه
        $firstDigit = rand(1, 9); // رقم اول بین 1 تا 9
        $remainingDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); // ۵ رقم بعدی
        $captchaCode = $firstDigit . $remainingDigits; // ترکیب به صورت ۶ رقمی

        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $captchaCodePersian = str_replace($englishNumbers, $persianNumbers, $captchaCode);

        $session->set('captcha_code', $captchaCode);

        $image = imagecreatetruecolor(200, 80);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $backgroundColor);

        $fontPath = __DIR__ . '/../Fonts/Vazirmatn-Black.ttf';
        imagettftext($image, 20, rand(-10, 10), 40, 50, $textColor, $fontPath, $captchaCodePersian);

        $lineColor1 = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        $lineColor2 = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imageline($image, rand(0, 50), rand(0, 80), rand(150, 200), rand(0, 80), $lineColor1);
        imageline($image, rand(0, 50), rand(0, 80), rand(150, 200), rand(0, 80), $lineColor2);

        $circleColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        imageellipse($image, rand(20, 180), rand(10, 70), rand(20, 50), rand(20, 50), $circleColor);

        $squareColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
        $squareX = rand(20, 160);
        $squareY = rand(10, 40);
        $squareSize = rand(20, 40);
        imagerectangle($image, $squareX, $squareY, $squareX + $squareSize, $squareY + $squareSize, $squareColor);

        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);

        $wavedImage = imagecreatetruecolor(200, 80);
        imagefill($wavedImage, 0, 0, $backgroundColor);
        for ($x = 0; $x < 200; $x++) {
            for ($y = 0; $y < 80; $y++) {
                $newX = $x + sin($y / 10) * 5;
                $newY = $y + cos($x / 10) * 5;
                if ($newX >= 0 && $newX < 200 && $newY >= 0 && $newY < 80) {
                    $color = imagecolorat($image, $x, $y);
                    imagesetpixel($wavedImage, (int)$newX, (int)$newY, $color);
                }
            }
        }

        ob_start();
        imagepng($wavedImage);
        $imageData = ob_get_clean();
        imagedestroy($image);
        imagedestroy($wavedImage);

        return new Response($imageData, 200, ['Content-Type' => 'image/png']);
    }

    public function isCaptchaRequired(string $attemptKey): bool
    {
        $session = $this->requestStack->getSession();
        $attempts = $session->get($attemptKey, 0);
        $lastAttemptTime = $session->get($attemptKey . '_time', 0);
        $currentTime = time();
        $halfHour = 30 * 60;

        if (($currentTime - $lastAttemptTime) > $halfHour) {
            $session->set($attemptKey, 0);
            $session->set($attemptKey . '_time', $currentTime);
            return false;
        }

        return $attempts >= 3;
    }

    public function validateCaptcha(string $captchaAnswer): bool
    {
        $session = $this->requestStack->getSession();
        if (empty($captchaAnswer)) {
            return false;
        }

        $storedCode = $session->get('captcha_code');
        if ($captchaAnswer === $storedCode) {
            $session->remove('captcha_code');
            return true;
        }

        return false;
    }

    public function incrementAttempts(string $attemptKey): void
    {
        $session = $this->requestStack->getSession();
        $attempts = $session->get($attemptKey, 0) + 1;
        $session->set($attemptKey, $attempts);
        $session->set($attemptKey . '_time', time());
    }

    public function resetAttempts(string $attemptKey): void
    {
        $session = $this->requestStack->getSession();
        $session->set($attemptKey, 0);
        $session->set($attemptKey . '_time', time());
    }
}