<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Console\Command;

use Discorgento\Core\Helper\Data as CoreHelper;
use Discorgento\Queue\Command\ClearCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestionFactory;

class Clear extends Command
{
    /** @var ClearCommand */
    private $clearCommand;

    /** @var ConfirmationQuestionFactory */
    private $confirmationQuestionFactory;

    /** @var CoreHelper */
    private $coreHelper;

    /** @var ProgressBarFactory */
    private $progressBarFactory;

    // phpcs:ignore
    public function __construct(
        ClearCommand $clearCommand,
        ConfirmationQuestionFactory $confirmationQuestionFactory,
        CoreHelper $coreHelper,
        ProgressBarFactory $progressBarFactory,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->clearCommand = $clearCommand;
        $this->confirmationQuestionFactory = $confirmationQuestionFactory;
        $this->coreHelper = $coreHelper;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('discorgento:queue:clear');
        $this->setDescription('Clear the jobs currently queued.');

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $total = $this->clearCommand->getTotal();
        if ($total < 1) {
            $output->writeln("<error>There's no pending jobs.</error>");

            return 0;
        }

        if ($this->coreHelper->isProductionMode()) {
            $questionHelper = $this->getHelper('question');
            $question = $this->confirmationQuestionFactory->create([
                'question' => 'Are you sure? <error>This cannot be undone!</error> (y/n) ',
                'default' => false,
            ]);

            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('Cleanup canceled.');

                return 0;
            }
        }

        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => $total,
        ]);

        foreach ($this->clearCommand->iterator() as $clearedMessage) {
            $progressBar->advance();
        }

        $output->writeln(PHP_EOL . '<info>Done.</info>');

        return 0;
    }
}
