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

namespace Mageplaza\LayeredNavigationPro\Model\Plugin;

use Closure;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\View\LayoutInterface;
use Mageplaza\LayeredNavigationPro\Helper\Data;

/**
 * Class FilterRenderer
 */
class FilterRenderer
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @type Data
     */
    protected $helper;

    /**
     * @var Manager
     */
    protected $_manager;

    /**
     * FilterRenderer constructor.
     *
     * @param Manager $manager
     * @param LayoutInterface $layout
     * @param Data $helper
     * @param array $blocks
     */
    public function __construct(
        Manager $manager,
        LayoutInterface $layout,
        Data $helper,
        array $blocks = []
    ) {
        $this->layout   = $layout;
        $this->helper   = $helper;
        $this->_manager = $manager;
    }

    /**
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param Closure $proceed
     * @param FilterInterface $filter
     *
     * @return mixed
     */
    public function aroundRender(
        \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject,
        Closure $proceed,
        FilterInterface $filter
    ) {
        if ($this->helper->isEnabled()) {
            $displayTypes = $this->helper->getDisplayTypes();
            $filterType   = $this->helper->getFilterModel()->getFilterType($filter);

            if (isset($displayTypes[$filterType]) && isset($displayTypes[$filterType]['class'])) {
                $this->_manager->dispatch('custom_display_filter', ['filter' => $filter]);
                if ($filter->getCustomDisplayFilter()) {
                    return $filter->getCustomDisplayFilter();
                }

                return $this->layout
                    ->createBlock($displayTypes[$filterType]['class'])
                    ->setFilter($filter)
                    ->toHtml();
            }
        }

        return $proceed($filter);
    }
}
