<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface MessageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Message list.
     * @return MessageInterface[]
     */
    public function getItems();

    /**
     * Set job list.
     * @param MessageInterface[] $items
     * @return self
     */
    public function setItems(array $items);
}
