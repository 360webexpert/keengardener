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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Model\Term\Source\System\Config;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ApplyFor
 * @package Mageplaza\SeoCrosslinks\Model\Term\Source\System\Config
 */
class ApplyFor implements ArrayInterface
{
    const PRODUCT_DESCRIPTION       = 0;
    const PRODUCT_SHORT_DESCRIPTION = 1;
    const CATEGORY_DESCRIPTION      = 2;
    const CMS_PAGE_CONTENT          = 3;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::PRODUCT_DESCRIPTION,
                'label' => __('Product Description')
            ],
            [
                'value' => self::PRODUCT_SHORT_DESCRIPTION,
                'label' => __('Product Short Description')
            ],
            [
                'value' => self::CATEGORY_DESCRIPTION,
                'label' => __('Category Description')
            ],
            [
                'value' => self::CMS_PAGE_CONTENT,
                'label' => __('CMS Page Content')
            ],
        ];

        return $options;
    }
}
