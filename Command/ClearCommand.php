<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Command;

use Discorgento\Queue\Model\MessageRepository;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessagesCollectionFactory;

class ClearCommand
{
    /** @var MessagesCollectionFactory */
    private $messagesCollectionFactory;

    /** @var MessageRepository */
    private $messageRepository;

    // phpcs:ignore
    public function __construct(
        MessagesCollectionFactory $messagesCollectionFactory,
        MessageRepository $messageRepository
    ) {
        $this->messagesCollectionFactory = $messagesCollectionFactory;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Clear the queue jobs
     *
     * @return int Amount of messages cleared
     */
    public function execute(): int
    {
        $messageCleanerIterator = $this->iterator();
        foreach ($messageCleanerIterator as $clearedMessage) {
            // iterator is cleaning the queue
        }

        return $messageCleanerIterator->getReturn();
    }

    /**
     * Clear the queue jobs
     *
     * @return \Generator
     */
    public function iterator(): \Generator
    {
        $messages = $this->messagesCollectionFactory->create();
        $total = $messages->getSize();

        foreach ($messages as $message) {
            $this->messageRepository->delete($message);
            yield $message;
        }

        return $total;
    }

    /**
     * Get the total amount of messages in the queue
     *
     * @return int
     */
    public function getTotal() : int
    {
        return $this->messagesCollectionFactory->create()->getSize();
    }
}
