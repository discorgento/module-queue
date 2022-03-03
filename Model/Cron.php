<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Helper\Executor;

class Cron
{
    /** @var Executor */
    protected $executorHelper;

    public function __construct(
        Executor $executorHelper
    ) {
        $this->executorHelper = $executorHelper;
    }

    /**
     * Execute the pending jobs
     */
    public function execute()
    {
        $messages = $this->executorHelper->getPendingMessages();
        foreach ($messages as $message) {
            $this->executorHelper->execute($message);
        }
    }
}
