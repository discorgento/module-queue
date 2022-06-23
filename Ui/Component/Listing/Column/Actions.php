<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public const URL_PATH_VIEW = 'discorgento_queue/management/view';

    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $storeId = $this->context->getFilterParam('store_id');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['message_id'])) {
                continue;
            }

            $item[$this->getData('name')]['view'] = [
                'href' => $this->urlBuilder->getUrl(
                    self::URL_PATH_VIEW,
                    ['message_id' => $item['message_id'], 'store' => $storeId]
                ),
                'label' => __('View'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
