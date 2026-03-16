<?php

namespace App\Command;

use App\Service\Sync\PaymentSyncInterface;
use App\Service\Sync\SyncInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class SyncCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'sync';

    protected static $defaultDescription = 'Start sync';

    private SyncInterface $syncService;
    private PaymentSyncInterface $paymentSync;
    private LoggerInterface $logger;

    public function __construct(
        SyncInterface $syncService,
        PaymentSyncInterface $paymentSync,
        LoggerInterface $logger
    ) {
        $this->syncService = $syncService;
        $this->paymentSync = $paymentSync;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock('sync_run_')) {
            $output->writeln('Command is already running');
            $this->logger->warning('Command is already running');

            return Command::SUCCESS;
        }

        $this->syncService->run();
        $this->paymentSync->run();

        $this->release();

        return Command::SUCCESS;
    }
}
