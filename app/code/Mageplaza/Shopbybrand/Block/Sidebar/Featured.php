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

namespace Mageplaza\Shopbybrand\Block\Sidebar;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Shopbybrand\Block\Brand;

/**
 * Class Featured
 *
 * @package Mageplaza\Shopbybrand\Block\Sidebar
 */
class Featured extends Brand
{
    /**
     * Default feature template
     *
     * @type string
     */
    protected $_template = 'Mageplaza_Shopbybrand::sidebar/featured.phtml';

    /**
     * Default title sidebar featured brand
     */
    const TITLE = 'Featured Brand';

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
     * @return mixed|string
     */
    public function getFeatureTitle()
    {
        return $this->helper->getModuleConfig('sidebar/feature/title') ?: self::TITLE;
    }

    /**
     * @return mixed
     */
    public function showTitle()
    {
        return $this->helper->getModuleConfig('sidebar/feature/show_title');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getFeaturedBrand()
    {
        $featureBrands = [];
        $collection = $this->_brandFactory->create()->getBrandCollection();
        foreach ($collection as $brand) {
            if ($brand->getIsFeatured()) {
                $featureBrands[] = $brand;
            }
        }

        return $featureBrands;
    }
}
