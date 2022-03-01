<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api\Data;

interface MessageInterface
{
    /**
     * Set additional data that can be used by the job later
     *
     * @param array|null $additionalData
     * @return MessageInterface
     */
    public function setAdditionalData($additionalData);

    /**
     * Retrieve additional data array
     * @return array
     */
    public function getAdditionalData();
}
