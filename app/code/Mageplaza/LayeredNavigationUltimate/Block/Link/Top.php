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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Block\Link;

use Magento\Framework\View\Element\Html\Link;

/**
 * Class Top
 * @package Mageplaza\LayeredNavigationUltimate\Block\Link
 */
class Top extends Link
{
    /**
     * @type \Mageplaza\LayeredNavigationUltimate\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\LayeredNavigationUltimate\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageplaza\LayeredNavigationUltimate\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $pageCollection = $this->helper->getProductsPageCollection();
        foreach ($pageCollection as $page) {
            if ($this->helper->canShowProductPageLink(
                $page,
                \Mageplaza\LayeredNavigationUltimate\Model\Config\Source\ProductPosition::TOPLINK
            )) {
                $html .= '<li class="nav item"><a href="' . $this->helper->getProductPageUrl($page) . '" title="' . $page->getPageTitle() . '">' . $page->getPageTitle() . '</a></li>';
            }
        }

        return $html;
    }

    /**
     * Get sort order for block.
     */
    public function getSortOrder()
    {
        return $this->getData('sortOrder');
    }
}
