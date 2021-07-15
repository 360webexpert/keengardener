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

namespace Mageplaza\LayeredNavigationUltimate\Plugin;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\TreeFactory;
use Mageplaza\LayeredNavigationUltimate\Helper\Data;
use Mageplaza\LayeredNavigationUltimate\Model\Config\Source\ProductPosition;
use Smartwave\Megamenu\Block\Topmenu;

/**
 * Class PortoTopmenu
 * @package Mageplaza\LayeredNavigationUltimate\Plugin
 */
class PortoTopmenu
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var TreeFactory
     */
    protected $treeFactory;

    /**
     * Topmenu constructor.
     *
     * @param Data $helper
     * @param TreeFactory $treeFactory
     */
    public function __construct(
        Data $helper,
        TreeFactory $treeFactory
    ) {
        $this->helper = $helper;
        $this->treeFactory = $treeFactory;
    }

    /**
     * @param Topmenu $subject
     * @param $categories
     *
     * @return mixed
     */
    public function afterGetStoreCategories(
        Topmenu $subject,
        $categories
    ) {
        $pages = $this->helper->getProductsPageCollection();
        foreach ($pages as $page) {
            if ($this->helper->canShowProductPageLink($page, ProductPosition::CATEGORY)) {
                $categories->add(
                    new Node(
                        [
                            'name' => $page->getPageTitle(),
                            'id' => 'mpLN-node',
                            'url' => $this->helper->getProductPageUrl($page),
                            'is_active' => 1,
                            'include_in_menu' => 1
                        ],
                        'id',
                        $this->treeFactory->create()
                    )
                );
            }
        }

        return $categories;
    }
}
