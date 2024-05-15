<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\ResourceModel\Message;

use Discorgento\Queue\Model\Message;
use Discorgento\Queue\Model\ResourceModel\Message as MessageResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = MessageResourceModel::ID_FIELD;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Message::class, MessageResourceModel::class);
    }
}
