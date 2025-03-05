<?php
// src/Command/ReleaseUpdateLockCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ReleaseUpdateLockCommand extends Command
{
    protected static $defaultName = 'app:release-update-lock';
    private LockFactory $lockFactory;

    public function __construct(LockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('software-update');
        $lock->release();
        $output->writeln('Update lock released successfully.');
        return Command::SUCCESS;
    }
}