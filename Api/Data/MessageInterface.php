<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api\Data;

interface MessageInterface
{
    public const FIELD_GROUP = 'group';
    public const FIELD_JOB = 'job';
    public const FIELD_TARGET = 'target';
    public const FIELD_ADDITIONAL_DATA = 'additional_data';
    public const FIELD_STATUS = 'status';

    public const DEFAULT_GROUP = 'default';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    /**
     * "group" getter
     *
     * @return string
     */
    public function getGroup();

    /**
     * "group" setter
     *
     * @param string $group
     * @return \Discorgento\Queue\Api\Data\MessageInterface
     */
    public function setGroup($group);

    /**
     * "job" getter
     *
     * @return string|null
     */
    public function getJob();

    /**
     * "job" setter
     *
     * @param string $job
     * @return \Discorgento\Queue\Api\Data\MessageInterface
     */
    public function setJob($job);

    /**
     * "target" getter
     *
     * @return string|null
     */
    public function getTarget();

    /**
     * "target" setter
     *
     * @param string $target
     * @return \Discorgento\Queue\Api\Data\MessageInterface
     */
    public function setTarget($target);

    /**
     * Set additional data that can be used by the job later
     *
     * @param array|null $additionalData
     * @return \Discorgento\Queue\Api\Data\MessageInterface
     */
    public function setAdditionalData($additionalData);

    /**
     * Retrieve additional data array
     *
     * @return array
     */
    public function getAdditionalData();
}
