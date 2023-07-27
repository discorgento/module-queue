<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Console\Command;

use Discorgento\Queue\Api\MessageManagementInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends Command
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
        $this->setName('discorgento:queue:execute');
        $this->setDescription('Execute pending jobs in queue.');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pendingMessages = $this->messageManagement->getPending();

        $totalMessages = $pendingMessages->getTotalCount();
        if ($totalMessages < 1) {
            $output->writeln("There's no pending jobs.");

            return 0;
        }

        $output->writeln('Executing the jobs in queue..');

        /** @var \Symfony\Component\Console\Helper\ProgressBar */
        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => $totalMessages,
        ]);

        /** @var \Discorgento\Queue\Api\Data\MessageInterface */
        foreach ($pendingMessages->getItems() as $message) {
            $this->messageManagement->process($message);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . '<info>Done.</info>');

        return 0;
    }
}
