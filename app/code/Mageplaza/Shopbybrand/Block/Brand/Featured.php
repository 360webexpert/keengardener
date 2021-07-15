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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Brand;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class Featured
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class Featured extends Brand
{
    /**
     * Default feature template
     *
     * @type string
     */
    protected $_template = 'Mageplaza_Shopbybrand::brand/featured.phtml';

    /**
     * @return string
     */
    public function includeCssLib()
    {
        $cssFiles = ['Mageplaza_Core::css/owl.carousel.css', 'Mageplaza_Core::css/owl.theme.css'];
        $template = '<link rel="stylesheet" type="text/css" media="all" href="%s">' . "\n";
        $result = '';
        foreach ($cssFiles as $file) {
            $asset = $this->_assetRepo->createAsset($file);
            $result .= sprintf($template, $asset->getUrl());
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getFeatureTitle()
    {
        return $this->helper->getFeatureConfig('title');
    }

    /**
     * @return bool
     */
    public function showLabel()
    {
        return $this->helper->getFeatureConfig('display');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getFeaturedBrand()
    {
        $featureBrands = [];
        $collection = $this->_brandFactory->create()->getBrandCollection($this->_storeManager->getStore()->getId());
        foreach ($collection as $brand) {
            if ($brand->getIsFeatured()) {
                $featureBrands[] = $brand;
            }
        }

        return $featureBrands;
    }

    /**
     * Get style display featured brand
     *
     * @return mixed
     */
    public function getStyleDisplayFeature()
    {
        return $this->helper->getFeatureConfig('style');
    }
}
