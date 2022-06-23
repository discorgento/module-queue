<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Model;

use Discorgento\Queue\Api\Data\MessageInterfaceFactory;
use Discorgento\Queue\Api\Data\MessageSearchResultsInterfaceFactory;
use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Model\ResourceModel\Message as ResourceMessage;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class MessageRepository implements MessageRepositoryInterface
{
    /** @var ResourceMessage */
    protected $resource;

    /** @var MessageInterfaceFactory */
    protected $messageFactory;

    /** @var MessageCollectionFactory */
    protected $messageCollectionFactory;

    /** @var MessageSearchResultsInterfaceFactory */
    protected $searchResultsFactory;

    /** @var CollectionProcessorInterface */
    protected $collectionProcessor;

    public function __construct(
        ResourceMessage $resource,
        MessageInterfaceFactory $messageFactory,
        MessageCollectionFactory $messageCollectionFactory,
        MessageSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->messageFactory = $messageFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /** @inheritDoc */
    public function getById($messageId)
    {
        $message = $this->messageFactory->create();
        $this->resource->load($message, $messageId);
        if (!$message->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $messageId));
        }

        return $message;
    }

    /** @inheritDoc */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->messageCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /** @inheritDoc */
    public function save($message)
    {
        $this->resource->save($message);
    }

    /** @inheritDoc */
    public function delete($message)
    {
        $this->resource->delete($message);
    }

    /** @inheritDoc */
    public function deleteById($messageId)
    {
        $this->delete($this->getById($messageId));
    }
}
