<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\Cron;

use Discorgento\Queue\Api\MessageManagementInterface;

class Execute
{
    /** @var MessageManagementInterface */
    private $messageManagement;

    public function __construct(
        MessageManagementInterface $messageManagement
    ) {
        $this->messageManagement = $messageManagement;
    }

    /**
     * Consume the queue in background
     */
    public function execute()
    {
        $pendingMessages = $this->messageManagement->getPending();
        foreach ($pendingMessages->getItems() as $message) {
            $this->messageManagement->process($message);
        }
    }
}
