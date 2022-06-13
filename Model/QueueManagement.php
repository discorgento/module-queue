<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\QueueManagementInterface;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
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
            /** @var Message */
            $message = $this->messageFactory->create()
                ->addData(compact('job', 'target'))
                ->setAdditionalData($additionalData);

            $this->parseSettings($message, $additionalData);

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

    /** @inheritDoc */
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
     *
     * @return bool
     */
    private function alreadyQueued(Message $message): bool
    {
        $encodedAdditionalData = json_encode($message->getAdditionalData());

        return $this->messageCollectionFactory->create()
            ->addFieldToFilter('group', $message->getGroup())
            ->addFieldToFilter('job', $message->getJob())
            ->addFieldToFilter('target', $message->getTarget())
            ->addFieldToFilter('additional_data', $encodedAdditionalData)
            ->addFieldToFilter('status', Message::STATUS_PENDING)
            ->count() > 0;
    }

    private function parseSettings(Message $message, array $additionalData)
    {
        $settings = &$additionalData[self::ADDITIONAL_SETTINGS_KEY];

        // handle message group
        $group = $settings['group'] ?? 'default';
        $message->setGroup($group);

        // prevent internal settings from end up in user additional data
        unset($settings);
    }
}
