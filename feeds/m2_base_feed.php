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
// load all products from the default store which are enabled and visible in the catalog view
$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$products = $productCollection->addAttributeToSelect('*');
$products->addAttributeToFilter('status', 1);//enabled
$products->addAttributeToFilter('visibility', 4);
$products->load();

// open the csv file and write the header in the first line
//$fp = fopen('/home/keengardener/public_html/feeds/base_feed.csv', 'w');
$fp = fopen(dirname(__FILE__).'/base_feed.csv', 'w');
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
 
   //check if the product is visible
   if ($product->getVisibility() == 4 )
   {
   	  $reference = $objectManager->create('\Magento\Catalog\Model\Product')->load($_product->getId())->getSku();
      $image = $mediaUrl.'catalog/product'.$product->getImage();
      $expiration = date('Y-m-d', strtotime("+30 days"));
      $condition = 'new';
	  $stockItem = $_product->getExtensionAttributes()->getStockItem();
   	  $qty = $stockItem->getQty(); 
   	  $backorders = $stockItem->getBackorders(); 
	  $mpn = substr( $reference, strpos( $reference, '-') + 1);
	  $direct = $product->getAttributeText('c2c_direct_despatch');
	  //$oos = $product->getOosAllowOrder();
	  //$gtin = $product->getEan();
	  $gtin = $product->getResource()->getAttribute('c2c_ean_1')->setStoreId(0)->getFrontend()->getValue($product);
	  
	  //Removes any items marked out of stock on website *Google Base Fix 27/04*
	  if ($backorders == 0 && $qty <= 0):
		$availability = 'out of stock';  
	  
	  //Filters brands that are available for sale on pre-order or direct from being listed as out of stock and will pay for marketing
	  elseif ($qty <= 0 && strpos($reference, 'MUCK-') === false && strpos($reference, 'PALR-') === false && strpos($reference, 'TGV') === false && strpos($reference, 'BULL-') === false && strpos($reference, 'NORL-') === false && strpos($reference, 'GABL-') === false && strpos($reference, 'BUSC-') === false && strpos($reference, 'BEEF-') === false && strpos($reference, 'COBR-') === false && strpos($reference, 'LECHA-') === false && strpos($reference, 'CHAR-') === false && strpos($reference, 'NAPO-') === false && strpos($reference, 'LEIS-') === false && strpos($reference, 'FORE-') === false && strpos($reference, 'KETE-') === false && strpos($reference, 'HAND-') === false && strpos($reference, 'GRAK-') === false && strpos($reference, 'PRIM-') === false && strpos($reference, 'EGO-') === false && strpos($reference, 'ALLETT-') === false && strpos($reference, 'LAND-') === false && strpos($reference, 'GRAN-') === false && strpos($reference, 'BILL-') === false && strpos($reference, 'EGO-') === false && strpos($reference, 'GHALL-') === false && strpos($reference, 'LIFE-') === false):
		$availability = 'out of stock';
	
	  //Force Stock status as In stock for all items not filtered out above
		else:
		$availability = 'in stock';
		endif;
	  
	  $description = preg_replace( "/\r|\n/", "", $_product->getShortDescription());
	  $descriptionShort = strip_tags($description, '<p><a><br>');
	  $link = strtok($product->getProductUrl(), '?');
	  $linkurl = $link.'?utm_source=google&amp;utm_medium=cpc';
	 	if (strpos($reference, 'MUCK-') === false && strpos($reference, 'LECHA-') === false){
			$weight = $product->getWeight().'kg';
		}else{
			$weight = '5.0000kg';
		}
		$categories = $_product->getCategoryIds();
		foreach($categories as $_category_id):
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
      fputcsv( $fp, $product_row, $delimiter = "|");
   }
}
 echo "Finished";
?>
