<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Api\MessageManagementInterface;
use Discorgento\Queue\Api\MessageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class MessageManagement implements MessageManagementInterface
{
    /** @var DateTime */
    private $date;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(
        DateTime $date,
        MessageRepositoryInterface $messageRepository,
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->date = $date;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /** @inheritDoc */
    public function process(MessageInterface $message)
    {
        try {
            $message->setExecutedAt($this->date->gmtDate());
            $message->setTries($message->getTries() + 1);
            $this->updateMessageStatus($message, Message::STATUS_PROCESSING);

            $job = $this->objectManager->create($message->getJob());

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

    /** @inheritDoc */
    public function massProcess(iterable $messages)
    {
        foreach ($messages as $message) {
            $this->process($message);
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

    /** @inheritDoc */
    public function getToBeRetried()
    {
        $retryAmount = $this->scopeConfig->getValue('queue/general/auto_retry_amount');
        if (!$retryAmount) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', Message::STATUS_ERROR)
            ->addFilter('tries', $retryAmount, 'lt')
            ->create();

        return $this->messageRepository->getList($searchCriteria)->getItems();
    }
}
