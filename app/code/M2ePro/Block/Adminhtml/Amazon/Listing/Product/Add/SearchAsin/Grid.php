<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Add\SearchAsin;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Add\SearchAsin\Grid
 */
class Grid extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    const SEARCH_SETTINGS_STATUS_NONE = 'none';
    const SEARCH_SETTINGS_STATUS_COMPLETED = 'completed';

    /** @var \Ess\M2ePro\Model\Listing */
    private $listing = null;

    protected $magentoProductCollectionFactory;
    protected $amazonFactory;

    protected $lockedDataCache = [];

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->amazonFactory = $amazonFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->listing = $this->amazonFactory->getCachedObjectLoaded('Listing', $this->getRequest()->getParam('id'));

        // Initialization block
        // ---------------------------------------
        $this->setId('searchAsinForListingProductsGrid'.$this->listing['id']);
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingProductsIds = $this->listing->getSetting('additional_data', 'adding_listing_products_ids');

        // Get collection
        // ---------------------------------------
        /** @var $collection \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection */
        $collection = $this->magentoProductCollectionFactory->create();

        $collection
            ->setListing($this->listing)
            ->setStoreId($this->listing['store_id'])
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');

        $collection->joinStockItem();

        // ---------------------------------------
        $lpTable = $this->activeRecordFactory->getObject('Listing\Product')->getResource()->getMainTable();
        $collection->joinTable(
            ['lp' => $lpTable],
            'product_id=entity_id',
            [
                'id'              => 'id',
                'component_mode'  => 'component_mode',
                'amazon_status'   => 'status',
                'additional_data' => 'additional_data'
            ],
            '{{table}}.listing_id='.(int)$this->listing['id']
        );

        $alpTable = $this->activeRecordFactory->getObject('Amazon_Listing_Product')->getResource()->getMainTable();
        $collection->joinTable(
            ['alp' => $alpTable],
            'listing_product_id=id',
            [
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_regular_price'           => 'online_regular_price',
                'online_regular_sale_price'      => 'online_regular_sale_price',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
            ],
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->getSelect()->where('lp.id IN (?)', $listingProductsIds);

        // ---------------------------------------

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header'   => $this->__('Product ID'),
            'align'    => 'right',
            'width'    => '100px',
            'type'     => 'number',
            'index'    => 'entity_id',
            'filter_index' => 'entity_id',
            'store_id' => $this->listing->getStoreId(),
            'renderer' => '\Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId'
        ]);

        $this->addColumn('name', [
            'header'    => $this->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'escape'       => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle']
        ]);

        $this->addColumn('general_id', [
            'header' => $this->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => [$this, 'callbackColumnGeneralId']
        ]);

        if ($this->listing->getChildObject()->isGeneralIdAttributeMode() ||
            $this->listing->getChildObject()->isWorldwideIdAttributeMode()) {
            $this->addColumn('settings', [
                'header' => $this->__('Search Settings Values'),
                'align' => 'left',
                'width' => '240px',
                'filter'    => false,
                'sortable'  => false,
                'type' => 'text',
                'index' => 'id',
                'frame_callback' => [$this, 'callbackColumnSettings']
            ]);
        }

        $this->addColumn('status', [
            'header' => $this->__('Status'),
            'width' => '200px',
            'index' => 'search_settings_status',
            'filter_index' => 'search_settings_status',
            'sortable'  => false,
            'type' => 'options',
            'options' => [
                self::SEARCH_SETTINGS_STATUS_NONE => $this->__('None'),
                \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS =>
                    $this->__('In Progress'),
                \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_NOT_FOUND =>
                    $this->__('Not Found'),
                \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED =>
                    $this->__('Action Required'),
                self::SEARCH_SETTINGS_STATUS_COMPLETED => $this->__('Completed')
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
            'filter_condition_callback' => [$this, 'callbackFilterStatus']
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('assignGeneralId', [
            'label'    => $this->__('Search ASIN/ISBN automatically'),
            'url'      => ''
        ]);

        $this->getMassactionBlock()->addItem('unassignGeneralId', [
            'label'    => $this->__('Reset ASIN/ISBN information'),
            'url'      => ''
        ]);
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = $this->getHelper('Data')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        $tempSku = $row->getData('sku');
        $tempSku === null
        && $tempSku = $this->modelFactory->getObject('Magento\Product')
            ->setProductId($row->getData('entity_id'))
            ->getSku();

        $value .= '<br/><strong>'.$this->__('SKU') .
            ':</strong> '.$this->getHelper('Data')->escapeHtml($tempSku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $listingProductId);

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if (!$variationManager->isRelationParentType()) {
            return $value;
        }

        $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();

        $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
        $value .= implode(', ', $productAttributes);
        $value .= '</div>';

        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnSettings($id, $row, $column, $isExport)
    {
        $value = '';
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $listingProduct */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $id)->getChildObject();

        if ($this->listing->getChildObject()->isGeneralIdAttributeMode()) {
            $attrValue = $listingProduct->getListingSource()->getSearchGeneralId();

            if (empty($attrValue)) {
                $attrValue = $this->__('Not set');
            } elseif (!$this->getHelper('Component\Amazon')->isASIN($attrValue) &&
                        !$this->getHelper('Data')->isISBN($attrValue)) {
                $attrValue = $this->__('Inappropriate value');
            }

            $value .= '<b>' . $this->__('ASIN/ISBN') . '</b>: ' . $attrValue . '<br/>';
        }

        if ($this->listing->getChildObject()->isWorldwideIdAttributeMode()) {
            $attrValue = $listingProduct->getListingSource()->getSearchWorldwideId();

            if (empty($attrValue)) {
                $attrValue = $this->__('Not Set');
            } elseif (!$this->getHelper('Data')->isUPC($attrValue) && !$this->getHelper('Data')->isEAN($attrValue)) {
                $attrValue = $this->__('Inappropriate value');
            }

            $value .= '<b>' . $this->__('UPC/EAN') . '</b>: ' . $attrValue;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $generalId = $row->getData('general_id');
        $searchSettingsStatus = $row->getData('search_settings_status');
        $style = 'display: inline-block; vertical-align: middle; line-height: 30px;';

        if (empty($generalId) && empty($searchSettingsStatus)) {
            $msg = $this->__('None');
            $tip = $this->__('The Search of Product was not performed yet');

            return <<<HTML
<span style="color: gray; {$style}">{$msg}</span>&nbsp;
{$this->getTooltipHtml($tip)}
HTML;
        }

        switch ($searchSettingsStatus) {
            case \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS:
                $searchData = $this->getHelper('Data')->jsonDecode($row->getData('search_settings_data'));

                $msg = $this->__('In Progress');
                $tip = $this->__(
                    'The Search is being performed now by %type% "%value%"',
                    $this->prepareSearchType($searchData['type']),
                    $searchData['value']
                );

                return <<<HTML
<span style="color: orange; {$style}">{$msg}</span>&nbsp;
{$this->getTooltipHtml($tip)}
HTML;

            case \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_NOT_FOUND:
                $msg = $this->__('Product was not found');
                $tip = $this->__('There are no Products found on Amazon after the Automatic Search
                                                   was performed according to Listing Search Settings.');

                return <<<HTML
<span style="color: red; {$style}">{$msg}</span>&nbsp;
{$this->getTooltipHtml($tip)}
HTML;
            case \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED:
                $searchData = $this->getHelper('Data')->jsonDecode($row->getData('search_settings_data'));

                $lpId = $row->getData('id');

                $productTitle = $row->getData('name');
                if (strlen($productTitle) > 60) {
                    $productTitle = substr($productTitle, 0, 60) . '...';
                }
                $productTitle = $this->getHelper('Data')->escapeHtml($productTitle);

                $productTitle = $this->__(
                    'Search ASIN/ISBN For &quot;%product_title%&quot;',
                    $productTitle
                );
                $productTitle = $this->getHelper('Data')->escapeJs($productTitle);

                $linkTxt = $this->__('choose one of the Results');

                $linkHtml = <<<HTML
<a href="javascript:void(0)"
    onclick="ListingGridObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">{$linkTxt}</a>
HTML;

                $msg = $this->__('Action Required');
                $tip = $this->__(
                    'Please %link% that were found by %type% "%value%"',
                    $linkHtml,
                    $this->prepareSearchType($searchData['type']),
                    $searchData['value']
                );

                return <<<HTML
<span style="color: orange; {$style}">{$msg}</span>&nbsp;
{$this->getTooltipHtml($tip)}
HTML;
        }

        $searchInfo = $this->getHelper('Data')->jsonDecode($row->getData('general_id_search_info'));

        $msg = $this->__('Completed');
        $tip = $this->__(
            'Product was found by %type% "%value%"',
            $this->prepareSearchType($searchInfo['type']),
            $searchInfo['value']
        );

        return <<<HTML
<span style="color: green; {$style}">{$msg}</span>&nbsp;
{$this->getTooltipHtml($tip)}
HTML;
    }

    private function prepareSearchType($searchType)
    {
        if ($searchType == 'string') {
            return 'query';
        }

        return strtoupper($searchType);
    }

    //########################################

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        $lpId = $row->getData('id');

        $productTitle = $row->getData('name');
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }
        $productTitle = $this->getHelper('Data')->escapeHtml($productTitle);

        $productTitle = $this->__('Search ASIN/ISBN For &quot;%product_title%&quot;', $productTitle);
        $productTitle = $this->getHelper('Data')->escapeJs($productTitle);
        // ---------------------------------------

        // ---------------------------------------

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {
            $tip = $this->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $this->getViewFileUrl('Ess_M2ePro::images/search_statuses/processing.gif');

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt="">
</a>
HTML;
        }
        // ---------------------------------------

        // ---------------------------------------
        if ($searchSettingsStatus == \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_ACTION_REQUIRED) {
            $linkTxt = $this->__('Choose ASIN/ISBN');

            return <<<HTML
<a href="javascript:;" title="{$linkTxt}"
   onclick="ListingGridObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">{$linkTxt}</a>
HTML;
        }
        // ---------------------------------------

        $na = $this->__('N/A');
        $tip = $this->__('Search for ASIN/ISBN');

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}" class="amazon-listing-view-icon amazon-listing-view-generalId-search"
   onclick="ListingGridObj.productSearchHandler.showSearchManualPrompt('{$productTitle}',{$lpId});">
</a>
HTML;
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');
        $marketplaceId = $this->listing->getMarketplaceId();

        $url = $this->getHelper('Component\Amazon')->getItemUrl(
            $generalId,
            $marketplaceId
        );

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = $this->getHelper('Data')->jsonDecode($generalIdSearchInfo);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {
            $tip = $this->__('ASIN/ISBN was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;
        } else {
            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text;
        }

        $listingProductId = (int)$row->getData('id');

        $tip = $this->__('Unassign ASIN/ISBN');

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    class="amazon-listing-view-icon amazon-listing-view-generalId-remove"
    onclick="ListingGridObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
    title="{$tip}">
</a>
HTML;

        return $text;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute'=>'sku','like'=>'%'.$value.'%'],
                ['attribute'=>'name', 'like'=>'%'.$value.'%']
            ]
        );
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if ($value == self::SEARCH_SETTINGS_STATUS_NONE) {
            $collection->addFieldToFilter('general_id', ['null' => null]);
            $collection->addFieldToFilter('search_settings_status', ['null' => null]);
            return;
        }

        if ($value == self::SEARCH_SETTINGS_STATUS_COMPLETED) {
            $collection->addFieldToFilter(
                [
                    ['attribute'=>'general_id', 'notnull' => null]
                ]
            );

            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'search_settings_status', 'eq' => $value]
            ]
        );
    }

    protected function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->lockedDataCache[$listingProductId])) {
            $objectLocks = $this->activeRecordFactory->getObjectLoaded('Listing\Product', $listingProductId)
                ->getProcessingLocks();
            $tempArray = [
                'object_locks' => $objectLocks,
                'in_action'    => !empty($objectLocks),
            ];
            $this->lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->lockedDataCache[$listingProductId];
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
    ListingGridObj.afterInitPage();
JS
            );

            return parent::_toHtml();
        }

        $showNotCompletedPopup = '';
        if ($this->getRequest()->getParam('not_completed', false)) {
            $showNotCompletedPopup = 'ListingGridObj.showNotCompletedPopup();';
        }

        $this->js->add(<<<JS
    require([
        'M2ePro/Amazon/Listing/Product/Add/SearchAsin/Grid'
    ],function() {

        ListingGridObj = new AmazonListingProductAddSearchAsinGrid(
            '{$this->getId()}',
            {$this->listing->getId()}
        );

        ListingGridObj.actionHandler.setProgressBar('search_asin_progress_bar');
        ListingGridObj.actionHandler.setGridWrapper('search_asin_content_container');
        ListingGridObj.afterInitPage();

        {$showNotCompletedPopup}
    });
JS
        );

        if (!$this->listing->getChildObject()->isGeneralIdAttributeMode() &&
            !$this->listing->getChildObject()->isWorldwideIdAttributeMode()) {
            if (!$this->listing->getChildObject()->isSearchByMagentoTitleModeEnabled()) {
                $gridId = $this->getId();

                $this->js->add(
                    <<<JS
    var mmassActionEl = $("{$gridId}_massaction-select");

    if (mmassActionEl &&  mmassActionEl.select('option[value="assignGeneralId"]').length > 0) {
        var assignGeneralIdOption = mmassActionEl.select('option[value="assignGeneralId"]')[0];
        assignGeneralIdOption.disabled = true;

        mmassActionEl.insert({bottom: assignGeneralIdOption.remove()});
    }
JS
                );
            }
        } else {
            $autoSearchSetting = $this->listing->getSetting('additional_data', 'auto_search_was_performed');

            if (!$autoSearchSetting) {
                $this->listing->setSetting('additional_data', 'auto_search_was_performed', 1);
                $this->listing->save();

                $this->js->add(
                    <<<JS
require([
    'M2ePro/Amazon/Listing/Product/Add/SearchAsin/Grid'
],function() {
    ListingGridObj.getGridMassActionObj().selectAll();
    ListingGridObj.productSearchHandler.searchGeneralIdAuto(ListingGridObj.getSelectedProductsString());
});
JS
                );
            }
        }

        return '<div id="search_asin_progress_bar"></div>' .
                '<div id="search_asin_content_container">' .
                parent::_toHtml() .
                '</div>';
    }

    //########################################
}
