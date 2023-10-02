<?php

namespace App\Service;
use App\Entity\Business;
use App\Entity\Permission;
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
        $bid = $this->em->getRepository(Business::class)->find($this->request->headers->get('activeBid'));
        if(is_null($bid))
            return false;
        $year = $this->em->getRepository(Year::class)->find($this->request->headers->get('activeYear'));
        if(is_null($year))
            return false;
        $accessArray = [
            'bid'=>$bid,
            'user'=>$this->user,
            'year'=>$year,
            'access'=>true
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