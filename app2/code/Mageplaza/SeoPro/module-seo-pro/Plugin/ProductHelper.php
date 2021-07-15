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
 * @package     Mageplaza_SeoPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoPro\Plugin;

use Magento\Catalog\Helper\Product;
use Mageplaza\SeoPro\Helper\Config;
use Mageplaza\SeoPro\Helper\Data as HelperConfig;

/**
 * Class ProductHelper
 * @package Mageplaza\SeoPro\Plugin
 */
class ProductHelper
{
    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * ProductHelper constructor.
     *
     * @param HelperConfig $helperConfig
     */
    function __construct(HelperConfig $helperConfig)
    {
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param Product $subject
     * @param $result
     *
     * @return bool
     */
    public function aftercanUseCanonicalTag(Product $subject, $result)
    {
        if ($this->helperConfig->isEnableCanonicalUrl()) {
            return false;
        }

        return $result;
    }
}
