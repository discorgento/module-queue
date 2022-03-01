<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Api;

use Discorgento\Queue\Model\Message;

interface MessageRepositoryInterface
{
    /**
     * @param Message $message
     */
    public function save($message);

    /**
     * @param int $messageId
     * @return Message
     */
    public function getById($messageId);

    /**
     * @param Message $message
     */
    public function delete($message);

    /**
     * @param int $messageId
     */
    public function deleteById($messageId);
}
