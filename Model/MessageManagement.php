<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Api\MessageManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class MessageManagement implements MessageManagementInterface
{
    /** @var DateTime */
    private $date;

    /** @var MessageRepository */
    private $messageRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    public function __construct(
        DateTime $date,
        MessageRepository $messageRepository,
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->date = $date;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /** @inheritDoc */
    public function process(MessageInterface $message)
    {
        try {
            $job = $this->objectManager->create($message->getJob());

            $message->setExecutedAt($this->date->gmtDate());
            $this->updateMessageStatus($message, Message::STATUS_PROCESSING);

            $result = $job->execute(
                $message->getTarget(),
                $message->getAdditionalData()
            );

            $message->setResult($result);
            $this->updateMessageStatus($message, Message::STATUS_SUCCESS);
        } catch (\Throwable $exception) {
            $message->setResult($exception->getMessage());
            $this->updateMessageStatus($message, Message::STATUS_ERROR);
        }
    }

    private function updateMessageStatus(Message $message, string $status): void
    {
        $message->setStatus($status);
        $this->messageRepository->save($message);
    }

    /** @inheritDoc */
    public function getPending()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', Message::STATUS_PENDING)
            ->create();

        return $this->messageRepository->getList($searchCriteria);
    }
}
