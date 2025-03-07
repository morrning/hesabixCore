<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application as SymfonyApplication;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;

class DoctrineExceptionListener
{
    private $kernel;
    private $logger;
    private $lockFactory;
    private $entityManager;

    public function __construct(
        KernelInterface $kernel,
        LoggerInterface $logger,
        LockFactory $lockFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->lockFactory = $lockFactory;
        $this->entityManager = $entityManager;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof InvalidFieldNameException) {
            return;
        }

        $request = $event->getRequest();
        $attemptCount = $request->attributes->getInt('_schema_update_attempts', 0);

        if ($attemptCount >= 1) {
            $this->logger->warning('Schema update already attempted for this request, skipping.', [
                'attempt' => $attemptCount,
            ]);
            return;
        }

        $this->logger->error('Database schema error occurred: ' . $exception->getMessage(), [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString(),
        ]);

        $lock = $this->lockFactory->createLock('schema_update_lock', 30);

        if (!$lock->acquire()) {
            $this->logger->info('Waiting for schema update lock to be released.');
            $lock->acquire(true); // Blocking wait
            $response = $this->kernel->handle($request);
            $this->logger->info('Request handled after waiting for schema update.', [
                'status_code' => $response->getStatusCode(),
            ]);
            $event->setResponse($response);
            $lock->release();
            return;
        }

        try {
            $application = new SymfonyApplication($this->kernel);
            $application->setAutoExit(false);
            $command = new UpdateCommand(new SingleManagerProvider($this->entityManager));
            $application->add($command);

            $input = new ArrayInput([
                'command' => 'doctrine:schema:update',
                '--force' => true,
                '--complete' => true, // برای سازگاری با DBAL 4 در آینده
            ]);

            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);
            $outputContent = $output->fetch();

            $this->logger->info('Schema update executed.', [
                'attempt' => $attemptCount + 1,
                'exit_code' => $exitCode,
                'output' => $outputContent,
            ]);

            if ($exitCode !== 0) {
                $event->setResponse(new Response(
                    'Failed to update database schema: ' . $outputContent,
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
                return;
            }

            $request->attributes->set('_schema_update_attempts', $attemptCount + 1);
            $response = $this->kernel->handle($request);

            $this->logger->info('Request handled successfully after schema update.', [
                'attempt' => $attemptCount + 1,
                'status_code' => $response->getStatusCode(),
            ]);

            $event->setResponse($response);
        } catch (\Exception $e) {
            $this->logger->critical('Exception during schema update: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            $event->setResponse(new Response(
                'A critical error occurred while updating the schema: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            ));
        } finally {
            $lock->release();
            $this->logger->debug('Schema update lock released.');
        }
    }
}