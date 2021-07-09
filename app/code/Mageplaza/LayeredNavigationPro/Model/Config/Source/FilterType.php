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

namespace Mageplaza\LayeredNavigationPro\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mageplaza\LayeredNavigationPro\Helper\Data as LayerHelper;

/**
 * Class FilterType
 * @package Mageplaza\LayeredNavigationPro\Model\Config\Source
 */
class FilterType implements ArrayInterface
{
    /**
     * @type \Mageplaza\LayeredNavigationPro\Helper\Data
     */
    protected $helper;

    /**
     * @param \Mageplaza\LayeredNavigationPro\Helper\Data $helper
     */
    public function __construct(LayerHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->helper->getDisplayOptions() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
