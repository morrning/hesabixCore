<?php
namespace App\Service;
use App\Entity\Settings;
use Melipayamak\MelipayamakApi;
use Doctrine\ORM\EntityManagerInterface;

class SMS
{
    private EntityManagerInterface $entityManager;
    private Settings $settings;
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->settings = $entityManager->getRepository(Settings::class)->findAll()[0];
    }

    public function send(array $params,$bodyID,$to){
        try{
            $username = $this->settings->getPayamakUsername();
            $password = $this->settings->getPayamakPassword();
            $api = new MelipayamakApi($username,$password);
            $sms = $api->sms('soap');
            $response = $sms->sendByBaseNumber($params,$to,$bodyID);
            $json = json_decode($response);
            echo $json->Value; //RecId or Error Number 
        }catch(\Exception $e){
            //echo $e->getMessage();
        }
    } 

}