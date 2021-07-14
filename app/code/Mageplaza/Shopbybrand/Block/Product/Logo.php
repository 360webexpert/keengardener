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

namespace Mageplaza\Shopbybrand\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\Config\Source\ShowBrandInfo;

/**
 * Class Logo
 * @package Mageplaza\Shopbybrand\Block\Product
 */
class Logo extends Template
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Logo constructor.
     *
     * @param Context $context
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        Helper $helper
    ) {
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return mixed|null
     */
    public function getBrandObject()
    {
        if (!$this->helper->isEnabled() || in_array(ShowBrandInfo::NOT_SHOW, $this->showBrandInfo(), true)) {
            return null;
        }

        return $this->helper->getBrandObject();
    }

    /**
     * @return Helper
     */
    public function helper()
    {
        return $this->helper;
    }

    /**
     * @return false|string[]
     */
    public function showBrandInfo()
    {
        return explode(',', $this->helper->getConfigGeneral('show_brand_info'));
    }

    /**
     * @return mixed
     */
    public function getLogoWidth()
    {
        return $this->helper->getConfigGeneral('logo_width_on_product_page');
    }

    /**
     * @return mixed
     */
    public function getLogoHeight()
    {
        return $this->helper->getConfigGeneral('logo_height_on_product_page');
    }
}
