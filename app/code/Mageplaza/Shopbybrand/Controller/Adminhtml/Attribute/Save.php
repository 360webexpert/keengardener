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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class Save
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute
 */
class Save extends Action
{

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type Filesystem
     */
    protected $_fileSystem;

    /**
     * @var AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @type PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var BrandHelper
     */
    protected $_brandHelper;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param BrandHelper $brandHelper
     * @param BrandFactory $brandFactory
     * @param Filesystem $fileSystem
     * @param AdapterFactory $adapterFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        BrandHelper $brandHelper,
        BrandFactory $brandFactory,
        Filesystem $fileSystem,
        AdapterFactory $adapterFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_brandHelper = $brandHelper;
        $this->_brandFactory = $brandFactory;
        $this->_fileSystem = $fileSystem;
        $this->_imageFactory = $adapterFactory;
        $this->_resultPageFactory = $resultPageFactory;
    }

    /**
     * execute
     */
    public function execute()
    {
        $result = ['success' => true];
        $data = $this->getRequest()->getPostValue();
        $this->_uploadImage($data, $result);
        $defaultStore = Store::DEFAULT_STORE_ID;

        if ($result['success']) {
            $data['url_key'] = isset($data['url_key']) ? $this->_brandHelper->formatUrlKey($data['url_key']) : '';
            try {
                $brand = $this->_brandFactory->create();
                if ($data['store_id'] != $defaultStore) {
                    $defaultBrand = $brand->loadByOption($data['option_id'], $defaultStore);
                    if (!$defaultBrand->getBrandId()) {
                        $brand->setData($data)
                            ->setId(null)
                            ->setStoreId($defaultStore)
                            ->save();
                    }
                }

                $brand->setData($data)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();

                $resultPage = $this->_resultPageFactory->create();
                $result['html'] = $resultPage->getLayout()->getBlock('brand.attribute.html')
                    ->setOptionData($brand->getData())
                    ->toHtml();

                $result['message'] = __('Brand option has been saved successfully.');
            } catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = $e->getMessage();//__('An error occur. Please try again later.');
            }
        }

        $this->getResponse()->representJson(BrandHelper::jsonEncode($result));
    }

    /**
     * @param $data
     * @param $result
     *
     * @return $this
     */
    protected function _uploadImage(&$data, &$result)
    {
        if (isset($data['image']['delete']) && $data['image']['delete']) {
            $data['image'] = '';
        } else {
            try {
                $uploader = $this->_objectManager->create(
                    \Magento\MediaStorage\Model\File\Uploader::class,
                    ['fileId' => 'image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);

                $image = $uploader->save(
                    $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath(BrandHelper::BRAND_MEDIA_PATH)
                );

                $data['image'] = BrandHelper::BRAND_MEDIA_PATH . '/' . $image['file'];
                $this->resizeImage($data['image'], 80);
            } catch (Exception $e) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
                if ((int)$e->getCode() !== Uploader::TMP_NAME_EMPTY) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    /**
     * @param $image
     * @param null $width
     * @param null $height
     *
     * @throws Exception
     */
    public function resizeImage($image, $width = null, $height = null)
    {
        $absolutePath = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath() . $image;

        $imageResized = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('mageplaza/resized/' . $width . '/') . $image;

        //create image factory...
        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);
        //destination folder
        $destination = $imageResized;
        //save image
        $imageResize->save($destination);
    }
}
