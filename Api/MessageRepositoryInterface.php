<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

use Discorgento\Queue\Api\Data\MessageSearchResultsInterface;
use Discorgento\Queue\Model\Message;
use Magento\Framework\Api\SearchCriteriaInterface;

interface MessageRepositoryInterface
{
    /**
     * Get message with given id
     *
     * @param int $messageId
     * @return Message
     */
    public function getById($messageId);

    /**
     * Get list of messages
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return MessageSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Persist given message
     *
     * @param Message $message
     * @return void
     */
    public function save(Message $message);

    /**
     * Delete given message
     *
     * @param Message $message
     * @return void
     */
    public function delete(Message $message);

    /**
     * Delete message by id
     *
     * @param int|string $messageId
     */
    public function deleteById($messageId);
}
