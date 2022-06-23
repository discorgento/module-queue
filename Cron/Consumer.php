<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Cron;

use Discorgento\Queue\Api\MessageManagementInterface;

class Consumer
{
    /** @var MessageManagementInterface */
    private $messageManagement;

    public function __construct(
        MessageManagementInterface $messageManagement
    ) {
        $this->messageManagement = $messageManagement;
    }

    /**
     * Consume the jobs in queue
     */
    public function execute()
    {
        $pendingMessages = $this->messageManagement->getPending()->getItems();
        $this->messageManagement->massProcess($pendingMessages);
    }

    /**
     * Retry the failed jobs
     */
    public function retry()
    {
        $failedJobs = $this->messageManagement->getToBeRetried();
        $this->messageManagement->massProcess($failedJobs);
    }
}
