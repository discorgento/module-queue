<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Ui\Component\Form;

use Discorgento\Queue\Model\ResourceModel\Message\Collection;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    /** must be protected */
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
    }

    public function getData(): array
    {
        if (!$this->loadedData) {
            $items = $this->collection->getItems();
            foreach ($items as $item) {
                $this->loadedData[$item->getId()] = $item->getData();
                break;
            }
        }

        return $this->loadedData;
    }
}
