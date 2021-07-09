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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoPro\Helper;

use Mageplaza\Seo\Helper\Data as AbstractHelper;

/**
 * Class Config
 * @package Mageplaza\SeoPro\Helper
 */
class Data extends AbstractHelper
{
    /** Canonical url configuration path */
    const CANONICAL_URL_CONFIGUARATION = 'canonical_url';

    /**
     * Is enable Canonical Url
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnableCanonicalUrl($storeId = null)
    {
        return $this->isEnabled() && $this->getConfigValue(
            self::CONFIG_MODULE_PATH . '/' . self::CANONICAL_URL_CONFIGUARATION . '/enable_canonical_url',
            $storeId
        );
    }

    /**
     * Is disable Canonical Url with no index robots
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isDisableCanonicalUrlWithNoIndexRobots($storeId = null)
    {
        return $this->getConfigValue(
            self::CONFIG_MODULE_PATH . '/' . self::CANONICAL_URL_CONFIGUARATION . '/disable_canonical_with_noindex_robots',
            $storeId
        );
    }

    /**
     * Get disable canonical pages from config
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDisableCanonicalPages($storeId = null)
    {
        return array_map('trim', explode(
            "\n",
            $this->getConfigValue(
                self::CONFIG_MODULE_PATH . '/' . self::CANONICAL_URL_CONFIGUARATION . '/disable_canonical_pages',
                $storeId
            )
        ));
    }
}
