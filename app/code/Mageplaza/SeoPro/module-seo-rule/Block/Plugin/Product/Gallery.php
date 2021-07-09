<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Block\Plugin\Product;

use Mageplaza\SeoRule\Helper\Data as HelperConfig;

/**
 * Class Gallery
 * @package Mageplaza\SeoRule\Block\Plugin\Product
 */
class Gallery
{
    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * Gallery constructor.
     *
     * @param HelperConfig $helpConfig
     */
    function __construct(HelperConfig $helpConfig)
    {
        $this->helperConfig = $helpConfig;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {
        if ($this->helperConfig->isEnableAutomateAltImg()) {
            $images = HelperConfig::jsonDecode($result);
            foreach ($images as &$image) {
                if (!$image['caption']) {
                    $image['caption'] = $subject->getProduct()->getName();
                }
            }

            $result = HelperConfig::jsonEncode($images);
        }

        return $result;
    }
}
