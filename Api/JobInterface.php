<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

interface JobInterface
{
    /**
     * The background job logic goes here
     *
     * @param int|string|null $target
     * @param array $additionalData
     */
    public function execute($target, $additionalData);
}
