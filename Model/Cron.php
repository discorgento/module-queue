<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Core\Helper\Data as CoreHelper;
use Discorgento\Queue\Contracts\JobInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class Cron
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

    public function execute()
    {
        $messages = $this->messageCollectionFactory->create();
        /** @var Message */
        foreach ($messages as $message) {
            try {
                /** @var JobInterface */
                $job = $this->objectManager->create($message->getJobClass());
                $job->execute($message->getTarget(), $message->getAdditionalData());

                // if in developer mode only clean jobs on success
                if ($this->coreHelper->isDeveloperMode()) {
                    $this->messageRepository->delete($message);
                }
            } catch (\Throwable $th) {
                $errorMessage = "Job {$message->getJobClass()} failed: '{$th->getMessage()}'";
                $this->logger->error($errorMessage, [
                    'target' => $message->getTarget(),
                    'additional_data' => $message->getAdditionalData(),
                ]);
            }

            // keep failed jobs when in developer mode
            if ($this->coreHelper->isProductionMode()) {
                $this->messageRepository->delete($message);
            }
        }

        return $this;
    }
}
