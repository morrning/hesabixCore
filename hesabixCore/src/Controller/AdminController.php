<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Provider;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

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

    /**
     * @throws Exception
     */
    #[Route('/api/admin/users/list', name: 'app_admin_users_list')]
    public function app_admin_users_list(Provider $provider,EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        return $this->json($provider->ArrayEntity2ArrayJustIncludes($users,[
            
        ]));
    }
}
