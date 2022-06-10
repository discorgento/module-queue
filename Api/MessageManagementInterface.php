<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

use Magento\Framework\Api\SearchResults;

interface MessageManagementInterface
{
    /**
     * Process given queue message
     */
    public function process(Data\MessageInterface $message);

    /**
     * Get messages waiting to be processed
     * @return SearchResults
     */
    public function getPending();

    /**
     * Get failed messages waiting to be retried
     * @return SearchResults
     */
    public function getToBeRetried();
}
