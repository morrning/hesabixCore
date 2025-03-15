<?php

namespace App\Command;

use App\Repository\SupportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'hesabix:autoupdate-tickets',
    description: 'Updates support tickets that have had no response for 48 hours to "خاتمه یافته".'
)]
class UpdateSupportTicketsCommand extends Command
{
    private SupportRepository $supportRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SupportRepository $supportRepository, EntityManagerInterface $entityManager)
    {
        $this->supportRepository = $supportRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currentTime = time();
        $fortyEightHours = 48 * 3600;

        $qb = $this->supportRepository->createQueryBuilder('s')
            ->select('s.id, s.dateSubmit as mainDate, MAX(r.dateSubmit) as lastResponseDate')
            ->leftJoin('App\Entity\Support', 'r', 'WITH', 'r.main = s.id')
            ->where('s.main = 0')
            ->andWhere('s.state != :closedState')
            ->setParameter('closedState', 'خاتمه یافته')
            ->groupBy('s.id, s.dateSubmit');

        $results = $qb->getQuery()->getResult();

        $updatedTickets = 0;

        foreach ($results as $result) {
            $lastActivityTime = $result['lastResponseDate'] ? (int)$result['lastResponseDate'] : (int)$result['mainDate'];
            $timeDifference = $currentTime - $lastActivityTime;

            if ($timeDifference > $fortyEightHours) {
                $this->entityManager->createQueryBuilder()
                    ->update('App\Entity\Support', 's')
                    ->set('s.state', ':newState')
                    ->where('s.id = :id')
                    ->setParameter('newState', 'خاتمه یافته')
                    ->setParameter('id', $result['id'])
                    ->getQuery()
                    ->execute();

                $updatedTickets++;
                $output->writeln(sprintf('Ticket #%d marked as "خاتمه یافته".', $result['id']));
            }
        }

        $output->writeln(sprintf('Total %d support tickets updated successfully.', $updatedTickets));

        return Command::SUCCESS;
    }
}