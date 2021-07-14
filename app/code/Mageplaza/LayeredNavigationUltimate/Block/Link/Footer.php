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

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\LayeredNavigationUltimate\Helper\Data;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\ProductPosition;

/**
 * Class Footer
 * @package Mageplaza\LayeredNavigationUltimate\Block\Link
 */
class Footer extends Current
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $helper;

    /**
     * Footer constructor.
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);

        $this->helper = $helper;
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
                ProductPosition::FOOTERLINK
            )) {
                $html .= '<li class="nav item"><a href="' . $this->helper->getProductPageUrl($page) . '" title="' . $page->getPageTitle() . '">' . $page->getPageTitle() . '</a></li>';
            }
        }

        return $html;
    }
}
