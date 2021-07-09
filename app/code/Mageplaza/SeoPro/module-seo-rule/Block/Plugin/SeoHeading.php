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

namespace Mageplaza\SeoRule\Block\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Theme\Block\Html\Title;
use Mageplaza\SeoRule\Helper\Data as HelperConfig;

/**
 * Class SeoBeforeRender
 * @package Mageplaza\Seo\Plugin
 */
class SeoHeading
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * SeoHeading constructor.
     *
     * @param Http $request
     * @param HelperConfig $helpConfig
     * @param Registry $registry
     */
    function __construct(
        Http $request,
        HelperConfig $helpConfig,
        Registry $registry
    ) {
        $this->request      = $request;
        $this->helperConfig = $helpConfig;
        $this->registry     = $registry;
    }

    /**
     * @param Title $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetPageHeading(Title $subject, $result)
    {
        if (($this->getFullActionName() == 'catalog_product_view') && $this->helperConfig->isUseSeoNameProduct()) {
            if ($this->getCurrentProduct()->getMpProductSeoName()) {
                $result = $this->getCurrentProduct()->getMpProductSeoName();
            }
        } elseif (($this->getFullActionName() == 'catalog_category_view') && $this->helperConfig->isUseSeoNameCategory()) {
            if ($this->getCurrentCategory()->getMpCategorySeoName()) {
                $result = $this->getCurrentCategory()->getMpCategorySeoName();
            }
        }

        return $result;
    }

    /**
     * Get full action name
     * @return string
     */
    public function getFullActionName()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get current Category
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get current product
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}
