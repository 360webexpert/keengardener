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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Observer\Plugin;

use Closure;
use Magento\Framework\Event\Observer;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class ProductAttributeFormBuildFrontTabObserver
 * @package Mageplaza\LayeredNavigationPro\Observer\Plugin
 */
class ProductAttributeFormBuildFrontTabObserver
{
    /** @var Data */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver $subject
     * @param Closure $proceed
     * @param Observer $observer
     *
     * @return $this|mixed
     */
    public function aroundExecute(
        \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver $subject,
        Closure $proceed,
        Observer $observer
    ) {
        if ($this->helper->isEnabled()) {
            return $this;
        }

        return $proceed($observer);
    }
}
