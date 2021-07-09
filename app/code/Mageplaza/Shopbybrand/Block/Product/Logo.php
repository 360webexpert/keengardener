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
     * Get product brand
     *
     * @return mixed|null
     */
    public function getProductBrand()
    {
        if ($this->helper->isEnabled() && $this->helper->getGeneralConfig('show_logo')) {
            return $this->helper->getProductBrand();
        }

        return null;
    }

    /**
     * @return Helper
     */
    public function helper()
    {
        return $this->helper;
    }
}
