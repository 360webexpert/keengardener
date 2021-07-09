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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config;

/**
 * Class GenerateBlocksAfterObserver
 * @package Mageplaza\SeoRule\Observer
 */
class GenerateBlocksAfterObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * GenerateBlocksAfterObserver constructor.
     *
     * @param Config $pageConfig
     * @param Registry $registry
     */
    public function __construct(
        Config $pageConfig,
        Registry $registry
    ) {
        $this->pageConfig = $pageConfig;
        $this->registry   = $registry;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $action = $observer->getEvent()->getFullActionName();
        if (in_array($action, ['catalog_product_view', 'catalog_category_view', 'cms_page_view'])) {
            $pageRobots = $this->registry->registry('seo_rule_robots');
            if (!empty($pageRobots)) {
                $this->pageConfig->setRobots($pageRobots);
            }
        }
    }
}
