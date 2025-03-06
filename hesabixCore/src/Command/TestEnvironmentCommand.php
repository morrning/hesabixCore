<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'hesabix:test-env',
    description: 'Tests the environment and runs composer install.'
)]
class TestEnvironmentCommand extends Command
{
    private string $appDir;
    private string $env;

    public function __construct(string $name = null)
    {
        $this->appDir = dirname(__DIR__, 2);
        // مستقیماً از .env.local.php بخوان
        $envConfig = file_exists($this->appDir . '/.env.local.php') ? require $this->appDir . '/.env.local.php' : [];
        $this->env = $envConfig['APP_ENV'] ?? getenv('APP_ENV') ?: 'prod';
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Detected environment: " . $this->env);

        $composerCommand = ['composer', 'install', '--optimize-autoloader'];
        if ($this->env !== 'dev') {
            $composerCommand[] = '--no-dev';
            $composerCommand[] = '--no-scripts';
        }

        $output->writeln("Running command: " . implode(' ', $composerCommand));

        $process = new Process($composerCommand, $this->appDir);
        $process->setTimeout(3600);
        if ($output->isVerbose()) {
            $process->mustRun(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        } else {
            $process->mustRun();
        }

        $output->writeln('<info>Composer install completed successfully.</info>');

        // تست اجرای cache:clear برای اطمینان
        $this->runProcess(['php', 'bin/console', 'cache:clear', "--env={$this->env}"], $output);

        return Command::SUCCESS;
    }

    private function runProcess(array $command, OutputInterface $output): void
    {
        $process = new Process($command, $this->appDir);
        $process->setTimeout(3600);
        if ($output->isVerbose()) {
            $process->mustRun(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });
        } else {
            $process->mustRun();
        }
        $output->writeln('<info>' . implode(' ', $command) . ' completed successfully.</info>');
    }
}