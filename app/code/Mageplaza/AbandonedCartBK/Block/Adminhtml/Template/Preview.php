<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Template;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Mageplaza\AbandonedCart\Model\LogsFactory;

/**
 * Class Preview
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Template
 */
class Preview extends Widget
{
    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @param Context $context
     * @param LogsFactory $logsFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        LogsFactory $logsFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->logsFactory = $logsFactory;
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $content = '';

        $log = $this->getLogs();
        if ($log->getId()) {
            $content = htmlspecialchars_decode($this->getLogs()->getEmailContent());
        }

        return $content;
    }

    /**
     * Load email log by id
     *
     * @return mixed
     */
    private function getLogs()
    {
        $logId = $this->getRequest()->getParam('id');

        return $this->logsFactory->create()->load($logId);
    }
}
