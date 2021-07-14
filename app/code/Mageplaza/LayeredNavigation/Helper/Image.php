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
 * @package     Mageplaza_LayeredNavigation
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigation\Helper;

use Exception;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\Media;

/**
 * Class Image
 * @package Mageplaza\LayeredNavigation\Helper
 */
class Image extends Media
{
    const TEMPLATE_MEDIA_PATH = 'mageplaza/layernavigation';
    const TOOLTIP_THUMBNAIL_PATH = 'tooltip/thumbnail';

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * Image constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $imageFactory
     * @param Repository $assetRepo
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageFactory,
        Repository $assetRepo
    ) {
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $objectManager, $storeManager, $filesystem, $uploaderFactory, $imageFactory);
    }

    /**
     * @param $data
     * @param string $fileName
     * @param null $oldImage
     *
     * @return $this|Media
     * @throws FileSystemException
     */
    public function uploadThumbnail(&$data, $fileName = 'image', $oldImage = null)
    {
        $type = self::TOOLTIP_THUMBNAIL_PATH;
        if (isset($data[$fileName]) && isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
            if ($oldImage) {
                $this->removeImage($oldImage, $type);
            }
            $data[$fileName] = '';
        } else {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileName]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);

                $path = $this->getBaseMediaPath($type);

                $image = $uploader->save(
                    $this->mediaDirectory->getAbsolutePath($path)
                );

                if ($oldImage) {
                    $this->removeImage($oldImage, $type);
                }

                $data[$fileName] = $this->_prepareFile($image['file']);
            } catch (Exception $e) {
                $this->_logger->critical($e);
                $data[$fileName] = isset($data[$fileName]['value']) ? $data[$fileName]['value'] : '';
            }
        }

        return $this;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getImageSrc($value)
    {
        $src = $this->getMediaPath($value, self::TOOLTIP_THUMBNAIL_PATH);
        if (!preg_match("/^http\:\/\/|https\:\/\//", $src)) {
            $src = $this->getMediaUrl($src);
        }

        return $src;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return string
     */
    public function getTooltipThumbnail($filter)
    {
        $url = '';
        try {
            $thumbnail = $filter->getAttributeModel()->getData('tooltip_thumbnail');

            if ($thumbnail) {
                $url = $this->getImageSrc($thumbnail);
            } else {
                $url = $this->_assetRepo->getUrl('Mageplaza_LayeredNavigation::css/images/tooltip.svg');
            }
        } catch (LocalizedException $e) {
            $this->_logger->error($e);
        }

        return $url;
    }
}
