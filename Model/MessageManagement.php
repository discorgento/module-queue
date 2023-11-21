<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Api\MessageManagementInterface;
use Discorgento\Queue\Api\MessageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class MessageManagement implements MessageManagementInterface
{
    private const LOCK_FILE = 'discorgento_queue.lock';

    /** @var DateTime */
    private $date;

    /** @var FileDriver */
    private $fileDriver;

    /** @var LoggerInterface */
    private $logger;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    // phpcs:ignore
    public function __construct(
        DateTime $date,
        FileDriver $fileDriver,
        LoggerInterface $logger,
        MessageRepositoryInterface $messageRepository,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->date = $date;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->messageRepository = $messageRepository;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /** @inheritDoc */
    public function process(MessageInterface $message)
    {
        $lockFilePath = $this->checkLockfile();

        try {
            $message->setExecutedAt($this->date->gmtDate());
            $message->setTries($message->getTries() + 1);
            $this->updateMessageStatus($message, Message::STATUS_PROCESSING);

            $job = $this->objectManager->create($message->getJob());

            $result = $job->execute(
                $message->getTarget(),
                $message->getAdditionalData()
            );

            if (is_array($result)) {
                $result = json_encode($result, JSON_PRETTY_PRINT);
            }

            $status = Message::STATUS_SUCCESS;
        } catch (\Throwable $exception) {
            $result = __("EXCEPTION: '%1', check the var/log/exception.log for more details.", $exception->getMessage());
            $status = Message::STATUS_ERROR;

            $this->logger->error(
                "Discorgento_Queue: {$exception->getMessage()}",
                compact('exception')
            );
        } finally {
            $message->setResult($result);
            $this->updateMessageStatus($message, $status);
            $this->fileDriver->deleteFile($lockFilePath);
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

    /**
     * Prevent jobs conflict by avoid them to run in parallel
     * @throws LocalizedException
     * @return string Lockfile relative path
     */
    private function checkLockfile()
    {
        $lockFilepath = BP . DIRECTORY_SEPARATOR . DirectoryList::VAR_DIR . DIRECTORY_SEPARATOR . self::LOCK_FILE;
        if ($this->fileDriver->isFile($lockFilepath)) {
            $lockTimeLifespan = floatval($this->scopeConfig->getValue('queue/general/lockfile_expires')) * 3600 ?: 3600;
            if (time() - filectime($lockFilepath) < $lockTimeLifespan) {
                throw new LocalizedException(
                    __('Queue already running! If you think this is mistake, delete the "%1" lock file.', $lockFilepath)
                );
            }

            $this->fileDriver->deleteFile($lockFilepath);
        }

        $this->fileDriver->touch($lockFilepath);

        return $lockFilepath;
    }
}
