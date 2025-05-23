<?php


namespace App\Service;


use App\Entity\ChangeReport;
use App\Entity\Plugin;
use App\Entity\Settings;
use App\Entity\PrintOptions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class twigFunctions
{
    private EntityManagerInterface $em;

    protected $request;
    protected RequestStack $requestStack;
    protected registryMGR $registryMGR;
    function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $request,
        registryMGR $registryMGR
    ) {
        $this->request = $request->getCurrentRequest();
        $this->em = $entityManager;
        $this->registryMGR = $registryMGR;
    }

    //get static data from registry
    public function getStaticData($root,$key)
    {
        return $this->registryMGR->get($root, $key);
    }

    public function md5($val)
    {
        return md5($val);
    }
    public function gravatarHash($email)
    {
        return md5(strtolower(trim($email)));
    }

    public function dayToNow($time)
    {

        $time = $time - time(); // to get the time since that moment
        $tokens = array(
            86400 => 'روز',
            2592000 => 'ماه'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit)
                continue;
            return floor($time / $unit) . $text;
        }
        return 'چند ساعت ';
    }

    public function pastTime($time)
    {

        $time = time() - $time; // to get the time since that moment
        $tokens = array(
            31536000 => 'سال',
            2592000 => 'ماه',
            604800 => 'هفته',
            86400 => 'روز',
            3600 => 'ساعت',
            60 => 'دقیقه',
            1 => 'ثانیه'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit)
                continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . ' قبل ';
        }
        return 'چند ثانیه قبل';
    }

    public function pastHash($hash)
    {
        $tokens = array(
            1024 * 1024 * 1024 * 1024 * 1024 => 'اگزاهش',
            1024 * 1024 * 1024 * 1024 => 'پتاهش',
            1024 * 1024 * 1024 => 'تراهش',
            1024 * 1024 => 'گیگاهش',
            1024 => 'مگاهش',
            1 => 'کیلوهش',
        );
        foreach ($tokens as $unit => $text) {
            if ($hash < $unit)
                continue;
            $numberOfUnits = floor($hash / $unit);
            return $numberOfUnits . ' ' . $text;
        }
    }

    public function getHesabixLastVersionNumber(): string
    {
        $last = $this->em->getRepository(ChangeReport::class)->findOneBy([], ['id' => 'DESC']);
        if ($last)
            return $last->getVersion();
        return '0.0.1';
    }

    public function systemSettings()
    {
        return $this->em->getRepository(Settings::class)->findAll()[0];
    }

    public function getCurrentUrl()
    {
        return $this->request->getUri();
    }

    public function getFooterText(string $side, PluginService $pluginService, $bid): string
    {
        // اگر پلاگین accpro فعال نباشد، مقدار پیش‌فرض را برمی‌گرداند
        if (!$pluginService->isActive('accpro', $bid)) {
            return $side === 'left' ? $this->getStaticData('system', 'footerLeft') : $this->getStaticData('system', 'footerRight');
        }

        // دریافت تنظیمات چاپ
        $printOptions = $this->em->getRepository(PrintOptions::class)->findOneBy(['bid' => $bid]);
        
        if (!$printOptions) {
            return $side === 'left' ? $this->getStaticData('system', 'footerLeft') : $this->getStaticData('system', 'footerRight');
        }

        // دریافت متن پانویس بر اساس سمت
        $footerText = $side === 'left' ? $printOptions->getLeftFooter() : $printOptions->getRightFooter();
        
        // اگر متن null یا خالی باشد، مقدار پیش‌فرض را برمی‌گرداند
        if ($footerText === null || $footerText === '') {
            return $side === 'left' ? $this->getStaticData('system', 'footerLeft') : $this->getStaticData('system', 'footerRight');
        }

        return $footerText;
    }
}