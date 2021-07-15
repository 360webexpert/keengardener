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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Plugin\Block\Swatches;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\UrlInterface;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered as SwatchesRenderLayered;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class RenderLayered
 * @package Mageplaza\LayeredNavigation\Block\Plugin\Swatches
 */
class RenderLayered
{
    /** @var UrlInterface */
    protected $_url;

    /** @var Pager */
    protected $_htmlPagerBlock;

    /** @var Data */
    protected $_moduleHelper;

    /** @type AbstractFilter */
    protected $filter;

    /**
     * RenderLayered constructor.
     *
     * @param UrlInterface $url
     * @param Pager $htmlPagerBlock
     * @param Data $moduleHelper
     */
    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock,
        Data $moduleHelper
    ) {
        $this->_url = $url;
        $this->_htmlPagerBlock = $htmlPagerBlock;
        $this->_moduleHelper = $moduleHelper;
    }

    /**
     * @param SwatchesRenderLayered $subject
     * @param AbstractFilter $filter
     *
     * @return array
     */
    public function beforeSetSwatchFilter(SwatchesRenderLayered $subject, AbstractFilter $filter)
    {
        $this->filter = $filter;

        return [$filter];
    }

    /**
     * @param SwatchesRenderLayered $subject
     * @param $proceed
     * @param $attributeCode
     * @param $optionId
     *
     * @return string
     */
    public function aroundBuildUrl(
        SwatchesRenderLayered $subject,
        $proceed,
        $attributeCode,
        $optionId
    ) {
        if (!$this->_moduleHelper->isEnabled()) {
            return $proceed($attributeCode, $optionId);
        }

        $attHelper = $this->_moduleHelper->getFilterModel();
        if ($attHelper->isMultiple($this->filter)) {
            $value = $attHelper->getFilterValue($this->filter);
            if (in_array($optionId, $value, true)) {
                $key = array_search($optionId, $value, true);
                if ($key !== false) {
                    unset($value[$key]);
                }
            } else {
                $value[] = $optionId;
            }
        } else {
            $value = [$optionId];
        }

        //Sort param on Url
        sort($value);

        $query = !empty($value) ? [$attributeCode => implode(',', $value)] : [$attributeCode => null];
        return $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}
