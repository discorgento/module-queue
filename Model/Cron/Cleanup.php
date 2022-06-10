<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\Cron;

use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Model\Message;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Cleanup
{
    /** @var DateTime */
    private $date;

    /** @var MessageRepository */
    private $messageRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(
        DateTime $date,
        MessageRepositoryInterface $messageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->date = $date;
        $this->messageRepository = $messageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Auto cleanup old entries
     */
    public function execute()
    {
        $this->cleanOldSuccess();
        $this->cleanOldFailure();
    }

    private function cleanOldSuccess()
    {
        $days = $this->scopeConfig->getValue('queue/general/success_jobs_expires');
        $this->cleanOld(Message::STATUS_SUCCESS, $days);
    }

    private function cleanOldFailure()
    {
        $days = $this->scopeConfig->getValue('queue/general/failed_jobs_expires');
        $this->cleanOld(Message::STATUS_ERROR, $days);
    }

    private function cleanOld($status, $days)
    {
        $daysAgoTimestamp = (new \DateTime())
            ->modify("-$days days")
            ->format('Y-m-d H:i:s');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $status)
            ->addFilter('queued_at', $daysAgoTimestamp, 'lt')
            ->create();

        $oldSuccess = $this->messageRepository->getList($searchCriteria);
        foreach ($oldSuccess->getItems() as $message) {
            $this->messageRepository->delete($message);
        }
    }
}
