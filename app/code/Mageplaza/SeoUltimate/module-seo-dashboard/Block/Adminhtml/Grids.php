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

namespace Mageplaza\SeoDashboard\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\Widget\Tabs;

/**
 * Class Grids
 * @package Mageplaza\SeoDashboard\Block\Adminhtml
 */
class Grids extends Tabs
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mp_seo_db_grid_tab');
        $this->setDestElementId('mp_seo_db_grid_tab_content');
    }

    /**
     * Prepare layout for dashboard bottom tabs
     *
     * To load block statically:
     *     1) content must be generated
     *     2) url should not be specified
     *     3) class should not be 'ajax'
     * To load with ajax:
     *     1) do not load content
     *     2) specify url (BE CAREFUL)
     *     3) specify class 'ajax'
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareLayout()
    {
        // load this active tab statically
        $this->addTab('duplicate_content', [
            'label'  => __('Duplicate Content'),
            'url'    => $this->getUrl('seo/dashboard/duplicateContent', ['_current' => true]),
            'class'  => 'ajax',
            'active' => true
        ]);

        // load other tabs with ajax
        $this->addTab('missing_meta_data', [
            'label' => __('Missing Meta Data'),
            'url'   => $this->getUrl('seo/dashboard/missingMetaData', ['_current' => true]),
            'class' => 'ajax'
        ]);

        $this->addTab('low_word_count', [
            'label' => __('Low Word Count'),
            'url'   => $this->getUrl('seo/dashboard/lowWordsCount', ['_current' => true]),
            'class' => 'ajax'
        ]);

        $this->addTab('wrong_pages', [
            'label' => __('404 Pages'),
            'url'   => $this->getUrl('seo/dashboard/noRoute', ['_current' => true]),
            'class' => 'ajax'
        ]);

        return parent::_prepareLayout();
    }
}
