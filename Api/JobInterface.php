<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

interface JobInterface
{
    /**
     * Your job logic goes here
     *
     * @param int|string|null $target
     * @param array $additionalData
     * @return string|null the "result" of your job (optional)
     */
    public function execute($target, $additionalData);
}
