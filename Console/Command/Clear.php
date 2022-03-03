<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Console\Command;

use Discorgento\Core\Helper\Data as CoreHelper;
use Discorgento\Queue\Model\MessageRepository;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessagesCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestionFactory;

class Clear extends Command
{
    /** @var ConfirmationQuestionFactory */
    protected $confirmationQuestionFactory;

    /** @var CoreHelper */
    protected $coreHelper;

    /** @var MessagesCollectionFactory */
    protected $messagesCollectionFactory;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var ProgressBarFactory */
    protected $progressBarFactory;

    public function __construct(
        ConfirmationQuestionFactory $confirmationQuestionFactory,
        CoreHelper $coreHelper,
        MessagesCollectionFactory $messagesCollectionFactory,
        MessageRepository $messageRepository,
        ProgressBarFactory $progressBarFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->confirmationQuestionFactory = $confirmationQuestionFactory;
        $this->coreHelper = $coreHelper;
        $this->messagesCollectionFactory = $messagesCollectionFactory;
        $this->messageRepository = $messageRepository;
        $this->progressBarFactory = $progressBarFactory;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this->setName('discorgento:queue:clear');
        $this->setDescription('Clear the jobs currently queued.');

        parent::configure();
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messages = $this->messagesCollectionFactory->create();
        $totalMessages = $messages->count();

        if ($totalMessages < 1) {
            return $output->writeln("<error>There's no pending jobs.</error>");
        }

        if ($this->coreHelper->isProductionMode()) {
            $questionHelper = $this->getHelper('question');
            $question = $this->confirmationQuestionFactory->create([
                'question' => 'Are you sure? <error>This cannot be undone!</error> (y/n) ',
                'default' => false,
            ]);

            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('Cleanup canceled.');

                return self::SUCCESS;
            }
        }

        $progressBar = $this->progressBarFactory->create([
            'output' => $output,
            'max' => $totalMessages,
        ]);

        foreach ($messages as $message) {
            $this->messageRepository->delete($message);
            $progressBar->advance();
        }

        $output->writeln(PHP_EOL . '<info>Done.</info>');
    }
}
