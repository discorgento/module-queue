<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Console\Command;

use Discorgento\Queue\Helper\Executor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Execute extends Command
{
    /** @var Executor */
    protected $executorHelper;

    /** @var ProgressBarFactory */
    protected $progressBarFactory;

    public function __construct(
        Executor $executorHelper,
        ProgressBarFactory $progressBarFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->executorHelper = $executorHelper;
        $this->progressBarFactory = $progressBarFactory;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this->setName('discorgento:queue:execute');
        $this->setDescription('Execute the jobs previously queued.');

        parent::configure();
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messages = $this->executorHelper->getPendingMessages();
        $totalMessages = $messages->count();

        if ($totalMessages < 1) {
            return $output->writeln("<error>There's no pending jobs.</error>");
        }

        $output->writeln('<info>Executing the jobs in queue..</info>');

        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => $totalMessages,
        ]);

        $failedJobsCount = 0;

        foreach ($messages as $message) {
            $hasSucceeded = $this->executorHelper->execute($message);
            if (!$hasSucceeded) {
                $failedJobsCount++;
            }

            $progressBar->advance();
        }

        $successedJobsCount = $totalMessages - $failedJobsCount;
        $output->writeln('');

        $resultMessage = "<info>{$successedJobsCount} jobs executed successfully</info>";
        if ($failedJobsCount > 0) {
            $resultMessage .= ", <error>{$failedJobsCount} jobs failed</error>, check var/log/discorgento_queue.log for more info";
        }
        $resultMessage .= '.';

        $output->writeln($resultMessage);
    }
}
