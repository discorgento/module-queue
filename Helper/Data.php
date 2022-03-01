<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Helper;

use Discorgento\Queue\Model\MessageFactory;
use Discorgento\Queue\Model\MessageRepository;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;

class Data
{
    protected MessageCollectionFactory $messageCollectionFactory;
    protected MessageFactory $messageFactory;
    protected MessageRepository $messageRepository;

    public function __construct(
        MessageCollectionFactory $messageCollectionFactory,
        MessageFactory $messageFactory,
        MessageRepository $messageRepository
    ) {
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Append given job to the queue
     *
     * @param string $jobClass
     * @param int|string|null $target
     * @param array $additionalData
     */
    public function append($jobClass, $target = null, $additionalData = [])
    {
        $message = $this->messageFactory->create()->addData([
            'job_class' => $jobClass,
            'target' => $target,
        ])->setAdditionalData($additionalData);

        if (!$this->alreadyQueued($message)) {
            $this->messageRepository->save($message);
        }
    }

    /**
     * Check if given message is already queued
     *
     * @param Message $message
     * @return bool
     */
    public function alreadyQueued($message)
    {
        $encodedAdditionalData = json_encode($message->getAdditionalData());

        return $this->messageCollectionFactory->create()
            ->addFieldToFilter('job_class', $message->getJobClass())
            ->addFieldToFilter('target', $message->getTarget())
            ->addFieldToFilter('additional_data', $encodedAdditionalData)
            ->count() > 0;
    }
}
