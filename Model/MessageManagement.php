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
use Psr\Log\LoggerInterface;

class MessageManagement implements MessageManagementInterface
{
    /** @var DateTime */
    private $date;

    /** @var LoggerInterface */
    private $logger;

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
        LoggerInterface $logger,
        MessageRepositoryInterface $messageRepository,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->date = $date;
        $this->logger = $logger;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

            $status = Message::STATUS_SUCCESS;
        } catch (\Throwable $exception) {
            $result = __("EXCEPTION: '{$exception->getMessage()}', check the var/exception.log for more details.");
            $status = Message::STATUS_ERROR;

            $this->logger->error(
                "Discorgento_Queue: {$exception->getMessage()}",
                compact('exception')
            );
        } finally {
            $message->setResult($result);
            $this->updateMessageStatus($message, $status);
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
