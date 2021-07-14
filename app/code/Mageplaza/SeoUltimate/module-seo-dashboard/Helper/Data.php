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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Helper;

use Mageplaza\Seo\Helper\Data as AbstractData;

/**
 * Class Data
 * @package Mageplaza\SeoDashboard\Helper
 */
class Data extends AbstractData
{
    /**
     * Fields
     */
    const META_TITLE        = 0;
    const META_DESCRIPTION  = 1;
    const FRONTEND_IDENTIFY = 2;
    const DESCRIPTION       = 3;
    const SHORT_DESCRIPTION = 4;
    const CONTENT           = 5;
    /**
     * Issue type
     */
    const ISSUE_TYPE_DUPLICATE = 1;
    const ISSUE_TYPE_MISSING   = 2;
    /**
     * entity
     */
    const PRODUCT_ENTITY  = 'product';
    const CATEGORY_ENTITY = 'category';
    const PAGE_ENTITY     = 'page';
    /**
     * Min count content
     */
    const SHORT_DESCRIPTION_WORD_COUNT_MINIMUM = 50;
    const DESCRIPTION_WORD_COUNT_MINIMUM       = 150;
    const CONTENT_WORD_COUNT_MINIMUM           = 300;
    /**
     * Status
     */
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR   = 'error';
    /**
     * Dashboard report path
     */
    const XML_PATH_DASHBOARD_REPORT = 'dashboard';

    /**
     * Get config check list
     *
     * @param $storeId
     *
     * @return array
     */
    function getConfigChecklist($storeId)
    {
        $urlSearchEngines = $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/design') . 'store/' . $storeId . '#google_analytics-head';
        if ($this->versionCompare('2.2.0')) {
            $urlSearchEngines = $this->_urlBuilder->getUrl('theme/design_config/edit', ['scope' => 'website']);
        }
        $verifications = [
            'design/search_engine_robots/default_robots' => [
                'INDEX,FOLLOW'     => [
                    'type'    => self::SUCCESS,
                    'message' => __('Your store is listing on Search Engines.'),
                ],
                'NOINDEX,NOFOLLOW' => [
                    'type'    => self::ERROR,
                    'message' => __(
                        'Your current store is discoureaging with Seach Engines, you should allow it by enabling <a href="%1">here</a>',
                        $urlSearchEngines
                    ) //Link to config design/search_engine_robots/default_robots
                ],
                'NOINDEX,FOLLOW'   => [
                    'type'    => self::ERROR,
                    'message' => __(
                        'Your current store is discoureaging with Seach Engines, you should allow it by enabling <a href="%1">here</a>',
                        $urlSearchEngines
                    ) //Link to config design/search_engine_robots/default_robots
                ],
                'INDEX,NOFOLLOW'   => [
                    'type'    => self::WARNING,
                    'message' => __('Your store is listing on Search Engines. But it\'s not influence the ranking of the link\'s target in the search engine\'s index.'),
                ],
            ],
            'web/seo/use_rewrites'                       => [
                1 => [
                    'type'    => self::SUCCESS,
                    'message' => __('Your store is enabling Magento Rewrite feature.'),
                ],
                0 => [
                    'type'    => self::WARNING,
                    'message' => __(
                        'We recommend using <b>Use Web Server Rewrites</b>. Enable it <a href="%1">here</a>',
                        $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/web') . 'store/' . $storeId . '#web_seo-head'
                    ) // Link to config page: web/seo/use_rewrites
                ],
            ],
            'seo/html_sitemap/enable'                    => [
                1 => [
                    'type'    => self::SUCCESS,
                    'message' => __('Your store has HTML Sitemap.'),
                ],
                0 => [
                    'type'    => self::WARNING,
                    'message' => __(
                        'We recommend that you enable HTML sitemap, it makes robots index your store pages faster. Enable it <a href="%1">here</a>',
                        $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/seo') . 'store/' . $storeId . '#seo_htmlsitemap-head'
                    ) // Link to config page: seo/html_sitemap/enable

                ],
            ]
        ];

        return $verifications;
    }

    /**
     * Get field value from column name
     *
     * @param $field
     *
     * @return int|string
     */
    public function getFieldName($field)
    {
        switch ($field) {
            case self::META_TITLE:
                return 'meta_title';
            case self::META_DESCRIPTION:
                return 'meta_description';
            case self::SHORT_DESCRIPTION:
                return 'short_description';
            case self::DESCRIPTION:
            case self::CONTENT:
                return 'description';
            default:
                return 'frontend_identity';
        }
    }

    /**
     * Get filed options
     *
     * @return array
     */
    public function getFieldOptions()
    {
        return [
            self::META_TITLE        => __('Meta Title'),
            self::META_DESCRIPTION  => __('Meta Description'),
            self::FRONTEND_IDENTIFY => __('Frontend Identify'),
            self::DESCRIPTION       => __("Description"),
            self::SHORT_DESCRIPTION => __("Short Description"),
            self::CONTENT           => __("Content")
        ];
    }

    /**
     * Get db report config
     *
     * @param $code
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function getDbReportConfig($code, $storeId = null)
    {
        if (!$this->getModuleConfig(self::XML_PATH_DASHBOARD_REPORT . '/enable', $storeId)) {
            return false;
        }

        return $this->getModuleConfig(self::XML_PATH_DASHBOARD_REPORT . '/' . $code, $storeId);
    }
}
