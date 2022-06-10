<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test;

use Discorgento\Queue\Api\QueueManagementInterface;
use Discorgento\Queue\Helper\Data as Helper;

class Append
{
    /** @var Helper */
    private $helper;

    /** @var QueueManagementInterface */
    private $queueManagement;

    public function __construct(
        Helper $helper,
        QueueManagementInterface $queueManagement
    ) {
        $this->helper = $helper;
        $this->queueManagement = $queueManagement;
    }

    public function append()
    {
        // legacy way
        for ($i = 1; $i <= 5; $i++) {
            $this->helper->append(Job::class, $i);
        }

        // modern way
        for ($i = 6; $i <= 10; $i++) {
            $this->queueManagement->append(Job::class, $i);
        }
    }
}
