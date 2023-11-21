<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\QueueManagementInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class QueueManagement implements QueueManagementInterface
{
    private const ADDITIONAL_SETTINGS_KEY = '_discorgento_queue_settings_';

    /** @var LoggerInterface */
    private $logger;

    /** @var MessageCollectionFactory */
    private $messageCollectionFactory;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var MessageRepository */
    private $messageRepository;

    /** @var SerializerInterface */
    private $serializer;

    // phpcs:ignore
    public function __construct(
        LoggerInterface $logger,
        MessageCollectionFactory $messageCollectionFactory,
        MessageFactory $messageFactory,
        MessageRepository $messageRepository,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->messageFactory = $messageFactory;
        $this->messageRepository = $messageRepository;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function append(string $job, $target = null, array $additionalData = [])
    {
        try {
            /** @var Message */
            $message = $this->messageFactory->create();
            $message->addData(compact('job', 'target'));

            $this->parseAdditionalData($message, $additionalData);

            if (!$this->alreadyQueued($message)) {
                $this->messageRepository->save($message);
            }
        } catch (\Throwable $th) {
            $this->logger->error(
                "Discorgento_Queue: failed to append job: {$th->getMessage()}",
                ['exception' => $th, 'job' => $job, 'target' => $target, 'additionalData' => $additionalData]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function appendToGroup(
        string $group,
        string $job,
        $target = null,
        array $additionalData = []
    ) {
        $additionalData[self::ADDITIONAL_SETTINGS_KEY] = compact('group');

        $this->append($job, $target, $additionalData);
    }

    /**
     * Check if given message is already queued
     *
     * @param Message $message
     * @return bool
     */
    public function alreadyQueued(Message $message): bool
    {
        $encodedAdditionalData = $this->serializer->serialize($message->getAdditionalData());

        return $this->messageCollectionFactory->create()
            ->addFieldToFilter(Message::FIELD_GROUP, $message->getGroup())
            ->addFieldToFilter(Message::FIELD_JOB, $message->getJob())
            ->addFieldToFilter(Message::FIELD_TARGET, $message->getTarget())
            ->addFieldToFilter(Message::FIELD_ADDITIONAL_DATA, $encodedAdditionalData)
            ->addFieldToFilter(Message::FIELD_STATUS, Message::STATUS_PENDING)
            ->getSize() > 0;
    }

    /**
     * Parse additional data and set it to the message
     *
     * @param Message $message
     * @param array $additionalData
     * @return void
     */
    private function parseAdditionalData(Message $message, array $additionalData)
    {
        $settings = $additionalData[self::ADDITIONAL_SETTINGS_KEY] ?? [];

        // handle message grouping
        $group = $settings[Message::FIELD_GROUP] ?? Message::DEFAULT_GROUP;
        $message->setGroup($group);

        // additional settings can go here in future

        // prevent internal settings from end up in user additional data
        unset($additionalData[self::ADDITIONAL_SETTINGS_KEY]);
        $message->setAdditionalData($additionalData);
    }
}
