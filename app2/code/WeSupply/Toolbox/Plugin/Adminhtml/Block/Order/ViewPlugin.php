<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Plugin\Adminhtml\Block\Order;

use Magento\Sales\Block\Adminhtml\Order\View;
use WeSupply\Toolbox\Block\Adminhtml\Order\View\WsExternalLinks;

/**
 * Class ViewPlugin
 * @package WeSupply\Toolbox\Plugin\Adminhtml\Block\Order
 */
class ViewPlugin
{
    /**
     * @var WsExternalLinks
     */
    private $externalLinks;

    /**
     * @var array[]
     */
    private $viewOrderLinkAttr;

    /**
     * @var array[]
     */
    private $returnListLinkAttr;

    /**
     * @var array[]
     */
    private $externalLinksData = [];

    /**
     * ViewPlugin constructor.
     * @param WsExternalLinks $externalLinks
     */
    public function __construct(WsExternalLinks $externalLinks)
    {
        $this->externalLinks = $externalLinks;

        $this->initViewOrderLinkAttr();
        $this->initReturnsListLinkAttr();
        $this->setExternalLinksData();
    }

    /**
     * @param View $subject
     */
    public function beforeSetLayout(View $subject)
    {
        if ($this->externalLinks->canShowButton()) {
            $subject->addButton(
                'ws_external_links_btn',
                [
                    'class' => __('ws-external-links'),
                    'label' => __('WeSupply'),
                    'onclick' => 'javascript.void(0)',
                    'data_attribute' => [
                        'ws-links' => $this->externalLinksData
                    ]
                ],
                -1
            );
        }
    }

    /**
     * @retrun void
     */
    private function setExternalLinksData()
    {
        if ($this->externalLinks->canShowViewOrder()) {
            $this->externalLinksData['view_order'] = $this->viewOrderLinkAttr;
        }
        if ($this->externalLinks->canShowReturnsList()) {
            $this->externalLinksData['return_list'] = $this->returnListLinkAttr;
        }
    }

    /**
     * @retrun void
     */
    private function initViewOrderLinkAttr()
    {
        $this->viewOrderLinkAttr = [
            'title' => __('Order View'),
            'url' => $this->externalLinks->getWsOrderViewUrl()
        ];
    }

    /**
     * @retrun void
     */
    private function initReturnsListLinkAttr()
    {
        $this->returnListLinkAttr = [
            'title' => __('Return List'),
            'url' => $this->externalLinks->getWsReturnsListUrl()
        ];
    }
}


