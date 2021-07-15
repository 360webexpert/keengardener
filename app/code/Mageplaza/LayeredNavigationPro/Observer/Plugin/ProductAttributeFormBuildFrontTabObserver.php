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

/**
 * Class ProductAttributeFormBuildFrontTabObserver
 * @package Mageplaza\LayeredNavigationPro\Observer\Plugin
 */
class ProductAttributeFormBuildFrontTabObserver
{
    /** @var \Mageplaza\LayeredNavigation\Helper\Data */
    protected $helper;

    /**
     * @param \Mageplaza\LayeredNavigation\Helper\Data $helper
     */
    public function __construct(\Mageplaza\LayeredNavigation\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this|mixed
     */
    public function aroundExecute(
        \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        if ($this->helper->isEnabled()) {
            return $this;
        }

        return $proceed($observer);
    }
}
