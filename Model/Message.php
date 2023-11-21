<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Model\ResourceModel\Message as MessageResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Message extends AbstractModel implements MessageInterface, IdentityInterface
{
    public const CACHE_TAG = 'discorgento_queue';

    public const STATUS_PENDING = 'pending';

    /** @var SerializerInterface */
    private $serializer;

    // phpcs:ignore
    public function __construct(
        SerializerInterface $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer;
    }

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_ERROR = 'error';

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
        $encodedData = $this->serializer->serialize($additionalData ?: []);

        return $this->setData('additional_data', $encodedData);
    }

    /** @inheritDoc */
    public function getAdditionalData()
    {
        $encodedData = (string) $this->getData('additional_data');

        return $this->serializer->unserialize($encodedData, true) ?: [];
    }
}
