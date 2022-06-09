<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

use Discorgento\Queue\Api\Data\MessageSearchResultsInterface;
use Discorgento\Queue\Model\Message;
use Magento\Framework\Api\SearchCriteriaInterface;

interface MessageRepositoryInterface
{
    /**
     * @param int $messageId
     * @return Message
     */
    public function getById($messageId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return MessageSearchResultsInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    public function save(Message $message);

    public function delete(Message $message);

    /**
     * @param int|string $messageId
     */
    public function deleteById($messageId);
}
