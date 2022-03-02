<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Model\ResourceModel\Message as MessageResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;

class MessageRepository implements MessageRepositoryInterface
{
    /** @var MessageFactory */
    protected $messageFactory;

    /** @var MessageResourceModel */
    protected $messageResourceModel;

    public function __construct(
        MessageFactory $messageFactory,
        MessageResourceModel $messageResourceModel
    ) {
        $this->messageFactory = $messageFactory;
        $this->messageResourceModel = $messageResourceModel;
    }

    /** @inheritDoc */
    public function save($message)
    {
        $this->messageResourceModel->save($message);
    }

    /** @inheritDoc */
    public function getById($messageId)
    {
        $message = $this->messageFactory->create();
        $this->messageResourceModel->load($message, $messageId);
        if (!$message->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $messageId));
        }

        return $message;
    }

    /** @inheritDoc */
    public function delete($message)
    {
        $this->messageResourceModel->delete($message);
    }

    /** @inheritDoc */
    public function deleteById($messageId)
    {
        $this->delete($this->getById($messageId));
    }
}
