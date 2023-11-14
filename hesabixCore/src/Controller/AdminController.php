<?php

namespace App\Controller;

use App\Entity\Business;
use App\Entity\ChangeReport;
use App\Entity\User;
use App\Service\Jdate;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AdminController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/api/admin/sync/database', name: 'app_admin_sync_database')]
    public function app_admin_sync_database(KernelInterface $kernel): JsonResponse
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:schema:update',
            // (optional) define the value of command arguments
            '--force' => true,
            '--complete' => true
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
        return $this->json([
            'message' => $content,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/admin/has/role/{role}', name: 'app_admin_has_role')]
    public function app_admin_has_role($role): JsonResponse
    {
        if(!is_bool(array_search($role,$this->getUser()->getRoles())))
        {
            return $this->json([
                'result' => true,
            ]);
        }
        return $this->json([
            'result' => false,
        ]);
    }

    #[Route('/api/admin/users/list', name: 'admin_users_list')]
    public function admin_users_list(Jdate $jdate,#[CurrentUser] ?User $user,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,Request $request): Response
    {
        $users = $entityManager->getRepository(User::class)->findBy([],['id'=>'DESC']);
        $resp = [];
        foreach ($users as $user) {
            $temp =[];
            $temp['id'] = $user->getId();
            $temp['email'] = $user->getEmail();
            $temp['mobile'] = $user->getMobile();
            $temp['fullname'] = $user->getFullName();
            $temp['status'] = $user->isActive();
            $temp['dateRegister'] = $jdate->jdate('Y/n/d',$user->getDateRegister());
            $temp['bidCount'] = count($entityManager->getRepository(Business::class)->findBy(['owner'=>$user]));
            $resp[] = $temp;
        }
        return $this->json($resp);
    }

    #[Route('/api/admin/reportchange/lists', name: 'app_admin_reportchange_list')]
    public function app_admin_reportchange_list(Jdate $jdate,Provider $provider,EntityManagerInterface $entityManager): JsonResponse
    {
        $rows = $entityManager->getRepository(ChangeReport::class)->findBy([],['id'=>'DESC']);
        foreach ($rows as $row){
            $row->setDateSubmit($jdate->jdate('Y/n/d',$row->getDateSubmit()));
        }
        return $this->json($provider->ArrayEntity2ArrayJustIncludes($rows,['getDateSubmit','getVersion','getId']));
    }

    #[Route('/api/admin/reportchange/delete/{id}', name: 'app_admin_reportchange_delete')]
    public function app_admin_reportchange_delete(string $id,EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $entityManager->getRepository(ChangeReport::class)->find($id);
        if($item){
            $entityManager->remove($item);
            $entityManager->flush();
        }
        return $this->json(['result'=>1]);
    }

    #[Route('/api/admin/reportchange/get/{id}', name: 'app_admin_reportchange_get')]
    public function app_admin_reportchange_get(string $id,EntityManagerInterface $entityManager): JsonResponse
    {
        $item = $entityManager->getRepository(ChangeReport::class)->find($id);
        if(!$item)
            throw $this->createNotFoundException();
        return $this->json($item);
    }

    #[Route('/api/admin/reportchange/mod/{id}', name: 'app_admin_reportchange_mod')]
    public function app_admin_reportchange_mod(Request $request,EntityManagerInterface $entityManager, int $id = 0): JsonResponse
    {
        $item = new ChangeReport();
        $item->setDateSubmit(time());

        if($id != 0){
            $item = $entityManager->getRepository(ChangeReport::class)->find($id);
            if(!$item)
                throw $this->createNotFoundException();
            else
                $item->setDateSubmit(time());

        }
        $params = [];
        if ($content = $request->getContent()) {
            $params = json_decode($content, true);
        }
        if(array_key_exists('version',$params) && array_key_exists('body',$params)){
            $item->setBody($params['body']);
            $item->setVersion($params['version']);
        }
        else
            throw $this->createNotFoundException();
        $entityManager->persist($item);
        $entityManager->flush();
        return $this->json(['result'=>1]);
    }
}
