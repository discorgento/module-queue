<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Console\Command;

use Discorgento\Queue\Api\MessageManagementInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Retry extends Command
{
    /** @var MessageManagementInterface */
    private $messageManagement;

    /** @var ProgressBarFactory */
    private $progressBarFactory;

    // phpcs:ignore
    public function __construct(
        MessageManagementInterface $messageManagement,
        ProgressBarFactory $progressBarFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->messageManagement = $messageManagement;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('discorgento:queue:retry');
        $this->setDescription('Retry failed jobs in queue.');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $failedJobs = $this->messageManagement->getToBeRetried();

        $totalMessages = count($failedJobs);
        if ($totalMessages < 1) {
            $output->writeln("There's no jobs waiting to be retried.");

            return self::SUCCESS;
        }

        $output->writeln('Retrying the failed jobs..');

        /** @var \Symfony\Component\Console\Helper\ProgressBar */
        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => $totalMessages,
        ]);

        foreach ($failedJobs as $message) {
            $this->messageManagement->process($message);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . '<info>Done.</info>');

        return self::SUCCESS;
    }
}
