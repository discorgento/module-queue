<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface MessageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items list.
     *
     * @return \Discorgento\Queue\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Discorgento\Queue\Api\Data\MessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
