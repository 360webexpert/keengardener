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

namespace Mageplaza\SeoDashboard\Plugin;

use DOMDocument;
use DOMXpath;
use Mageplaza\SeoDashboard\Helper\Data;

/**
 * Class Dashboard
 * @package Mageplaza\SeoDashboard\Plugin
 */
class Dashboard
{
    /**
     * @type Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param Data $helper
     */
    function __construct(Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * After to html - Add Mageplaza Seo Dashboard Grids
     *
     * @param \Magento\Backend\Block\Dashboard $subject
     * @param $result
     *
     * @return string
     */
    public function afterToHtml(\Magento\Backend\Block\Dashboard $subject, $result)
    {
        if ($this->_helper->isEnabled() && $this->_helper->getDbReportConfig('dashboard_enable')) {
            $dom    = new DOMDocument();
            $result = mb_convert_encoding($result, 'HTML-ENTITIES', 'utf-8');
            $dom->loadHTML($result);
            $xpath = new DOMXpath($dom);

            $query = $xpath->query('//div[@class="dashboard-store-stats"]');
            if ($query->length > 0) {
                $template = $dom->createDocumentFragment();

                $mpSeoDbGrids = '<div class="mp-seo-db-head dashboard-item-title">' . __('Mageplaza SEO Report') . '</div>'
                    . $subject->getChildHtml('mpSeoDbGrids')
                    . '<div id="mp_seo_db_grid_tab_content" class="dashboard-store-stats-content"></div>';

                $template->appendXML($mpSeoDbGrids);
                $query->item(0)->appendChild($template);
                $result = $dom->saveHTML();
            }
        }

        return $result;
    }
}
