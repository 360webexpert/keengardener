<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('memory_limit', '5G');
error_reporting(0);

use Magento\Framework\App\Bootstrap;
require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
$store = $storeManager->getStore();
$mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
//$product = $objectManager->create('\Magento\Catalog\Model\Product')->load($id);
$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$products = $productCollection->addAttributeToSelect('*');
$products->addAttributeToFilter('status', 1);//enabled
$products->addAttributeToFilter('visibility', 1);
$products->addAttributeToFilter(
 array(
        array('attribute' => 'sku', 'like' => 'MUCK-%'),
        array('attribute' => 'sku', 'like' => 'LECHA-%'),
    )
);
$products->load();

//product_export.php

// open the csv file and write the header in the first line
$fp = fopen(dirname(__FILE__).'/base_feed.csv', 'a');
$csvHeader = array(
  'id',
  "title",
  "brand",
  "condition",
  "rrp",
  "quantity",
  "mpn",
  "availability",
  "description",
  "expiration_date",
  "image_link",
  "link",
  "price",
  "product_type",
  "gtin",
  "weight"
);
fputcsv( $fp, $csvHeader, $delimiter = "|");
// iterate through all the products
 
foreach ( $products as $_product )
{
   // load a product object using its sku
   $sku = $_product->getData('sku');
   $product = $_product->load($sku);
   $reference = $objectManager->create('\Magento\Catalog\Model\Product')->load($_product->getId())->getSku();
   //die('sss');
   //$reference = Mage::getModel('catalog/product')->load($_product->getId())->getSku();
   //check if the product is not visible but needs to be listed in google shopping
   if ($product->getVisibility() == 1)
   {
      $image = $mediaUrl.'catalog/product'.$product->getImage();
      $expiration = date('Y-m-d', strtotime("+30 days"));
      $condition = 'new';

      $stockItem = $_product->getExtensionAttributes()->getStockItem();
   	  $qty = $stockItem->getQty(); 

   	  //$qty = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
      //$qty->getStockQty($_product->getId(), $_product->getStore()->getWebsiteId());
     
	  //$qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
	  $mpn = substr( $reference, strpos( $reference, '-') + 1);
	  //$direct = $product->getAttributeText('c2c_direct_despatch');
	  $direct = $product->getResource()->getAttribute('c2c_direct_despatch')->setStoreId(0)->getFrontend()->getValue($product);
	  $oos = $product->getOosAllowOrder();

	  $gtin = $product->getResource()->getAttribute('c2c_ean_1')->setStoreId(0)->getFrontend()->getValue($product);
	  $parentIds = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($_product->getId());
	  
	  //Removes any items marked out of stock on website *Google Base Fix 27/04*
	  if ($oos == 0 && $qty <= 0):
		$availability = 'out of stock';  
	  
	  //Filters brands that are available for sale on pre-order or direct from being listed as out of stock and will pay for marketing
	  elseif ($qty <= 0 && strpos($reference, 'MUCK-') === false && strpos($reference, 'PALR-') === false && strpos($reference, 'TGV') === false && strpos($reference, 'BULL-') === false && strpos($reference, 'NORL-') === false && strpos($reference, 'GABL-') === false && strpos($reference, 'BUSC-') === false && strpos($reference, 'BEEF-') === false && strpos($reference, 'COBR-') === false && strpos($reference, 'LECHA-') === false && strpos($reference, 'CHAR-') === false && strpos($reference, 'NAPO-') === false && strpos($reference, 'LEIS-') === false && strpos($reference, 'FORE-') === false && strpos($reference, 'KETE-') === false && strpos($reference, 'HAND-') === false && strpos($reference, 'GRAK-') === false && strpos($reference, 'PRIM-') === false && strpos($reference, 'EGO-') === false && strpos($reference, 'ALLETT-') === false && strpos($reference, 'LAND-') === false && strpos($reference, 'GRAN-') === false):
		$availability = 'out of stock';
	
	  //Force Stock status as In stock for all items not filtered out above
		else:
		$availability = 'in stock';
		endif;
	  
	  $description = preg_replace( "/\r|\n/", "", $_product->getShortDescription());
	  $descriptionShort = strip_tags($description, '<p><a><br>');
	  $link = 'https://www.keengardener.co.uk/'.strtok($product->getUrlKey(), '?');
	  $linkurl = $link.'.html?utm_source=google&amp;utm_medium=cpc';
	if (strpos($reference, 'MUCK-') === false && strpos($reference, 'LECHA-') === false){
		$weight = $product->getWeight().'kg';
	}else{
		$weight = '5.0000kg';
	}
	$categories = $_product->getCategoryIds();
	$type = '';
	foreach($categories as $_category_id):
	//$_category = Mage::getModel('catalog/category')->load($_category_id);
	$_category = $objectManager->create('Magento\Catalog\Model\Category')->load($_category_id);
	$type = $_category->getName();
	endforeach;
	  $product_row = array(
      $reference,
	  $product->getName(),
	  $product->getAttributeText('manufacturer'), 
	  $condition, 
	  $_product->getData('c2c_rrp'), 
	  $qty, 
	  $mpn, 
	  $availability, 
	  $descriptionShort, 
	  $expiration, 
	  $image,
	  $linkurl,
	  $_product->getFinalPrice(),
	  $type, 
	  $gtin,
	  $weight
      );
	  
//$product = Mage::getModel('catalog/product')->load($singleId);
/*if ($singleId) {
	$product = $objectManager->create('\Magento\Catalog\Model\Product')->load($singleId);
	$parentIds = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getId());
	foreach($parentIds as $parentId) {
	    $groupProduct = $objectManager->create('\Magento\Catalog\Model\Product')->load($parentId);
	    $groupPath = $groupProduct->getProductUrl();

	    echo $groupPath;
	}
}*/	  
      fputcsv( $fp, $product_row, $delimiter = "|");
   }
}
 echo "Done";
?>
