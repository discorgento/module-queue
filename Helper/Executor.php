<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Helper;

use Discorgento\Core\Helper\Data as CoreHelper;
use Discorgento\Queue\Api\JobInterface;
use Discorgento\Queue\Model\Message;
use Discorgento\Queue\Model\MessageRepository;
use Discorgento\Queue\Model\ResourceModel\Message\Collection as MessagesCollection;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Executor
{
    /** @var CoreHelper */
    protected $coreHelper;

    /** @var LoggerInterface */
    protected $logger;

    /** @var MessageCollectionFactory */
    protected $messageCollectionFactory;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    public function __construct(
        CoreHelper $coreHelper,
        LoggerInterface $logger,
        MessageCollectionFactory $messageCollectionFactory,
        MessageRepository $messageRepository,
        ObjectManagerInterface $objectManager
    ) {
        $this->coreHelper = $coreHelper;
        $this->logger = $logger;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve pending jobs messages from database
     * @return MessagesCollection
     */
    public function getPendingMessages()
    {
        return $this->messageCollectionFactory->create();
    }

    /**
     * Safely execute given job message
     * @return bool if the job was successfully executed
     */
    public function execute(Message $message)
    {
        try {
            /** @var JobInterface */
            $job = $this->objectManager->create($message->getJobClass());
            $job->execute($message->getTarget(), $message->getAdditionalData());
            $this->messageRepository->delete($message);
        } catch (\Throwable $th) {
            $errorMessage = "Job {$message->getJobClass()} failed: '{$th->getMessage()}'";
            $this->logger->error($errorMessage, [
                'target' => $message->getTarget(),
                'additional_data' => $message->getAdditionalData(),
            ]);

            // keep failed jobs when in developer mode
            if ($this->coreHelper->isProductionMode()) {
                $this->messageRepository->delete($message);
            }

            return false;
        }

        return true;
    }
}
