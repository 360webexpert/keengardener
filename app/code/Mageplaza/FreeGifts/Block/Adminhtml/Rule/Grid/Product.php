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
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price;
use Magento\Store\Model\Store;

/**
 * Class Product
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid
 */
class Product extends Extended
{
    /**
     * @var ProductCollectionFactory
     */
    protected $_productColFactory;

    /**
     * @var Type
     */
    protected $_type;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductCollectionFactory $productColFactory
     * @param Type $type
     * @param Session $catalogSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductCollectionFactory $productColFactory,
        Type $type,
        Session $catalogSession,
        Registry $registry,
        array $data = []
    ) {
        $this->_productColFactory = $productColFactory;
        $this->_type = $type;
        $this->_catalogSession = $catalogSession;
        $this->_registry = $registry;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mpfreegifts_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setIsCollapsed(false);
    }

    /**
     * @return Extended
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productColFactory->create();
        $gridCollection = $this->assembleProductCollection($collection);
        $this->setCollection($gridCollection);

        return parent::_prepareCollection();
    }

    /**
     * @param ProductCollection $collection
     *
     * @return ProductCollection
     * @throws LocalizedException
     */
    public function assembleProductCollection(ProductCollection $collection)
    {
        $collection->addStoreFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('special_price')
            ->addAttributeToSelect('special_to_date')
            ->addAttributeToSelect('visibility')
            ->addAttributeToFilter('type_id', ['nin' => ['bundle', 'grouped']])
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]]);

        $savedGiftIds = $this->_catalogSession->getGiftIds();

        if ($giftIds = $this->getRequest()->getParam('giftIds')) {
            $giftIds = explode(',', $giftIds);
            $collection->addAttributeToFilter('entity_id', ['nin' => $giftIds]);
            $this->_catalogSession->setGiftIds($giftIds);
        }
        if ($savedGiftIds) {
            $collection->addAttributeToFilter('entity_id', ['nin' => $savedGiftIds]);
        }

        $collection->joinField(
            'quantity',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1 AND {{table}}.website_id=0',
            'left'
        );

        return $collection;
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        /** @var Store $store */
        $store = $this->_storeManager->getStore();

        $this->addColumn('entity_id', [
            'header' => __('Product ID'),
            'type' => 'number',
            'index' => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index' => 'name',
            'type' => 'text',
            'sortable' => true
        ]);
        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index' => 'sku',
            'type' => 'text',
            'sortable' => true
        ]);
        $this->addColumn('quantity', [
            'header' => __('Quantity'),
            'type' => 'number',
            'index' => 'quantity'
        ]);
        $this->addColumn('price', [
            'header' => __('Price'),
            'column_css_class' => 'price',
            'type' => 'currency',
            'currency_code' => $store->getBaseCurrencyCode(),
            'index' => 'price',
            'renderer' => Price::class
        ]);
        $this->addColumn('type', [
            'header' => __('Type'),
            'index' => 'type_id',
            'type' => 'options',
            'options' => $this->getTypeOptionArray()
        ]);
        $this->addColumn('special_price', [
            'header' => __('Special Price'),
            'index' => 'special_price',
            'type' => 'currency',
            'currency_code' => $store->getBaseCurrencyCode(),
            'renderer' => Price::class
        ]);
        $this->addColumn('special_to_date', [
            'header' => __('Special To Date'),
            'index' => 'special_to_date',
            'type' => 'datetime',
        ]);
        $this->addColumn('visibility', [
            'header' => __('Visibility'),
            'index' => 'visibility',
            'type' => 'options',
            'options' => Visibility::getOptionArray(),
        ]);
        $this->addColumn('dummy_input', [
            'header' => __('Dummy Input'),
            'name' => 'dummy_input',
            'header_css_class' => 'hidden',
            'column_css_class' => 'hidden',
            'editable' => true
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    public function getTypeOptionArray()
    {
        $types = $this->_type->getOptionArray();

        if (isset($types['bundle'])) {
            unset($types['bundle']);
        }
        if (isset($types['grouped'])) {
            unset($types['grouped']);
        }

        return $types;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpfreegifts/rule_actions/product', ['form_key' => $this->getFormKey()]);
    }

    /**
     * @param object $row
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @return $this|Extended
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('mpfreegifts_product_ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'add',
            [
                'label' => __('Add'),
                'url' => $this->getUrl('mpfreegifts/rule_actions/addGift', [
                    'ruleId' => $this->getRequest()->getParam('ruleId')
                ]),
                'complete' => 'reloadGiftListing'
            ]
        );

        return $this;
    }
}
