<?php
/**
 * Import Multiple Images during Product Import
 *
 */
 
class Mage_InteractiveImage_Model_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /**
     * Save product (import)
     *
     * @param array $importData
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function saveRow(array $importData)
    {
        $product = $this->getProductModel()
            ->reset();
 
        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
            }
        }
        else {
            $store = $this->getStoreByCode($importData['store']);
        }
 
        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
        }
 
        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);
 
        if ($productId) {
            $product->load($productId);
        }
        else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();
 
            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);
 
            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                    Mage::throwException($message);
                }
            }
        }
 
        $this->setProductTypeInstance($product);
 
        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }
 
        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }
 
        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }
 
        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            $websiteCodes = split(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }
 
        foreach ($importData as $field => $value) {
			
			
			$attribute = $this->getAttribute($field);
			
		
				
			$custom_options = array();
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (in_array($field, $this->_imageFields)) {
                continue;
            }
 
            
			
            if (!$attribute) {
				
				
				
                continue;
            }
 
            $isArray = false;
            $setValue = $value;
 
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = split(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }
 
            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }
 
            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);
 
                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }
			
			
            $product->setData($field, $setValue);
        }
 
        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }
 
        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                }
                else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);
		
/*$my_server_img = 'http://1000x.newrockplanet.com/M.9806-S1.jpg';
$img = imagecreatefromjpeg($my_server_img);
$path = Mage::getBaseDir('media') . DS . 'import'. DS;
imagejpeg($img, $path);*/
		
		
		
$imageN = 'M.9806-S1.jpg';
$image_url = 'http://1000x.newrockplanet.com/'.$imageN;
$server_root = Mage::getBaseDir('media') . DS . 'import';
define('DIRECTORY',$server_root);
echo DIRECTORY;
echo $destination = Mage::getBaseDir('media') . DS . 'import'. DS .$imageN;
exit;
$ch = curl_init($image_url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
$raw=curl_exec($ch);
curl_close($ch);
exit;


$file = fopen($destination,"w+");
fputs($file,$raw);
if(fclose($file)) {
	echo "downloaded";
	}
echo  $raw;

exit;
$newImg = imagecreatefromstring($raw);
//echo $img =  imagecreatefromstring($raw);

imagejpeg($newImg, DIRECTORY.'/M.9806-S1.jpg',100);

 
        $imageData = array();
        foreach ($this->_imageFields as $field) {
            if (!empty($importData[$field]) && $importData[$field] != 'no_selection') {
                if (!isset($imageData[$importData[$field]])) {
                    $imageData[$importData[$field]] = array();
                }
               echo  $imageData[$importData[$field]][] = $field;
            }
        }
		

	
		//print_r($imageData["image"]);
		
		

		
		/** EXTERNAL IMAGE IMPORT - START **/
//$image_url  = $importData['external_image_url']; //get external image url from csv

/*$filepath   = Mage::getBaseDir('media') . DS . 'import';
$image_url  = "http://1000x.newrockplanet.com/M.9806-S1.jpg"; //get external image url from csv

echo $content = file_get_contents($image_url);
exit;
file_put_contents($filepath.'/M.9806-S1.jpg', $content);*/

/* 
$server_root = Mage::getBaseDir('media') . DS . 'import//';

$ch = curl_init('http://1000x.newrockplanet.com/M.9806-S1.jpg');
$fp = fopen($server_root."M_9806-S1.jpg", 'wb');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);	
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);
fclose($fp);
echo $server_root;
exit;   */


/**
 * Add image to media gallery
 *
 * @param string        $file              file path of image in file system
 * @param string|array  $mediaAttribute    code of attribute with type 'media_image',
 *                                         leave blank if image should be only in gallery
 * @param boolean       $move              if true, it will move source file
 * @param boolean       $exclude           mark image as disabled in product page view
 */
$product->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
/** EXTERNAL IMAGE IMPORT - END **/
		
		
	
		
        foreach ($imageData as $file => $fields) {
            try {
				$product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $file, $fields);
				}
            catch (Exception $e) {}
        }
		/**
		 * Allows you to import multiple images for each product.
		 * Simply add a 'gallery' column to the import file, and separate
		 * each image with a semi-colon.
		 */
	        try {
	                $galleryData = explode(';',$importData["gallery"]);
	                foreach($galleryData as $gallery_img)
					/**
					 * @param directory where import image resides
					 * @param leave 'null' so that it isn't imported as thumbnail, base, or small
					 * @param false = the image is copied, not moved from the import directory to it's new location
					 * @param false = not excluded from the front end gallery
					 */
	                {
	                        $product->addImageToMediaGallery(Mage::getBaseDir('media') . DS . 'import' . $gallery_img, null, false, false);
	                }
	            }
	        catch (Exception $e) {}
		/* End Modification */

        
		
		
		
		
		$product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);
 		
			
		
        $product->save();
 		
        return true;
    }
}