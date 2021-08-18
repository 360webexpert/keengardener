<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Keengardener\MagentoInventory\Model\IsProductSalableCondition;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
/**
 * Get back order notify for customer condition
 */
class BackOrderNotifyCustomerCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    private $productRepository;
    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetStockItemDataInterface $getStockItemData
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param GetProductSalableQtyInterface|null $getProductSalableQty
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ?GetProductSalableQtyInterface $getProductSalableQty = null,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData = $getStockItemData;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->getProductSalableQty = $getProductSalableQty
            ?? ObjectManager::getInstance()->get(GetProductSalableQtyInterface::class);
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */

    public function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        if ($stockItemConfiguration->isManageStock()
            && $stockItemConfiguration->getBackorders() === StockItemConfigurationInterface::BACKORDERS_YES_NOTIFY
        ) {
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            if (null === $stockItemData) {
                return $this->productSalableResultFactory->create(['errors' => []]);
            }

            $salableQty = $this->getProductSalableQty->execute($sku, $stockId);
            $backOrderQty = $requestedQty - $salableQty;
            $displayQty = $this->getDisplayQty($backOrderQty, $salableQty, $requestedQty);

            if ($displayQty > 0) {
                die('sss');
                $c2c_direct_despatch = $this->loadMyProduct($sku)->getData('c2c_direct_despatch');
                $c2c_direct_oos_delivery_time = $this->loadMyProduct($sku)->getAttributeText('c2c_direct_oos_delivery_time');
                if (isset($c2c_direct_despatch)) {
                    $message = 'This item is sent from our suppliers and is usually delivered in '.$c2c_direct_oos_delivery_time;
                }else{
                    $message = 'This item is a pre-order and is usually delivered in '.$c2c_direct_oos_delivery_time;
                }
                $errors = [
                    $this->productSalabilityErrorFactory->create([
                            'code' => 'back_order-not-enough',
                            'message' => $message
                            ])
                ];
                return $this->productSalableResultFactory->create(['errors' => $errors]);
            }
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }

    /**
     * Get display quantity to show the number of quantity customer can backorder
     *
     * @param float $backOrderQty
     * @param float $salableQty
     * @param float $requestedQty
     * @return float
     */
    private function getDisplayQty(float $backOrderQty, float $salableQty, float $requestedQty): float
    {
        $displayQty = 0;
        if ($backOrderQty > 0 && $salableQty > 0) {
            $displayQty = $backOrderQty;
        } elseif ($requestedQty > $salableQty) {
            $displayQty = $requestedQty;
        }
        return $displayQty;
    }
}
