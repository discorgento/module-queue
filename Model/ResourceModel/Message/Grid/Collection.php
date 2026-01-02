<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model\ResourceModel\Message\Grid;

use Discorgento\Queue\Model\ResourceModel\Message\Collection as MessageCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;

class Collection extends MessageCollection implements SearchResultInterface
{
    /** @var AggregationInterface */
    private $aggregations;

    // phpcs:ignore
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @inheritDoc
     */
    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    /**
     * @inheritDoc
     */
    public function setAggregations($aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param integer|null $limit
     * @param integer|null $offset
     * @return array
     */
    public function getAllIds(?int $limit = null, ?int $offset = null): array
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * @inheritDoc
     */
    public function getSearchCriteria(): ?SearchCriteriaInterface
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null): Collection
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTotalCount(): int
    {
        return $this->getSize();
    }

    /**
     * @inheritDoc
     */
    public function setTotalCount($totalCount): Collection
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setItems(?array $items = null): Collection
    {
        return $this;
    }
}
