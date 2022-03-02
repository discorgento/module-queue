<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Model\ResourceModel\Message as MessageResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Message extends AbstractModel implements MessageInterface, IdentityInterface
{
    public const CACHE_TAG = 'discorgento_queue_message';

    /** @inheritDoc */
    protected function _construct()
    {
        $this->_init(MessageResourceModel::class);
    }

    /** @inheritDoc */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_{$this->getId()}"];
    }

    /** @inheritDoc */
    public function setAdditionalData($additionalData)
    {
        $encodedData = json_encode($additionalData ?: []);

        return $this->setData('additional_data', $encodedData);
    }

    /** @inheritDoc */
    public function getAdditionalData()
    {
        $encodedData = (string) $this->getData('additional_data');

        return json_decode($encodedData, true) ?: [];
    }
}
