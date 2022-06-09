<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Ui\Component\Listing\Column\Status;

use Discorgento\Queue\Model\Message;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /** @inheritDoc */
    public function toOptionArray()
    {
        return [[
            'value' => Message::STATUS_PENDING,
            'label' => __('Pending'),
        ], [
            'value' => Message::STATUS_PROCESSING,
            'label' => __('Processing'),
        ], [
            'value' => Message::STATUS_SUCCESS,
            'label' => __('Done'),
        ], [
            'value' => Message::STATUS_ERROR,
            'label' => __('Error'),
        ]];
    }
}
