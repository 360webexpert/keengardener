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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Helper;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Mageplaza\Seo\Helper\Data as AbstractData;

/**
 * Class Data
 * @package Mageplaza\Redirects\Helper
 */
class Data extends AbstractData
{
    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigRedirect($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/redirects' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isRedirectEnabled($storeId = null)
    {
        return $this->getConfigRedirect('enabled', $storeId);
    }

    /**Is enable better 404 page
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnableBetter404page($storeId = null)
    {
        if ($this->isRedirectEnabled()) {
            return $this->getConfigRedirect('better_404_page', $storeId);
        }

        return false;
    }

    /**
     * Get product url suffix
     * @return mixed
     */
    public function getProductUrlSuffix()
    {
        return $this->getConfigValue(ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX);
    }
}
