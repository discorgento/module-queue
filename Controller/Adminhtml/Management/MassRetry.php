<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Controller\Adminhtml\Management;

use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Model\Message;
use Discorgento\Queue\Model\ResourceModel\Message\Collection;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassRetry extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Discorgento_Queue::management';

    /** @var Filter */
    private $filter;

    /** @var Collection */
    private $objectCollection;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    // phpcs:ignore
    public function __construct(
        Context $context,
        Filter $filter,
        Collection $objectCollection,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->filter = $filter;
        $this->objectCollection = $objectCollection;
        $this->messageRepository = $messageRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->objectCollection);
        $collectionSize = $collection->getSize();

        foreach ($collection as $message) {
            $message->addData([
                'status' => Message::STATUS_PENDING,
                'tries' => 0,
                'executed_at' => null,
                'result' => null,
            ]);

            $this->messageRepository->save($message);
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
