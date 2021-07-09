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
 * @package     Mageplaza_SeoUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoUltimate\Helper;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Mageplaza\Seo\Helper\Data as AbstractHelper;

/**
 * Class Config
 * @package Mageplaza\SeoUltimate\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @param $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHrefLangConfig($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(self::CONFIG_MODULE_PATH . '/hreflang' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnableHrefLang($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getHrefLangConfig('enable', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed|null
     */
    public function isUseDefaultLocale($storeId = null)
    {
        return $this->getHrefLangConfig('use_default_locale', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getXDefault($storeId = null)
    {
        return $this->getHrefLangConfig('x_default', $storeId);
    }

    /**
     * Is enable for product
     *
     * @param null $storeId
     *
     * @return mixed|null
     */
    public function isEnableForProduct($storeId = null)
    {
        return $this->getHrefLangConfig('enable_product', $storeId);
    }

    /**
     * Is Enable for category
     *
     * @param null $storeId
     *
     * @return mixed|null
     */
    public function isEnableForCategory($storeId = null)
    {
        return $this->getHrefLangConfig('enable_category', $storeId);
    }

    /**
     * Is enable for page
     *
     * @param null $storeId
     *
     * @return mixed|null
     */
    public function isEnableForPage($storeId = null)
    {
        return $this->getHrefLangConfig('enable_page', $storeId);
    }

    /**
     * @param $storeId
     *
     * @return array|mixed
     */
    public function getHrefLangByStore($storeId)
    {
        if ($this->getXDefault() == $storeId) {
            return 'x-default';
        }

        if ($this->isUseDefaultLocale($storeId)) {
            $hrefLang = $this->getConfigValue(
                Custom::XML_PATH_GENERAL_LOCALE_CODE,
                $storeId
            );
        } else {
            $hrefLang = $this->getHrefLangConfig('code', $storeId);
        }

        return str_replace('_', '-', $hrefLang);
    }
}
