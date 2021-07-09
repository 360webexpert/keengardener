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

namespace Mageplaza\Shopbybrand\Model\Import;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\CatalogImportExport\Model\Import\Uploader;
use Magento\CatalogImportExport\Model\Import\UploaderFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class Brand
 * @package Mageplaza\Shopbybrand\Model\Import
 */
class Brand extends AbstractEntity
{
    const COL_ATTR_CODE                = 'attribute_code';
    const COL_NAME                     = 'brand_name';
    const COL_STORE_ID                 = 'store_id';
    const COL_VALUE                    = 'label';
    const COL_URL_KEY                  = 'url_key';
    const COL_IMAGE                    = 'image';
    const COL_FEATURED                 = 'is_featured';
    const COL_SHORT_DESCRIPTION        = 'short_description';
    const COL_DESCRIPTION              = 'description';
    const COL_STATIC_BLOCK             = 'static_block';
    const COL_META_TITLE               = 'meta_title';
    const COL_META_DESCRIPTION         = 'meta_description';
    const COL_META_KEYWORDS            = 'meta_keywords';
    const COL_SWATCH_TYPE              = 'swatch_type';
    const COL_SWATCH_VALUE             = 'swatch_value';
    const SCOPE_DEFAULT                = 0;
    const SCOPE_STORE                  = 1;
    const ERROR_TITLE_IS_EMPTY         = 'Empty TITLE';
    const ERROR_INVALID_ATTRIBUTE_CODE = 'invalidAttributeCode';
    const ERROR_INVALID_BRAND_OPTION   = 'invalidBrandOption';
    const ERROR_INVALID_VALUE          = 'invalidValue';
    const ERROR_INVALID_ATTRIBUTE      = 'invalidAttribute';

    /**
     * @var CollectionFactory
     */
    protected $_optionCollection;

    /**
     * @var Data
     */
    protected $_brandHelper;

    /**
     * @var Repository
     */
    protected $_productRepository;

    /**
     * @var array \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_fileUploader;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var Media
     */
    protected $swatchHelper;

    /** @inheritdoc */
    protected $_availableBehaviors = [
        Import::BEHAVIOR_ADD_UPDATE,
        Import::BEHAVIOR_REPLACE,
        Import::BEHAVIOR_DELETE
    ];

    /** @inheritdoc */
    protected $_messageTemplates = [
        self::ERROR_INVALID_ATTRIBUTE_CODE => 'Invalid value in Attribute Code column',
        self::ERROR_INVALID_BRAND_OPTION   => 'Invalid value in Brand name column',
        self::ERROR_INVALID_VALUE          => 'Invalid value in Value column',
        self::ERROR_INVALID_ATTRIBUTE      => 'Attribute does not exist'
    ];

    /** @inheritdoc */
    protected $validColumnNames = [
        self::COL_NAME,
        self::COL_STORE_ID
    ];

    /**
     * @var array Attribute list
     */
    protected $_attributeList = [];

    /**
     * @var array Option List
     */
    protected $_optionList = [];

    /** @inheritdoc */
    protected $masterAttributeCode = 'brand_name';

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * Brand constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Repository $productRespository
     * @param CollectionFactory $optionCollection
     * @param Data $brandHelper
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Media $swatchHelper
     * @param array $data
     *
     * @throws FileSystemException
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        Repository $productRespository,
        CollectionFactory $optionCollection,
        Data $brandHelper,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Media $swatchHelper,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_productRepository = $productRespository;
        $this->_optionCollection = $optionCollection;
        $this->_brandHelper = $brandHelper;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->swatchHelper = $swatchHelper;
        $this->errorMessageTemplates = array_merge($this->errorMessageTemplates, $this->_messageTemplates);

        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityTypeCode()
    {
        return 'mageplaza_brand';
    }

    /**
     * {@inheritDoc}
     */
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }

    /**
     * {@inheritDoc}
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE === $this->getBehavior()) {
            $this->_deleteBrands();
        } else {
            $this->_saveBrands();
        }

        return true;
    }

    /**
     * Delete rows
     *
     * @return $this
     */
    protected function _deleteBrands()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToDelete = [];

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum) || (self::SCOPE_DEFAULT != $rowData[self::COL_STORE_ID])) {
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                if (array_key_exists($rowData[self::COL_NAME], $entitiesToDelete)) {
                    continue;
                }

                $options = $this->processBrandOptions($this->getAttributeCode($rowData), self::SCOPE_DEFAULT, $rowNum);
                $optionToDelete = array_filter($options, function ($option) use (&$rowData) {
                    return $option['default_value'] === $rowData[self::COL_NAME];
                });
                if (is_array($optionToDelete) && count($optionToDelete)) {
                    $option = array_shift($optionToDelete);
                    $entitiesToDelete[$rowData[self::COL_NAME]] = isset($option['option_id'])
                        ? $option['option_id'] : null;
                }
            }
            if (count($entitiesToDelete)) {
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_resource->getTableName('eav_attribute_option'),
                    $this->_connection->quoteInto('option_id IN (?)', array_values(array_filter($entitiesToDelete)))
                );
            }
        }

        return $this;
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        if (!isset($rowData[self::COL_NAME]) || !$rowData[self::COL_NAME]) {
            $this->addRowError(self::ERROR_INVALID_BRAND_OPTION, $rowNum);

            return false;
        }

        if (isset($rowData[self::COL_ATTR_CODE]) && $rowData[self::COL_ATTR_CODE]) {
            try {
                $this->_productRepository->get($rowData[self::COL_ATTR_CODE]);
            } catch (Exception $e) {
                $this->addRowError(self::ERROR_INVALID_ATTRIBUTE, $rowNum);

                return false;
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param $attributeCode
     * @param $storeId
     * @param $rowNum
     *
     * @return bool|mixed
     */
    private function processBrandOptions($attributeCode, $storeId, $rowNum)
    {
        if (!isset($this->_attributeList[$attributeCode])) {
            try {
                $attribute = $this->_productRepository->get($attributeCode);

                /** @var Collection $options */
                $options = $this->_optionCollection->create()
                    ->setAttributeFilter($attribute->getId())
                    ->setStoreFilter($storeId);

                $this->_attributeList[$attributeCode] = $options->getData();
            } catch (Exception $e) {
                $this->addRowError(self::ERROR_INVALID_ATTRIBUTE, $rowNum);

                return false;
            }
        }

        return $this->_attributeList[$attributeCode];
    }

    /**
     * @param $rowData
     *
     * @return mixed
     */
    private function getAttributeCode($rowData)
    {
        return (isset($rowData[self::COL_ATTR_CODE]) && $rowData[self::COL_ATTR_CODE])
            ? $rowData[self::COL_ATTR_CODE] : $this->_brandHelper->getAttributeCode(self::SCOPE_DEFAULT);
    }

    /**
     * Save Rows
     */
    protected function _saveBrands()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $brandData = [
                $this->_resource->getTableName('mageplaza_brand')             => [],
                $this->_resource->getTableName('eav_attribute_option_swatch') => []
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(self::ERROR_TITLE_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                if (!isset($this->_optionList[$rowData[self::COL_NAME]])) {
                    $storeId = $rowData[self::COL_STORE_ID];
                    $attributeCode = $this->getAttributeCode($rowData);
                    $optionList = $this->processBrandOptions($attributeCode, $storeId, $rowNum);
                    $optionFilters = array_filter($optionList, function ($option) use (&$rowData) {
                        return $option['default_value'] == $rowData[self::COL_NAME];
                    });
                    if (is_array($optionFilters) && count($optionFilters)) {
                        $option = array_shift($optionFilters);
                        $this->_optionList[$rowData[self::COL_NAME]] = $option['option_id'];
                    } else {
                        $attribute = $this->_productRepository->get($attributeCode);
                        $this->_connection->insert(
                            $this->_resource->getTableName('eav_attribute_option'),
                            ['attribute_id' => $attribute->getId(), 'sort_order' => 0]
                        );
                        $this->_optionList[$rowData[self::COL_NAME]] = $this->_connection->lastInsertId(
                            $this->_resource->getTableName('eav_attribute_option')
                        );
                    }
                }

                $rowData['option_id'] = $this->_optionList[$rowData[self::COL_NAME]];
                $rowData['value'] = (isset($rowData[self::COL_VALUE]) && $rowData[self::COL_VALUE])
                    ? $rowData[self::COL_VALUE]
                    : (($rowData[self::COL_STORE_ID] === self::SCOPE_DEFAULT) ? $rowData[self::COL_NAME] : '');

                if (isset($rowData['value']) && $rowData['value']) {
                    /**
                     * Table attribute_option_value doesn't have index
                     * (option_id, store_id) so cannot insert on duplicate row
                     */
                    $this->_connection->delete(
                        $this->_resource->getTableName('eav_attribute_option_value'),
                        ['option_id =?' => $rowData['option_id'], 'store_id =?' => $rowData['store_id']]
                    );
                    $this->_connection->insert($this->_resource->getTableName('eav_attribute_option_value'), [
                        'option_id' => $rowData['option_id'],
                        'store_id'  => $rowData['store_id'],
                        'value'     => $rowData['value']
                    ]);
                }

                if (isset($rowData['image']) && $rowData['image']) {
                    $res = $this->_getUploader('brand')->move($rowData['image']);
                    $rowData['image'] = 'mageplaza/brand' . $res['file'];
                }

                $brandData[$this->_resource->getTableName('mageplaza_brand')][] = [
                    'option_id'         => $rowData['option_id'],
                    'store_id'          => $rowData['store_id'],
                    'url_key'           => isset($rowData['url_key']) ? $rowData['url_key'] : null,
                    'image'             => isset($rowData['image']) ? $rowData['image'] : null,
                    'is_featured'       => isset($rowData['is_featured']) ? $rowData['is_featured'] : null,
                    'short_description' => isset($rowData['short_description']) ? $rowData['short_description'] : null,
                    'description'       => isset($rowData['description']) ? $rowData['description'] : null,
                    'static_block'      => isset($rowData['static_block']) ? $rowData['static_block'] : null,
                    'meta_title'        => isset($rowData['meta_title']) ? $rowData['meta_title'] : null,
                    'meta_keywords'     => isset($rowData['meta_keywords']) ? $rowData['meta_keywords'] : null,
                    'meta_description'  => isset($rowData['meta_description']) ? $rowData['meta_description'] : null,
                ];

                if (($rowData[self::COL_STORE_ID] == self::SCOPE_DEFAULT)
                    && isset($rowData[self::COL_SWATCH_TYPE])
                    && isset($rowData[self::COL_SWATCH_VALUE])
                ) {
                    if (($rowData[self::COL_SWATCH_TYPE] == Swatch::SWATCH_TYPE_VISUAL_IMAGE)
                        && $rowData[self::COL_SWATCH_VALUE]
                    ) {
                        $res = $this->_getUploader('swatch')->move($rowData[self::COL_SWATCH_VALUE], true);
                        $rowData[self::COL_SWATCH_VALUE] = $res['file'];
                    }
                    $brandData[$this->_resource->getTableName('eav_attribute_option_swatch')][] = [
                        'option_id' => $rowData['option_id'],
                        'store_id'  => self::SCOPE_DEFAULT,
                        'type'      => $rowData[self::COL_SWATCH_TYPE],
                        'value'     => $rowData[self::COL_SWATCH_VALUE]
                    ];
                }
            }
            foreach ($brandData as $key => $data) {
                if (count($data)) {
                    $updateFields = ($key == $this->_resource->getTableName('eav_attribute_option_value')) ? ['value'] :
                        ($key == $this->_resource->getTableName('eav_attribute_option_swatch') ? ['type', 'value'] :
                            [
                                'url_key',
                                'image',
                                'is_featured',
                                'short_description',
                                'description',
                                'static_block',
                                'meta_title',
                                'meta_keywords',
                                'meta_description'
                            ]);

                    $this->_connection->insertOnDuplicate($this->_connection->getTableName($key), $data, $updateFields);
                }
            }
        }
    }

    /**
     * Returns an object for upload a media files
     *
     * @param $type
     *
     * @return Uploader
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function _getUploader($type)
    {
        if (!isset($this->_fileUploader[$type]) || ($this->_fileUploader[$type] === null)) {
            $fileUploader = $this->_uploaderFactory->create();

            $fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            if (!empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])) {
                $tmpPath = $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
            } else {
                $tmpPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath('import');
            }

            if (!$fileUploader->setTmpDir($tmpPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = ($type === 'brand') ? "mageplaza/brand" : 'attribute/swatch';
            $destinationPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath($destinationDir);

            $this->_mediaDirectory->create($destinationPath);
            if (!$fileUploader->setDestDir($destinationPath)) {
                throw new LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }

            $this->_fileUploader[$type] = $fileUploader;
        }

        return $this->_fileUploader[$type];
    }
}
