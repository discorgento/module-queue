<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\SerializerInterface;

class Message extends AbstractModel implements MessageInterface
{
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

    /**
     * @inheritDoc
     */
    public function getGroup()
    {
        return $this->getData(self::FIELD_GROUP) ?: self::DEFAULT_GROUP;
    }

    /**
     * @inheritDoc
     */
    public function setGroup($group): self
    {
        return $this->setData(self::FIELD_GROUP, $group);
    }

    /**
     * @inheritDoc
     */
    public function getJob()
    {
        return $this->getData(self::FIELD_JOB);
    }

    /**
     * @inheritDoc
     */
    public function setJob($job): self
    {
        return $this->setData(self::FIELD_JOB, $job);
    }

    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return $this->getData(self::FIELD_TARGET);
    }

    /**
     * @inheritDoc
     */
    public function setTarget($target): self
    {
        return $this->setData(self::FIELD_TARGET, $target);
    }

    /** @inheritDoc */
    public function setAdditionalData($additionalData)
    {
        $encodedData = $this->serializer->serialize($additionalData ?: []);

        return $this->setData(self::FIELD_ADDITIONAL_DATA, $encodedData);
    }

    /** @inheritDoc */
    public function getAdditionalData()
    {
        $encodedData = (string) $this->getData(self::FIELD_ADDITIONAL_DATA);

        return $this->serializer->unserialize($encodedData, true) ?: [];
    }
}
