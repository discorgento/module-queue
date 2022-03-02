<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Contracts\JobInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Cron
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var MessageCollectionFactory */
    protected $messageCollectionFactory;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    public function __construct(
        LoggerInterface $logger,
        MessageCollectionFactory $messageCollectionFactory,
        MessageRepository $messageRepository,
        ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
    }

    public function execute()
    {
        $messages = $this->messageCollectionFactory->create();
        /** @var Message */
        foreach ($messages as $message) {
            try {
                /** @var JobInterface */
                $job = $this->objectManager->create($message->getJobClass());
                $job->execute($message->getTarget(), $message->getAdditionalData());
            } catch (\Throwable $th) {
                $errorMessage = "Job {$message->getJobClass()} failed: '{$th->getMessage()}'";
                $this->logger->error($errorMessage, [
                    'target' => $message->getTarget(),
                    'additional_data' => $message->getAdditionalData(),
                ]);
            }

            $this->messageRepository->delete($message);
        }

        return $this;
    }
}
