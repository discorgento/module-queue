<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Ui\Component\Listing\Column\Group;

use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /** @var MessageCollectionFactory */
    private $messageCollectionFactory;

    // phpcs:ignore
    public function __construct(
        MessageCollectionFactory $messageCollectionFactory
    ) {
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $messages = $this->messageCollectionFactory->create();
        $messages->getSelect()->group('group');

        $groups = array_map(
            fn ($message) => $message->getGroup(),
            $messages->getItems()
        );

        return array_map(
            fn ($group) => ['label' => $group, 'value' => $group],
            $groups
        );
    }
}
