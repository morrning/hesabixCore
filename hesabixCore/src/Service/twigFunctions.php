<?php


namespace App\Service;


use App\Entity\ChangeReport;
use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class twigFunctions
{
    private EntityManagerInterface $em;

    protected Request $request;
    protected RequestStack $requestStack;

    function __construct(
        EntityManagerInterface  $entityManager,
        RequestStack $request
    )
    {
        $this->request = $request->getCurrentRequest();
        $this->em = $entityManager;
    }


    public function md5($val){
        return md5($val);
    }
    public function gravatarHash($email){
       return md5( strtolower( trim( $email) ) );
    }

    public function dayToNow($time){

        $time =  $time - time(); // to get the time since that moment
        $tokens = array (
            86400 => 'روز',
            2592000 => 'ماه'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            return floor($time / $unit) . $text;
        }
        return 'چند ساعت ';
    }

    public function pastTime($time){

        $time = time() - $time; // to get the time since that moment
        $tokens = array (
            31536000 => 'سال',
            2592000 => 'ماه',
            604800 => 'هفته',
            86400 => 'روز',
            3600 => 'ساعت',
            60 => 'دقیقه',
            1 => 'ثانیه'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text . ' قبل ';
        }
        return 'چند ثانیه قبل';
    }

    public function pastHash($hash){
        $tokens = array (
             1024 *1024 *1024 *1024 *1024  => 'اگزاهش',
             1024 *1024 *1024 *1024  => 'پتاهش',
             1024 *1024 *1024  => 'تراهش',
             1024 *1024  => 'گیگاهش',
             1024 => 'مگاهش',
             1 => 'کیلوهش',
        );
        foreach ($tokens as $unit => $text) {
            if ($hash < $unit) continue;
            $numberOfUnits = floor($hash / $unit);
            return $numberOfUnits.' '.$text;
        }
    }

    public function getHesabixLastVersionNumber():string{
        $last = $this->em->getRepository(ChangeReport::class)->findOneBy([],['id'=>'DESC']);
        if($last)
            return $last->getVersion();
        return '0.0.1';
    }

    public function systemSettings(){
        return $this->em->getRepository(Settings::class)->findAll()[0];
    }

    public function getCurrentUrl(){
        return $this->request->getUri();
    }

}