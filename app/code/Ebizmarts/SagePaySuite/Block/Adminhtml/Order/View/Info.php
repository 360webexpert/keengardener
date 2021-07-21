<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Order\View;

use Ebizmarts\SagePaySuite\Model\Config;

/**
 * Backend order view block for Sage Pay payment information
 *
 * @package Ebizmarts\SagePaySuite\Block\Adminhtml\Order\View
 */
class Info extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Ebizmarts\SagePaySuite\Helper\Data
     */
    private $suiteHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        Config $config,
        array $data = []
    ) {
    
        $this->order       = $registry->registry('current_order');
        $this->config      = $config;
        $this->suiteHelper = $suiteHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        return $this->order->getPayment();
    }

    public function getTemplate()
    {
        $template = parent::getTemplate();

        $isSagePayMethod = $this->config->isSagePaySuiteMethod($this->getPayment()->getMethod());

        if ($isSagePayMethod === false) {
            $template = '';
        }

        return $template;
    }

    public function getSyncFromApiUrl()
    {
        $url =  $this->getUrl('sagepaysuite/order/syncFromApi', ['order_id'=>$this->order->getId()]);
        return $url;
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Helper\Data
     */
    public function getSuiteHelper()
    {
        return $this->suiteHelper;
    }

    /**
     * If the 3D status code is equal to 2007 will return true
     *
     * @param int $statusCode
     * @return bool
     */
    public function isThreeDRedirect($statusCode)
    {
        return $statusCode == 2007;
    }

    /**
     * @return string
     */
    public function getFirstParagraph()
    {
        return $this->escapeHtml(
            __(
                "The customer was redirected to their bank page to complete 3D authentication."
                ." On this scenario two things can happen:"
            )
        );
    }

    /**
     * @return string
     */
    public function getSecondParagraph()
    {
        return $this->escapeHtml(
            __(
                "- The customer completes the 3D check and the order status is updated."
            )
        );
    }

    /**
     * @return string
     */
    public function getThirdParagraph()
    {
        return $this->escapeHtml(
            __(
                "- The customer does not complete 3D and the message will still be visible."
                ." For example, the customer does not remember their pin code."
            )
        );
    }

    /**
     * @return string
     */
    public function getForthParagraph()
    {
        return $this->escapeHtml(
            __(
                "If after a few minutes the customer does not complete the order,"
                ." you can click the Sync from API link"
                ." to query Opayo for the latest information on this transaction."
            )
        );
    }
}
