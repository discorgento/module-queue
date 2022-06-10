<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Helper;

use Discorgento\Queue\Api\QueueManagementInterface;

/**
 * Although technically deprecated, this class
 * will be kept to assure retrocompatibility.
 */
class Data
{
    /** @var QueueManagementInterface */
    private $queueManagement;

    public function __construct(
        QueueManagementInterface $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    /**
     * Append given job to the queue
     *
     * @param string $job
     * @param int|string|null $target
     * @param array $additionalData
     */
    public function append($job, $target = null, $additionalData = [])
    {
        $this->queueManagement->append($job, $target, $additionalData);
    }
}
