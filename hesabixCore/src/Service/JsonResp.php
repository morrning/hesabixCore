<?php
namespace App\Service;
use App\Entity\Business;
use App\Entity\HesabdariDoc;
use App\Entity\Settings;
use App\Entity\User;
use Melipayamak\MelipayamakApi;
use Doctrine\ORM\EntityManagerInterface;

class JsonResp
{
    // this function get Hesabdari Document in entity format and return back in array format
    public static function SerializeHesabdariDoc(HesabdariDoc $hesabdariDoc) : array {
        return [
            'id'    => $hesabdariDoc->getId(),
            'dateSubmit' => $hesabdariDoc->getDateSubmit(),
            'date' => $hesabdariDoc->getDate(),
            'type' => $hesabdariDoc->getType(),
            'code' => $hesabdariDoc->getCode(),
            'des' => $hesabdariDoc->getDes(),
            'amount'=>$hesabdariDoc->getAmount(),
            'mdate' =>$hesabdariDoc->getMdate(),
            'plugin'=>$hesabdariDoc->getPlugin(),
            'refData'=>$hesabdariDoc->getRefData(),
            'shortLink'=>$hesabdariDoc->getShortlink(),
            'status' => $hesabdariDoc->getStatus(),
            'tempStatus' => $hesabdariDoc->getTempStatus(),
        ];
    }
}