<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\QueueManagementInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Psr\Log\LoggerInterface;

class QueueManagement implements QueueManagementInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var MessageCollectionFactory */
    private $messageCollectionFactory;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var MessageRepository */
    private $messageRepository;

    public function __construct(
        LoggerInterface $logger,
        MessageCollectionFactory $messageCollectionFactory,
        MessageFactory $messageFactory,
        MessageRepository $messageRepository
    ) {
        $this->logger = $logger;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
    }

    /** @inheritDoc */
    public function append(string $job, $target = null, array $additionalData = [])
    {
        try {
            $message = $this->messageFactory->create()
                ->addData(compact('job', 'target'))
                ->setAdditionalData($additionalData);

            if (!$this->alreadyQueued($message)) {
                $this->messageRepository->save($message);
            }
        } catch (\Throwable $th) {
            $this->logger->error(
                "Discorgento_Queue: failed to append the job, {$th->getMessage()}",
                compact('job', 'target')
            );
        }
    }

    /**
     * Check if given message is already queued
     *
     * @param Message $message
     *
     * @return bool
     */
    private function alreadyQueued(Message $message): bool
    {
        $encodedAdditionalData = json_encode($message->getAdditionalData());

        return $this->messageCollectionFactory->create()
            ->addFieldToFilter('job', $message->getJob())
            ->addFieldToFilter('target', $message->getTarget())
            ->addFieldToFilter('additional_data', $encodedAdditionalData)
            ->addFieldToFilter('status', Message::STATUS_PENDING)
            ->count() > 0;
    }
}
