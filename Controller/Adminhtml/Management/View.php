<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Controller\Adminhtml\Management;

use Discorgento\Queue\Model\MessageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class View extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Discorgento_Queue::management';

    private Registry $registry;
    private PageFactory $resultPageFactory;
    private MessageFactory $objectFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        MessageFactory $objectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->objectFactory = $objectFactory;
        parent::__construct($context);
    }

    /** @inheritDoc */
    public function execute()
    {
        $id = $this->getRequest()->getParam('message_id');
        $objectInstance = $this->objectFactory->create();

        if ($id) {
            $objectInstance->load($id);
            if (!$objectInstance->getId()) {
                $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $objectInstance->addData($data);
        }

        $this->registry->register('message_id', $id);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Discorgento_Queue::management');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Queue Management - Message #%1', $id)
        );

        return $resultPage;
    }
}
