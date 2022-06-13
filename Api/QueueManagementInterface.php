<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

interface QueueManagementInterface
{
    /**
     * Append given job to the queue
     *
     * @param string $job
     * @param int|string|null $target
     * @param array $additionalData
     */
    public function append(string $job, $target = null, array $additionalData = []);

    /**
     * Append a job to the queue, with a group identifier
     * Eg.: each of yours modules name that use the queue
     *
     * @param string $group
     * @param string $job
     * @param int|string|null $target
     * @param array $additionalData
     */
    public function appendToGroup(string $group, string $job, $target = null, array $additionalData = []);
}
