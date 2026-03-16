<?php

namespace App\Command;

use App\Service\BackSync\BackSyncInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class BackSyncCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'backsync';
    protected static $defaultDescription = 'Start back sync';

    private BackSyncInterface $syncService;
    private LoggerInterface $logger;

    public function __construct(
        BackSyncInterface $syncService,
        LoggerInterface $logger
    ) {
        $this->syncService = $syncService;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock('back_sync_run_')) {
            $output->writeln('Command is already running');
            $this->logger->warning('Command is already running');

            return Command::SUCCESS;
        }

        $this->syncService->run();

        $this->release();

        return Command::SUCCESS;
    }
}
