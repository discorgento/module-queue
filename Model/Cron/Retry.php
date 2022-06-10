<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\Cron;

use Discorgento\Queue\Api\MessageManagementInterface;

class Retry
{
    /** @var MessageManagementInterface */
    private $messageManagement;

    public function __construct(
        MessageManagementInterface $messageManagement
    ) {
        $this->messageManagement = $messageManagement;
    }

    /**
     * Retried the failed jobs in background
     */
    public function execute()
    {
        $failedJobs = $this->messageManagement->getToBeRetried();
        foreach ($failedJobs->getItems() as $message) {
            $this->messageManagement->process($message);
        }
    }
}
