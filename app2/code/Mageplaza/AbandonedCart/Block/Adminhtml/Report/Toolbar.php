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

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Report;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Toolbar
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Report
 */
class Toolbar extends Template
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Toolbar constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateTime $date,
        array $data = []
    ) {
        $this->date = $date;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getFromDefault()
    {
        $current = $this->date->date('m/d/Y');

        return $this->date->date('m/d/Y', $current . '-15 days');
    }

    /**
     * @return string
     */
    public function getToDefault()
    {
        return $this->date->date('m/d/Y');
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('abandonedcart/index/ajax');
    }
}
