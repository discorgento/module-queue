<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

use Magento\Framework\Api\SearchResults;

interface MessageManagementInterface
{
    /**
     * Execute a single message
     *
     * @param Data\MessageInterface $message
     * @return void
     */
    public function process(Data\MessageInterface $message);

    /**
     * Execute multiple messages at once
     *
     * @param iterable $messages
     * @return void
     */
    public function massProcess(iterable $messages);

    /**
     * Get messages waiting to be processed
     *
     * @return SearchResults
     */
    public function getPending();

    /**
     * Get failed messages waiting to be retried
     *
     * @return Data\MessageInterface[]
     */
    public function getToBeRetried();
}
