<?php

namespace App\Service;

use App\Entity\APIToken;
use App\Entity\Business;
use App\Entity\Money;
use App\Entity\Permission;
use App\Entity\UserToken;
use App\Entity\Year;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Access
{
    private $em;
    protected Request $request;
    protected RequestStack $requestStack;
    protected UserInterface | null $user;

    protected Business | string $bid;

    function __construct(
        EntityManagerInterface  $entityManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $request
    )
    {
        $this->em = $entityManager;
        $this->request = $request->getCurrentRequest();
        if($tokenStorage->getToken()){
            $this->user = $tokenStorage->getToken()->getUser();
        }
        else{
            $this->user = null;
        }
    }

    public function hasRole($roll): bool|array
    {
        if(is_null($this->user))
            return false;
        if ($this->request->headers->get('activeBid')) {
            $bid = $this->em->getRepository(Business::class)->find($this->request->headers->get('activeBid'));
            if (is_null($bid)) {
                return false;
            }
        }
        elseif($this->request->headers->get('api-key')){
            $token = $this->em->getRepository(APIToken::class)->findOneBy([
                'token'=>$this->request->headers->get('api-key')
            ]);
            if(!$token) { return false; }
            $bid = $token->getBid();
        }
        if ($this->request->headers->get('activeYear')) {
            $year = $this->em->getRepository(Year::class)->findOneBy([
                'id' => $this->request->headers->get('activeYear'),
                'bid'=>$bid
            ]);
            if (!$year) { return false; }
        }
        elseif($this->request->headers->get('api-key')){
            $year = $this->em->getRepository(Year::class)->findOneBy([
                'head' => true,
                'bid'=>$bid
            ]);
        }
        else {
            $year = $this->em->getRepository(Year::class)->findOneBy([
                'head' => true,
                'bid'=>$bid
            ]);
            if (!$year) { return false; }
        }
        
        if ($this->request->headers->get('activeMoney')) {
            $money = $this->em->getRepository(Money::class)->findOneBy([
                'name' => $this->request->headers->get('activeMoney'),
            ]);
            if (!$money) { return false; }
        }
        else{
            $money = $bid->getMoney();
            if (!$money) { return false; }
        }

        $accessArray = [
            'bid'=>$bid,
            'user'=>$this->user,
            'year'=>$year,
            'access'=>true,
            'money'=>$money
        ];
        
        if($bid->getOwner()->getEmail() === $this->user->getUserIdentifier()){
            //user is owner
            return $accessArray;
        }
        elseif ($this->user && $roll == 'join' && count($this->em->getRepository(Permission::class)->findBy(['user'=>$this->user,'bid'=>$bid]))){
            return $accessArray;
        }
        $methodName = 'is' . ucfirst($roll);
        $permission = $this->em->getRepository(Permission::class)->findOneBy([
           'bid'=>$bid,
           'user'=>$this->user
        ]);
        if($permission){
            if($permission->{$methodName}())
                return $accessArray;
        }
        return false;
    }
}