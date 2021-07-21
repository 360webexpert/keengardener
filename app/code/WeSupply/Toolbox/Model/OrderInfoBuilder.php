<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory as PurchasedCollectionFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory as PurchasedItemCollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Api\OrderInfoBuilderInterface;
use WeSupply\Toolbox\Helper\Data;
use WeSupply\Toolbox\Helper\WeSupplyMappings;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class OrderInfoBuilder
 * @package WeSupply\Toolbox\Model
 */
class OrderInfoBuilder implements OrderInfoBuilderInterface
{
    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var Attribute
     */
    protected $productAttr;

    /**
     * @var AttributeInterface
     */
    protected $attributeInterface;

    /**
     * @var string
     * url to media directory
     */
    protected $mediaUrl;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     * order status label
     */
    protected $orderStatusLabel;

    /**
     * @var array
     */
    protected $weSupplyStatusMappedArray;

    /**
     * @var WeSupplyMappings
     */
    protected $weSupplyMappings;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Data
     */
    private $_helper;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var Repository
     */
    private $assetRepos;

    /**
     * @var ImageFactory
     */
    private $helperImageFactory;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * Product image subdirectory
     * @var string
     */
    const PRODUCT_IMAGE_SUBDIRECTORY = 'catalog/product/';

    /**
     * Used as prefix for wesupply order id
     * to avoid duplicate id with other providers (aptos)
     * @var string
     */
    const PREFIX = 'mage_';

    /**
     * Products excluded from export that cannot be tracked
     * @var array
     */
    const EXCLUDED_ITEMS
        = [
            1 => DownloadableType::TYPE_DOWNLOADABLE,
            2 => ProductType::TYPE_VIRTUAL
        ];

    /**
     * Product attributes whose value must remain as they were
     * when placing the order
     * @var array
     */
    const DO_NOT_UPDATE =
        [
            'ItemImageUri',
            'ItemProductUri',
            'OptionHidden',
            'ItemWeight',
            'ItemWidth',
            'ItemHeight',
            'ItemLength',
            'ItemWeightUnit',
            'ItemMeasureUnit'
        ];

    /**
     * @var PurchasedCollectionFactory
     */
    private $linksFactory;

    /**
     * @var PurchasedItemCollectionFactory
     */
    private $itemsFactory;

    /**
     * @var int
     */
    private $availableDownloadableItems;

    /**
     * OrderInfoBuilder constructor.
     *
     * @param ProductRepositoryInterface     $productRepositoryInterface
     * @param ImageFactory                   $helperImageFactory
     * @param CustomerRepositoryInterface    $customer
     * @param CountryFactory                 $countryFactory
     * @param AttributeInterface             $attributeInterface
     * @param Attribute                      $productAttr
     * @param SearchCriteriaBuilder          $searchCriteriaBuilder
     * @param ManagerInterface               $eventManager
     * @param Filesystem                     $filesystem
     * @param Reader                         $moduleReader
     * @param TimezoneInterface              $timezone
     * @param Repository                     $assetRepos
     * @param ShipmentRepositoryInterface    $shipmentRepository
     * @param StoreManagerInterface          $storeManagerInterface
     * @param Data                           $helper
     * @param WeSupplyMappings               $weSupplyMappings
     * @param Logger                         $logger
     * @param PurchasedCollectionFactory     $linksFactory
     * @param PurchasedItemCollectionFactory $itemsFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface,
        ImageFactory $helperImageFactory,
        CustomerRepositoryInterface $customer,
        CountryFactory $countryFactory,
        AttributeInterface $attributeInterface,
        Attribute $productAttr,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $eventManager,
        Filesystem $filesystem,
        Reader $moduleReader,
        TimezoneInterface $timezone,
        Repository $assetRepos,
        ShipmentRepositoryInterface $shipmentRepository,
        StoreManagerInterface $storeManagerInterface,
        Data $helper,
        WeSupplyMappings $weSupplyMappings,
        Logger $logger,
        PurchasedCollectionFactory $linksFactory,
        PurchasedItemCollectionFactory $itemsFactory
    ) {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->helperImageFactory = $helperImageFactory;
        $this->customer = $customer;
        $this->countryFactory = $countryFactory;
        $this->attributeInterface = $attributeInterface;
        $this->productAttr = $productAttr;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eventManager = $eventManager;
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
        $this->timezone = $timezone;
        $this->assetRepos = $assetRepos;
        $this->shipmentRepository = $shipmentRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->_helper = $helper;
        $this->weSupplyMappings = $weSupplyMappings;
        $this->logger = $logger;
        $this->linksFactory = $linksFactory;
        $this->itemsFactory = $itemsFactory;

        $this->weSupplyStatusMappedArray = $weSupplyMappings->mapOrderStateToWeSupplyStatus();
    }

    /**
     * @param $order
     * @param $existingOrderData
     * @return array|bool|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function gatherInfo($order, $existingOrderData)
    {
        $orderData = $order->getData();
        $this->orderStatusLabel = ucfirst($orderData['status']);

        $carrierCode = '';
        $supplierCode = $this->_helper->recursivelyGetArrayData(['store_id'], $orderData);
        if ($shippingMethod = $order->getShippingMethod()) {
            $shippingMethodArr = explode('_', $shippingMethod);
            $carrierCode = reset($shippingMethodArr);
            if (isset($this->weSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode])) {
                $carrierCode = $this->weSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode];
            }
            if ($extAttrs = $order->getExtensionAttributes()) {
                $extAttrsArr = $extAttrs->__toArray();
                if (isset($extAttrsArr['pickup_location_code'])) {
                    $supplierCode = $extAttrsArr['pickup_location_code'];
                }
            }
        }

        $orderData['carrier_code'] = $carrierCode;
        $orderData['supplier_code'] = $supplierCode;
        $orderData['wesupply_updated_at'] = date('Y-m-d H:i:s');

        unset($orderData['extension_attributes']);
        unset($orderData['items']);

        /** Gather order items information */
        $i = 0;
        $items = $order->getItems();
        foreach ($items as $item) {
            $itemData = $item->getData();
            if (isset($itemData['parent_item'])) { // that means it is a simple associated product
                if (array_key_exists($i-1, $orderData['OrderItems'])) { // try to get and set product cost
                    $orderData['OrderItems'][$i - 1]['base_cost'] = $this->_helper->recursivelyGetArrayData(['base_cost'], $itemData, 0);
                }

                continue;
            }

            unset($itemData['has_children']);
            $orderData['OrderItems'][] = $itemData;

            $i++;
        }

        /** Set billing and shipping Address */
        $billingAddressData = $order->getBillingAddress()->getData();
        $orderData['billingAddressInfo'] = $billingAddressData;

        /** Downloadable product order have no shipping address */
        $shippingAdressData = $billingAddressData;
        if ($order->getShippingAddress()) {
            $shippingAdressData = $order->getShippingAddress()->getData();
        }
        $orderData['shippingAddressInfo'] = $shippingAdressData;

        /** Gather the shipments and trackings information */
        $shipmentTracks = [];
        $shipmentData = [];
        $shipmentCollection = $order->getShipmentsCollection();

        $inventorySourcesByItemIds = $this->_fetchInventorySourcesByItems($orderData);
        if ($shipmentCollection->getSize()) {
            foreach ($shipmentCollection->getItems() as $shipment) {
                $tracks = $shipment->getTracksCollection();

                foreach ($tracks->getItems() as $track) {
                    $trackId = $track->getParentId();
                    $shipmentTracks[$trackId]['track_number'] = $track['track_number'];
                    $shipmentTracks[$trackId]['title'] = $track['title'];
                    $shipmentTracks[$trackId]['carrier_code'] = $track['carrier_code'];
                }

                $sItems = $shipmentItems = $shipment->getItemsCollection();
                if (method_exists($shipmentItems, 'getItems')) {
                    $sItems = $shipmentItems->getItems();
                }
                foreach ($sItems as $shipmentItem) {
                    /** Default empty values for non existing tracking */
                    $shipmentId = $shipmentItem->getParentId();
                    if (!isset($shipmentTracks[$shipmentId])) {
                        $shipmentTracks[$shipmentId]['track_number'] = '';
                        $shipmentTracks[$shipmentId]['title'] = '';
                        $shipmentTracks[$shipmentId]['carrier_code'] =
                            $this->_helper->recursivelyGetArrayData(['carrier_code'], $orderData);
                    }

                    $shipmentTracks[$shipmentId]['inventory_source'] =
                        !empty($inventorySourcesByItemIds) && isset($inventorySourcesByItemIds[$shipmentId]) ?
                            $inventorySourcesByItemIds[$shipmentId] :
                            $this->_helper->recursivelyGetArrayData(['store_id'], $orderData);

                    $shipmentData[$shipmentItem['order_item_id']][] = array_merge(
                        [
                            'qty' => $shipmentItem['qty'],
                            'sku' => $shipmentItem['sku']
                        ],
                        $shipmentTracks[$shipmentId]
                    );
                }
            }
        }

        $orderData['shipmentTracking'] = $shipmentData;

        /** Set payment data */
        $paymentData = $order->getPayment()->getData();
        $orderData['paymentInfo'] = $paymentData;

        $this->eventManager->dispatch(
            'wesupply_order_gather_info_after',
            ['orderData' => $orderData]
        );

        return $this->mapFieldsForWesupplyStructure($orderData, $existingOrderData);
    }

    /**
     * Prepares the order information for db storage
     * @param array $orderData
     * @return mixed|string
     */
    public function prepareForStorage($orderData)
    {
        return $this->convertInfoToXml($orderData);
    }

    /**
     * Returns the order last updated time
     * @param array $orderData
     * @return mixed|string
     */
    public function getUpdatedAt($orderData)
    {
        return $orderData['OrderModified'];
    }

    /**
     * Return the store id from the order information array
     * @param array $orderData
     * @return int|mixed
     */
    public function getStoreId($orderData)
    {
        return $orderData['StoreId'];
    }

    /**
     * Return the order number from the order information array
     * @param array $orderData
     * @return int
     */
    public function getOrderNumber($orderData)
    {
        return $orderData['OrderNumber'];
    }

    /**
     * @param $date
     * @return false|string
     */
    protected function modifyToLocalTimezone($date)
    {
        if ($date) {
            try {
                $formattedDate = $this->timezone->formatDateTime(
                    $date,
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::MEDIUM,
                    null,
                    null,
                    'yyyy-MM-dd HH:mm:ss'
                );
            } catch (\Exception $e) {
                $this->logger->error("WeSupply Error when changing date to local timezone:" . $e->getMessage());
                return false;
            }
        }

        return $formattedDate ?? date('Y-m-d H:i:s');
    }

    /**
     * @param $orderData
     * @param $existingOrderData
     * @return array|bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function mapFieldsForWesupplyStructure($orderData, $existingOrderData)
    {
        $finalOrderData = [];
        $this->mapOrderStateToWeSupply($orderData, $finalOrderData);
        $finalOrderData['StoreId'] = $this->_helper->recursivelyGetArrayData(['store_id'], $orderData);
        $finalOrderData['OrderID'] = self::PREFIX . $this->_helper->recursivelyGetArrayData(['entity_id'], $orderData);
        $finalOrderData['OrderNumber'] = $this->_helper->recursivelyGetArrayData(['increment_id'], $orderData);
        $finalOrderData['OrderExternalOrderID'] = $this->_helper->recursivelyGetArrayData(['increment_id'], $orderData);
        $finalOrderData['OrderDate'] = $this->modifyToLocalTimezone($orderData['created_at']);
        $finalOrderData['OrderModified'] = $this->_helper->recursivelyGetArrayData(['wesupply_updated_at'], $orderData);
        $finalOrderData['LastModifiedDate'] = $this->modifyToLocalTimezone($orderData['updated_at']);
        $finalOrderData['FirstName'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'firstname'], $orderData);
        $finalOrderData['LastName'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'lastname'], $orderData);
        $finalOrderData['OrderContact'] = $finalOrderData['FirstName'] . ' ' . $finalOrderData['LastName'];
        $finalOrderData['OrderShippingAddress1'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'street'], $orderData);
        $finalOrderData['OrderShippingCity'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'city'], $orderData);
        $finalOrderData['OrderShippingStateProvince'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'region'], $orderData);
        $finalOrderData['OrderShippingCountry'] = $this->getCountryName($this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'country_id'], $orderData));
        $finalOrderData['OrderShippingCountryCode'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'country_id'], $orderData);
        $finalOrderData['OrderShippingZip'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'postcode'], $orderData);
        $finalOrderData['OrderShippingPhone'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'telephone'], $orderData);
        $finalOrderData['OrderAmount'] = $this->_helper->recursivelyGetArrayData(['base_subtotal'], $orderData);
        $finalOrderData['OrderAmountShipping'] = $this->_helper->recursivelyGetArrayData(['base_shipping_amount'], $orderData);
        $finalOrderData['OrderAmountTax'] = $this->_helper->recursivelyGetArrayData(['base_tax_amount'], $orderData);
        $finalOrderData['OrderAmountTotal'] = $this->_helper->recursivelyGetArrayData(['base_grand_total'], $orderData);
        $finalOrderData['OrderAmountCoupon'] = number_format(0, 4, '.', '');
        $finalOrderData['OrderAmountGiftCard'] = $this->_helper->recursivelyGetArrayData(['base_gift_cards_amount'], $orderData, '0.0000');
        $finalOrderData['OrderPaymentTypeId'] = '';
        $finalOrderData['OrderPaymentType'] = $this->_helper->recursivelyGetArrayData(['paymentInfo', 'additional_information', 'method_title'], $orderData);
        $finalOrderData['OrderDiscountDetailsTotal'] = $this->_helper->recursivelyGetArrayData(['base_discount_amount'], $orderData);
        $finalOrderData['CurrencyCode'] = $this->_helper->recursivelyGetArrayData(['order_currency_code'], $orderData);
        $finalOrderData['EstimateUTCOffset'] = $this->_helper->recursivelyGetArrayData(['delivery_utc_offset'], $orderData, 0);
        $finalOrderData['EstimateUTCTimestamp'] = $this->applyOffset(
            $this->unifyDeliveryTimestamps($this->_helper->recursivelyGetArrayData(['delivery_timestamp'], $orderData, '')),
            $finalOrderData['EstimateUTCOffset']
        );

        /** Collect customer data */
        $this->collectCustomerGeneralData($finalOrderData, $orderData);
        $this->collectCustomerBillingData($finalOrderData, $orderData);
        $this->collectCustomerShippingData($finalOrderData, $orderData);

        /** * Order items */
        $orderItems = $this->prepareOrderItems($orderData, $existingOrderData);
        if (count($orderItems) === 0) {
            return false;
        }

        $finalOrderData['OrderItems'] = $orderItems;

        if (count($orderItems) === $this->availableDownloadableItems) {
            // force orders status to complete
            $finalOrderData['OrderStatus'] = 'Complete';
            $finalOrderData['OrderStatusId'] = $this->weSupplyMappings::WESUPPLY_ORDER_COMPLETE;
        }

        $this->eventManager->dispatch(
            'wesupply_order_mapping_info_after',
            [
                'initialOrderData' => $orderData,
                'finalOrderData' => $finalOrderData
            ]
        );

        return $finalOrderData;
    }

    /**
     * @param $timestamps
     * @return string
     */
    protected function unifyDeliveryTimestamps($timestamps)
    {
        $prevTstp = false;
        $timestampsArr = explode(',', $timestamps);
        foreach ($timestampsArr as $index => $timestamp) {
            $currentTstp = $timestamp;
            if (($prevTstp && $currentTstp == $prevTstp) || empty($currentTstp)) {
                unset($timestampsArr[$index]);
                $prevTstp = $currentTstp;

                continue;
            }
            $prevTstp = $currentTstp;
        }

        return implode(',', $timestampsArr);
    }

    /**
     * @param $timestamps
     * @param $offset
     * @return string
     */
    private function applyOffset($timestamps, $offset)
    {
        $timestampsArr = array_map('intval', explode(',', $timestamps));

        foreach ($timestampsArr as $key => $timestamp) {
            $timestampsArr[$key] = $timestamp + (int) $offset;
        }

        return  implode(',', $timestampsArr);
    }

    /**
     * Converts order information
     * @param $orderData
     * @return mixed
     */
    protected function convertInfoToXml($orderData)
    {
        $xmlData = $this->array2xml($orderData, false);
        $xmlData = str_replace("<?xml version=\"1.0\"?>\n", '', $xmlData);

        return $xmlData;
    }

    /**
     * Convert array to xml
     * @param $array
     * @param bool $xml
     * @param string $xmlAttribute
     * @return mixed
     */
    private function array2xml($array, $xml = false, $xmlAttribute = '')
    {
        if ($xml === false) {
            $xml = new \SimpleXMLElement('<Order/>');
        }

        foreach ($array as $key => $value) {
            $key = ucwords($key, '_');
            if (is_object($value)) {
                continue;
            }
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $this->array2xml($value, $xml->addChild($key), $key);
                } else {
                    //mapping for $key to proper
                    $xmlAttribute = $this->mapXmlAttributeForChildren($xmlAttribute);
                    $this->array2xml($value, $xml->addChild($xmlAttribute), $key);
                }
            } else {
                if (is_numeric($key)) {
                    $child = $xml->addChild($xmlAttribute);
                    $child->addAttribute('key', $key);
                    $value = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
                    $child->addAttribute('value', $value);
                } else {
                    $value = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
                    $xml->addChild($key, $value);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * @param $key
     * @return mixed
     */
    private function mapXmlAttributeForChildren($key)
    {
        $mappings = [
            'OrderItems' => 'Item',
            'AttributesInfo' => 'Info'
        ];

        if (isset($mappings[$key])) {
            return $mappings[$key];
        }

        return $key;
    }

    /**
     * Return country name
     * @param $countryId
     * @return string
     */
    protected function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->loadByCode($countryId);
        return $country->getName();
    }

    /**
     * Due to possibility of endless order statuses in magento2
     * we are transferring the order status label and order state mapped to WeSupply order status
     *
     * @param $orderData
     * @param $finalOrderData
     */
    protected function mapOrderStateToWeSupply($orderData, &$finalOrderData)
    {
        $orderStatusId = $this->weSupplyStatusMappedArray[\Magento\Sales\Model\Order::STATE_NEW];

        if (isset($orderData['state'])) {
            $state = $orderData['state'];
            if (array_key_exists($state, $this->weSupplyStatusMappedArray)) {
                $orderStatusId = $this->weSupplyStatusMappedArray[$state];
            }
        }

        $finalOrderData['OrderStatus'] = $this->orderStatusLabel;
        $finalOrderData['OrderStatusId'] = $orderStatusId;
    }

    /**
     * @param $status
     * @param $information
     */
    protected function getItemStatusInfo($status, &$information)
    {
        switch ($status) {
            case 'canceled':
                $orderStatus = 'Canceled';
                $orderStatusId = 1;
                break;
            case 'refunded':
                $orderStatus = 'Refunded';
                $orderStatusId = 2;
                break;
            case 'shipped':
                $orderStatus = 'Shipped';
                $orderStatusId = 3;
                break;
            case 'instore_pickup':
                $orderStatus = 'Ready for Pickup';
                $orderStatusId = 15;
                break;
            case 'virtual':
                $orderStatus = 'Virtual';
                $orderStatusId = 50;
                break;
            case 'download_available':
                $orderStatus = 'Downloadable';
                $orderStatusId = 60;
                break;
            default:
                $orderStatus = 'Processing';
                $orderStatusId = 4;
                break;

        }

        $information['ItemStatus'] = $orderStatus;
        $information['ItemStatusId'] = $orderStatusId;
    }

    /**
     * @param $orderData
     * @param $existingOrderData
     * @return array
     * @throws NoSuchEntityException
     */
    protected function prepareOrderItems($orderData, $existingOrderData)
    {
        $orderItems = [];
        $this->availableDownloadableItems = 0;

        $itemFeeShipping = $this->_helper->recursivelyGetArrayData(['base_shipping_amount'], $orderData, 0);
        $orderItemsData = $orderData['OrderItems'];

        foreach ($orderItemsData as $item) {

            $generalData = [];
            $generalData['ItemID'] = $this->_helper->recursivelyGetArrayData(['item_id'], $item);
            $generalData['ItemPrice'] = $this->_helper->recursivelyGetArrayData(['base_price'], $item);
            $generalData['ItemCost'] = $this->_helper->recursivelyGetArrayData(['base_cost'], $item, $generalData['ItemPrice']);
            $generalData['ItemAddressID'] = $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'entity_id'], $orderData);
            $generalData['Option1'] = '';
            $generalData['Option2'] = $this->_fetchProductOptionsAsArray($item);
            $generalData['Option3'] = $this->_fetchProductBundleOptionsAsArray($item);
            $generalData['ItemProduct'] = [];
            $generalData['ItemProduct']['ProductID'] = $this->_helper->recursivelyGetArrayData(['product_id'], $item);
            $generalData['ItemProduct']['ProductCode'] = $this->_helper->recursivelyGetArrayData(['name'], $item);
            $generalData['ItemProduct']['ProductPartNo'] = $this->_helper->recursivelyGetArrayData(['sku'], $item);
            $generalData['ItemTitle'] = $this->_helper->recursivelyGetArrayData(['name'], $item);

            /**
             * some item data needs to remain as it was at the place order moment
             * so, we are not allowed to update it
             */
            $generalData = $this->_fetchInvariableData($existingOrderData, $item, $generalData);

            $itemQtyGrouped = $this->splitItemQty($item, $orderData['shipmentTracking']);
            $itemTotals = $this->getItemTotals($item);

            $initItemStatus = '';
            $carrierCode = $orderData['carrier_code'];

            /** Send information about downloadable items */
            $generalData['ItemDownloadUrl'] = '';
            if ($item['product_type'] === DownloadableType::TYPE_DOWNLOADABLE) {
                $carrierCode = 'downloadable';
                $initItemStatus = 'download_available';
                $generalData['ItemDownloadUrl'] = $this->_getProductDownloadUrl($item);
            }

            /** Send information about virtual items */
            if ($item['product_type'] === ProductType::TYPE_VIRTUAL) {
                $carrierCode = 'virtual';
                $initItemStatus = 'virtual';
            }

            /** Send information about shipped items */
            $addedToShipment = false;
            $shippedItems = $orderData['shipmentTracking'];
            foreach ($shippedItems as $itemId => $shipment) {
                if ($itemId == $this->_helper->recursivelyGetArrayData(['item_id'], $item)) {
                    foreach ($shipment as $trackingInformation) {
                        $carrierCode = $this->_helper->recursivelyGetArrayData(
                            ['carrier_code'],
                            $trackingInformation
                        );

                        if (isset($this->weSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode])) {
                            $carrierCode = $this->weSupplyMappings::MAPPED_CARRIER_CODES[$carrierCode];
                        }

                        $itemStatus = $carrierCode == $this->weSupplyMappings::INSTORE_PICKUP_LABEL ?
                            'instore_pickup' : 'shipped';

                        $itemInfo = $this->getItemSpecificInformation(
                            $itemFeeShipping,
                            $itemTotals['row_total'],
                            $itemTotals['tax_amount'],
                            $itemTotals['discount_amount'],
                            $itemQtyGrouped['qty_ordered'],
                            $trackingInformation['qty'],
                            $itemStatus,
                            $trackingInformation['title'],
                            $trackingInformation['track_number'],
                            $trackingInformation['inventory_source'],
                            $carrierCode
                        );

                        $itemFeeShipping = 0; // reset shipping fee because its amount was assigned for the very firs shipped item
                        if ($this->groupItemsWithSameTracking($orderItems, $trackingInformation['track_number'], $itemId, $itemInfo)) {
                            continue;
                        }

                        $generalData = array_merge($generalData, $itemInfo);
                        $orderItems[] = $generalData;
                        $addedToShipment = true;
                    }
                }
            }

            /** Send information about canceled items */
            if ($itemQtyGrouped['qty_canceled'] > 0) {
                $itemInfo = $this->getItemSpecificInformation(
                    $itemFeeShipping,
                    $itemTotals['row_total'],
                    $itemTotals['tax_amount'],
                    $itemTotals['discount_amount'],
                    $itemQtyGrouped['qty_ordered'],
                    $itemQtyGrouped['qty_canceled'],
                    'canceled',
                    '',
                    '',
                    $this->_helper->recursivelyGetArrayData(['supplier_code'], $orderData),
                    $carrierCode
                );

                $generalData = array_merge($generalData, $itemInfo);
                $orderItems[] = $generalData;
            }

            /** For more detailed data we might use information  from the created credit memos */
            if ($itemQtyGrouped['qty_refunded'] > 0 && !$addedToShipment) {
                $itemInfo = $this->getItemSpecificInformation(
                    $itemFeeShipping,
                    $itemTotals['row_total'],
                    $itemTotals['tax_amount'],
                    $itemTotals['discount_amount'],
                    $itemQtyGrouped['qty_ordered'],
                    $itemQtyGrouped['qty_refunded'],
                    'refunded',
                    '',
                    '',
                    $this->_helper->recursivelyGetArrayData(['supplier_code'], $orderData),
                    $carrierCode
                );

                $generalData = array_merge($generalData, $itemInfo);
                $orderItems[] = $generalData;
            }

            /** Send information about items still in processed state */
            if ($itemQtyGrouped['qty_processing'] > 0) {
                $itemInfo = $this->getItemSpecificInformation(
                    $itemFeeShipping,
                    $itemTotals['row_total'],
                    $itemTotals['tax_amount'],
                    $itemTotals['discount_amount'],
                    $itemQtyGrouped['qty_ordered'],
                    $itemQtyGrouped['qty_processing'],
                    $initItemStatus,
                    '',
                    '',
                    $this->_helper->recursivelyGetArrayData(['supplier_code'], $orderData),
                    $carrierCode
                );

                $generalData = array_merge($generalData, $itemInfo);
                $orderItems[] = $generalData;
            }
            $itemFeeShipping = 0;
        }

        return $orderItems;
    }

    /**
     * @param $itemFeeShipping
     * @param $itemTotal
     * @param $taxTotal
     * @param $discountTotal
     * @param $qtyOrdered
     * @param $qtyCurrent
     * @param $itemStatus
     * @param $shippingService
     * @param $shippingTracking
     * @param $inventorySource
     * @param $carrierCode
     * @return array
     */
    protected function getItemSpecificInformation(
        $itemFeeShipping,
        $itemTotal,
        $taxTotal,
        $discountTotal,
        $qtyOrdered,
        $qtyCurrent,
        $itemStatus,
        $shippingService,
        $shippingTracking,
        $inventorySource,
        $carrierCode
    ) {
        $information = [];
        $information['ItemQuantity'] = $qtyCurrent;
        $information['ItemShippingService'] = $shippingService;
        $information['ItemPOShipper'] = $carrierCode;
        $information['ItemShippingTracking'] = $shippingTracking;
        $information['ItemLevelSupplierName'] = $inventorySource;
        $information['ItemTotal'] = number_format(($qtyCurrent * $itemTotal) / $qtyOrdered, 4, '.', '');
        $information['ItemTax'] = number_format(($qtyCurrent * $taxTotal) / $qtyOrdered, 4, '.', '');
        $information['ItemDiscountDetailsTotal'] = number_format(($qtyCurrent * $discountTotal) / $qtyOrdered, 4, '.', '');
        $this->getItemStatusInfo($itemStatus, $information);

        /**
         *  ItemShipping - the first item will have shipping value, all other items will have 0 value
         *  Item_CouponAmount - will always have 0, the discount amount is set trough OrderDiscountDetailsTotal field
         */
        $information['ItemShipping'] = number_format($itemFeeShipping, 4, '.', '');
        $information['Item_CouponAmount'] = number_format(0, 4, '.', '');

        /**
         * ItemTotal will include also the shipping value
         */
        $information['ItemTotal'] += $information['ItemShipping'];

        return $information;
    }

    private function _fetchProductBundleOptionsAsArray($item)
    {
        $bundleArray = [];
        /**
         * bundle product options
         */
        $productOptions = $item['product_options'];
        if (isset($productOptions['bundle_options'])) {
            foreach ($productOptions['bundle_options'] as $bundleOptions) {
                $bundleProductInfo = [];
                $bundleProductInfo['label'] = $bundleOptions['label'];
                $finalOptionsCounter = 0;
                foreach ($bundleOptions['value'] as $finalOptions) {
                    $bundleProductInfo['product_' . $finalOptionsCounter] = $finalOptions;
                    $finalOptionsCounter++;
                }
                $bundleArray['value_' . $bundleOptions['option_id']] = $bundleProductInfo;
            }
        }

        return $bundleArray;
    }

    /**
     * @param $item
     * @return array
     */
    private function _fetchProductAttributesToExport($item)
    {
        $attrToBeCollected = $this->_helper->getAttributesToBeExported();

        return $this->collectAttributeValues($item, $attrToBeCollected);
    }

    /**
     * Collect product attributes
     *
     * @param $item
     * @param $attrToBeCollected
     * @return array
     */
    private function collectAttributeValues($item, $attrToBeCollected)
    {
        $prodAttrs = [];
        $products = $this->getProductsFromItemByPriorityFetch($item);
        foreach ($products as $_product) {
            if (is_null($_product)) {
                continue;
            }

            foreach ($attrToBeCollected as $attrCode) {
                if (!array_key_exists($attrCode, $prodAttrs)) {
                    if ($attrCode == 'price') {
                        $prodAttrs[$attrCode] = $this->_helper->recursivelyGetArrayData(['base_price'], $item);
                        continue;
                    }
                    if ($attributeData = $_product->getData($attrCode)) {
                        $attribute = $_product->getResource()->getAttribute($attrCode);
                        if ($attribute->usesSource()) {
                            $attributeData = $attribute->getSource()->getOptionText($attributeData);
                            if ($attributeData instanceof Phrase) {
                                $attributeData = $attributeData->getText();
                            }
                        }
                        $prodAttrs[$attrCode] = is_array($attributeData) ? implode(', ', $attributeData) : $attributeData;
                    }
                }
            }
        }

        return $prodAttrs;
    }

    /**
     * @param $item
     * @return array
     */
    private function getProductsFromItemByPriorityFetch($item)
    {
        $productOptions = $item['product_options'];
        $fetchFrom = $this->_helper->getAttributesFetchPriority();
        switch ($fetchFrom) {
            case 'itself_parent':
                if (isset($productOptions['simple_sku'])) {
                    $products[] = $this->_getProductBySku($productOptions['simple_sku']);
                }
                $products[] = $this->_getProductById($item['product_id']);
                break;
            case 'parent_itself':
                $products[] = $this->_getProductById($item['product_id']);
                if (isset($productOptions['simple_sku'])) {
                    $products[] = $this->_getProductBySku($productOptions['simple_sku']);
                }
                break;
            case 'itself_only':
                $products[] = isset($productOptions['simple_sku']) ? // it was ordered a configurable product?
                    $this->_getProductBySku($productOptions['simple_sku']) :
                    $this->_getProductById($item['product_id']);
                break;
            case 'parent_only':
                $products[] = $this->_getProductById($item['product_id']);
                break;
            default:
                $products = [];
                break;
        }

        return $products ?? [];
    }

    /**
     * @param $item
     * @return array
     */
    private function _fetchProductOptionsAsArray($item)
    {
        $optionsArray = [];
        /**
         * configurable product options
         */
        $productOptions = $item['product_options'];
        if (isset($productOptions['attributes_info'])) {
            foreach ($productOptions['attributes_info'] as $attributes) {
                $xmlLabel = preg_replace('/[^\w0-1]|^\d/', '_', trim($attributes['label']));
                $optionsArray[$xmlLabel] = $attributes['value'];
            }
        }

        /**
         * custom options
         */
        if (isset($productOptions['options'])) {
            foreach ($productOptions['options'] as $customOption) {
                $xmlLabel = preg_replace('/[^\w0-1]|^\d/', '_', trim($customOption['label']));
                $optionsArray[$xmlLabel] = $customOption['value'];
            }
        }

        return $optionsArray;
    }

    /**
     * Fetch item image
     * or fallback on getting the default placeholder
     *
     * @param $item
     * @return string
     * @throws NoSuchEntityException
     */
    private function _fetchProductImage($item)
    {
        $storeManager = $this->storeManagerInterface->getStore($item['store_id']);
        $this->mediaUrl = $storeManager->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        $productOptions = $item['product_options'];
        if (isset($productOptions['simple_sku'])) { // first, look for associated simple product image
            $_product = $this->_getProductBySku($productOptions['simple_sku']);
            if (!is_null($_product)) {
                $productImage = $_product->getImage();
                if ($this->isValidProductImage($productImage) && $this->checkRealMediaDir($productImage)) {
                    return $this->mediaUrl . self::PRODUCT_IMAGE_SUBDIRECTORY . trim($productImage, '/');
                }
            }
        }

        $_product = $this->_getProductById($item['product_id']);
        if (!is_null($_product)) {
            $productImage = $_product->getImage();
            if ($this->isValidProductImage($productImage) && $this->checkRealMediaDir($productImage)) {
                return $this->mediaUrl . self::PRODUCT_IMAGE_SUBDIRECTORY . trim($productImage, '/');
            }
        }

        /**
         * finally try to get the custom placeholder image
         * if the above methods failed
         */
        $imageUrl = $this->helperImageFactory->create()->getDefaultPlaceholderUrl('image');

        return $this->convertToUnversionedFrontendUrl($imageUrl, $item['store_id']) ?? '';
    }

    /**
     * @param $item
     * @return string
     */
    private function _fetchProductUrl($item)
    {
        $product = $this->_getProductById($item['product_id']);
        if (!is_null($product)) {
            return $product->getProductUrl();
        }

        return '#';
    }

    /**
     * @return mixed
     */
    private function _fetchWeightUnit()
    {
        return $this->_helper->getWeightUnit();
    }

    /**
     * @return string
     */
    private function _fetchMeasurementsUnit()
    {
        return $this->_helper->getMeasurementsUnit();
    }

    /**
     * @param $item
     * @param $attrCode
     * @return string
     */
    private function _fetchProductAttr($item, $attrCode)
    {
        $attrData = $this->collectAttributeValues($item, [$attrCode]);

        if (isset($attrData[$attrCode])) {
            return $attrData[$attrCode];
        }

        return '';
    }

    /**
     * @param $item
     * @return string
     */
    private function _getProductDownloadUrl($item)
    {
        $purchased = $this->linksFactory->create()
            ->addFieldToFilter('order_id', $item['order_id'])
            ->addFieldToFilter('order_item_id', $item['item_id'])
            ->getFirstItem();

        if (!$purchased->getPurchasedId()) {
            return '';
        }

        $purchasedItem = $this->itemsFactory->create()->addFieldToFilter(
                'purchased_id', ['in' => $purchased->getPurchasedId()]
            )->addFieldToFilter(
                'status', ['nin' => [Item::LINK_STATUS_PENDING_PAYMENT, Item::LINK_STATUS_PAYMENT_REVIEW]]
            )->setOrder(
                'item_id',
                'desc'
            )->getFirstItem();

        if ($purchasedItem->getStatus() !== Item::LINK_STATUS_AVAILABLE) {
            return '';
        }

        $this->availableDownloadableItems++;

        return $this->storeManagerInterface->getStore($item['store_id'])
                ->getBaseUrl(UrlInterface::URL_TYPE_LINK)
            . 'downloadable/download/link/'
            . $purchasedItem->getLinkHash();
    }

    /**
     * @param $productId
     * @return ProductInterface
     */
    private function _getProductById($productId)
    {
        try {
            return $this->productRepositoryInterface->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Wesupply error: ' . $e->getMessage());
        }
    }

    /**
     * @param $productSku
     * @return ProductInterface
     */
    private function _getProductBySku($productSku)
    {
        try {
            return $this->productRepositoryInterface->get($productSku);
        } catch (NoSuchEntityException $e) {
            $this->logger->error('Wesupply error: ' . $e->getMessage());
        }
    }

    /**
     * @param $imageUrl
     * @param $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function convertToUnversionedFrontendUrl($imageUrl, $storeId)
    {
        $theme = $this->_helper->getCurrentTheme($storeId);
        $imageUrlArr = explode('/', $imageUrl);

        foreach ($imageUrlArr as $key => $urlPart) {
            if (strpos($urlPart, 'version') !== false) {
                unset($imageUrlArr[$key]);
            }
            if (strpos($urlPart, 'adminhtml') !== false || strpos($urlPart, 'webapi') !== false) {
                $imageUrlArr[$key] = 'frontend';
            }
            if (strpos($urlPart, 'view') !== false) {
                $imageUrlArr[$key] = trim($theme->getThemePath(), '/');
            }
            if (strpos($urlPart, 'backend') !== false) {
                $themePathArr = explode('/', $theme->getThemePath());
                $imageUrlArr[$key] = end($themePathArr);
            }
        }

        return implode('/', $imageUrlArr);
    }

    private function isValidProductImage($productImage)
    {
        return (!empty($productImage) && strpos($productImage, 'no_selection') === false);
    }

    /**
     * @param $existingOrderData
     * @param $item
     * @param $generalData
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function _fetchInvariableData($existingOrderData, $item, &$generalData)
    {
        $existingItemKey = false;
        if ($existingOrderData) {// check if this is the first sync or it is an update
            if ($this->_isMultiProducts($existingOrderData['OrderItems']['Item'])) {
                $found = array_filter($existingOrderData['OrderItems']['Item'],
                    function ($existingItemData) use ($item) {
                        return $existingItemData['ItemID'] == $item['item_id'];
                    }
                );
                $existingItemKey = key($found);
            }

            $origItemData = false !== $existingItemKey ?
                $existingOrderData['OrderItems']['Item'][$existingItemKey] :
                $existingOrderData['OrderItems']['Item'];

            foreach (self::DO_NOT_UPDATE as $key) {
                $generalData[$key] = $origItemData[$key];
            }
        }

        foreach (self::DO_NOT_UPDATE as $key) {
            if (!isset($generalData[$key])) {
                switch ($key) {
                    case 'ItemImageUri':
                        $itemData = $this->_fetchProductImage($item);
                        break;
                    case 'ItemProductUri':
                        $itemData = $this->_fetchProductUrl($item);
                        break;
                    case 'OptionHidden':
                        $itemData = $this->_fetchProductAttributesToExport($item);
                        break;
                    case 'ItemWeight':
                    case 'ItemWidth':
                    case 'ItemHeight':
                    case 'ItemLength':
                        $attrCode = $this->_helper->getOrderExportSettings(
                            $this->_helper->fromCamelCase($key, '_') . '_attr'
                        );
                        $itemData = $attrCode ? $this->_fetchProductAttr($item, $attrCode) : '';
                        break;
                    case 'ItemWeightUnit':
                        $itemData = $this->_fetchWeightUnit();
                        break;
                    case 'ItemMeasureUnit':
                        $itemData = $this->_fetchMeasurementsUnit();
                        break;
                    default:
                        $itemData = '';
                        break;
                }
                $generalData[$key] = $itemData;
            }
        }

        return $generalData;
    }

    /**
     * Check image file and update mediaUrl
     *
     * @param $productImage
     * @return bool
     */
    private function checkRealMediaDir($productImage)
    {
        $mediaDir = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        if (!file_exists($mediaDir . self::PRODUCT_IMAGE_SUBDIRECTORY . trim($productImage, '/'))) {
            // remove pub directory and recheck image file
            $replacement = '$1$3';
            $pattern = '/(^.*)(pub\/)(.*)/i';
            $mediaDir = preg_replace($pattern, $replacement, $mediaDir, 1);
            if (!file_exists($mediaDir . self::PRODUCT_IMAGE_SUBDIRECTORY . trim($productImage, '/'))) {
                return false;
            }
            // update mediaUrl
            $this->mediaUrl = preg_replace($pattern, $replacement, $this->mediaUrl);
        }

        return true;
    }

    /**
     * @param $arr
     * @return bool
     */
    private function _isMultiProducts($arr)
    {
        if ([] === $arr) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * @param $orderData
     * @return array
     */
    private function _fetchInventorySourcesByItems($orderData)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderData['entity_id'])->create();

        try {
            $shipments = $this->shipmentRepository->getList($searchCriteria);
            foreach ($shipments->getItems() as $shipment) {
                $shipmentDetails = $this->shipmentRepository->get($shipment->getEntityId());
                $extensionAttr = $shipmentDetails->getExtensionAttributes();
                foreach ($shipmentDetails->getItems() as $item) {
                    if (!method_exists($extensionAttr, 'getSourceCode')) {
                        $inventorySourcesByItemIds[$item->getParentId()] = $this->_helper->recursivelyGetArrayData(['store_id'], $orderData);
                        continue;
                    }

                    $inventorySourcesByItemIds[$item->getParentId()] = $extensionAttr->getSourceCode() != 'default' ?
                        $extensionAttr->getSourceCode() :
                        $this->_helper->recursivelyGetArrayData(['store_id'], $orderData);
                }
            }
        } catch (Exception $exception) {
            $this->logger->error('Error while fetching MSI ' . $exception->getMessage());
        }

        return $inventorySourcesByItemIds ?? [];
    }

    /**
     * @param $orderItems
     * @param $trackingNo
     * @param $currItemId
     * @param $currItemInfo
     * @return bool
     */
    private function groupItemsWithSameTracking(&$orderItems, $trackingNo, $currItemId, $currItemInfo)
    {
        $found = array_filter($orderItems, function ($orderedItem) use ($trackingNo, $currItemId) {
            return $orderedItem['ItemShippingTracking'] == $trackingNo && $orderedItem['ItemID'] == $currItemId;
        });

        if (!empty($found)) {
            $foundKey = key($found);
            $orderItems[$foundKey]['ItemQuantity'] += $currItemInfo['ItemQuantity'];
            $orderItems[$foundKey]['ItemTotal'] += $currItemInfo['ItemTotal'];
            $orderItems[$foundKey]['ItemTax'] += $currItemInfo['ItemTax'];
            $orderItems[$foundKey]['ItemDiscountDetailsTotal'] += $currItemInfo['ItemDiscountDetailsTotal'];
            $orderItems[$foundKey]['Item_CouponAmount'] += $currItemInfo['Item_CouponAmount'];

            return true;
        }

        return false;
    }

    /**
     * @param $item
     * @param $shippedItems
     * @return array
     */
    private function splitItemQty($item, $shippedItems)
    {
        $qtySplit = [];
        $qtySplit['qty_ordered']    = floatval($this->_helper->recursivelyGetArrayData(['qty_ordered'], $item));
        $qtySplit['qty_canceled']   = floatval($this->_helper->recursivelyGetArrayData(['qty_canceled'], $item, 0));
        $qtySplit['qty_refunded']   = floatval($this->_helper->recursivelyGetArrayData(['qty_refunded'], $item, 0));
        $qtySplit['qty_shipped']    = !empty($shippedItems[$item['item_id']]) ?
            array_reduce($shippedItems[$item['item_id']], function($carry, $sItem) {
                $carry += floatval($sItem['qty']);
                return $carry;
            }) : 0;
        $qtySplit['qty_processing'] =
            $qtySplit['qty_ordered'] -
            $qtySplit['qty_canceled'] -
            $qtySplit['qty_refunded'] -
            $qtySplit['qty_shipped'];

        return $qtySplit;
    }

    /**
     * @param $item
     * @return array
     */
    private function getItemTotals($item)
    {
        return [
            'row_total' => $this->_helper->recursivelyGetArrayData(['row_total'], $item),
            'tax_amount' => $this->_helper->recursivelyGetArrayData(['tax_amount'], $item),
            'discount_amount' => $this->_helper->recursivelyGetArrayData(['discount_amount'], $item)
        ];
    }

    /**
     * @param $finalOrderData
     * @param $orderData
     * @throws LocalizedException
     */
    private function collectCustomerGeneralData(&$finalOrderData, $orderData)
    {
        $customerIsGuest = $this->_helper->recursivelyGetArrayData(['customer_is_guest'], $orderData);
        $customerId = $customerIsGuest ?
            intval(664616765 . '' . $orderData['entity_id']) :
            $this->_helper->recursivelyGetArrayData(['customer_id'], $orderData);

        $finalOrderData['OrderCustomer']['IsGuest'] = $customerIsGuest;
        $finalOrderData['OrderCustomer']['CustomerID'] = $customerId;

        try {
            $customer = $this->customer->getById($customerId);
            $finalOrderData['OrderCustomer']['CustomerCreateDate'] = $customer->getCreatedAt();
            $finalOrderData['OrderCustomer']['CustomerModifiedDate'] = $customer->getUpdatedAt();
        } catch (NoSuchEntityException $e) {
            $finalOrderData['OrderCustomer']['CustomerCreateDate'] = $finalOrderData['OrderDate'];
            $finalOrderData['OrderCustomer']['CustomerModifiedDate'] = $finalOrderData['LastModifiedDate'];
        }
    }

    /**
     * @param $finalOrderData
     * @param $orderData
     */
    private function collectCustomerBillingData(&$finalOrderData, $orderData)
    {
        $finalOrderData['OrderCustomer']['CustomerFirstName'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'firstname'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerLastName'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'lastname'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerName'] =
            $finalOrderData['OrderCustomer']['CustomerFirstName'] . ' ' . $finalOrderData['OrderCustomer']['CustomerLastName'];
        $finalOrderData['OrderCustomer']['CustomerEmail'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'email'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerAddress1'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'street'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerAddress2'] = ''; // not saved separately in magento
        $finalOrderData['OrderCustomer']['CustomerCity'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'city'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerStateProvince'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'region'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerPostalCode'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'postcode'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerCountry'] =
            $this->getCountryName($this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'country_id'], $orderData));
        $finalOrderData['OrderCustomer']['CustomerCountryCode'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'country_id'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerPhone'] =
            $this->_helper->recursivelyGetArrayData(['billingAddressInfo', 'telephone'], $orderData);
    }

    /**
     * @param $finalOrderData
     * @param $orderData
     */
    private function collectCustomerShippingData(&$finalOrderData, $orderData)
    {
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressID'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'entity_id'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressContact'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'firstname'], $orderData) . ' ' .
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'lastname'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressAddress1'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'street'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressCity'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'city'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressState'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'region'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressZip'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'postcode'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressCountry'] =
            $this->getCountryName($this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'country_id'], $orderData));
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressCountryCode'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'country_id'], $orderData);
        $finalOrderData['OrderCustomer']['CustomerShippingAddresses']['CustomerShippingAddress']['AddressPhone'] =
            $this->_helper->recursivelyGetArrayData(['shippingAddressInfo', 'telephone'], $orderData);
    }
}
