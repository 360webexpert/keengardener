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

namespace Mageplaza\LayeredNavigation\Plugin\Model\Adapter;

use Closure;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\LayeredNavigation\Helper\Data;

/**
 * Class Preprocessor
 * @package Mageplaza\LayeredNavigation\Model\Plugin\Adapter
 */
class Preprocessor
{
    /**
     * @type Data
     */
    protected $_moduleHelper;

    /**
     * @type ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    private $invalidFields = ['mp_is_new', 'mp_on_sale'];

    /**
     * Preprocessor constructor.
     *
     * @param Data $moduleHelper
     * @param ObjectManagerInterface $objectManager
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Data $moduleHelper,
        ObjectManagerInterface $objectManager,
        ProductMetadataInterface $productMetadata
    ) {
        $this->_moduleHelper   = $moduleHelper;
        $this->objectManager   = $objectManager;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject
     * @param Closure $proceed
     * @param $filter
     * @param $isNegation
     * @param $query
     *
     * @return string
     */
    public function aroundProcess(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject,
        Closure $proceed,
        $filter,
        $isNegation,
        $query
    ) {
        if (!$this->_moduleHelper->isEnabled()) {
            return $proceed($filter, $isNegation, $query);
        }
        if (in_array($filter->getField(), $this->invalidFields, true)) {
            return '';
        }
        if (($filter->getField() === 'category_ids')) {
            $filterValue = implode(',', array_map([$this, 'validateCatIds'], explode(',', $filter->getValue())));

            $version = $this->productMetadata->getVersion();
            if (version_compare($version, '2.1.13', '>=') && version_compare($version, '2.1.15', '<=')) {
                return 'category_products_index.category_id IN (' . $filterValue . ')';
            }

            return 'category_ids_index.category_id IN (' . $filterValue . ')';
        }

        return $proceed($filter, $isNegation, $query);
    }

    /**
     * @param $catId
     *
     * @return int
     */
    protected function validateCatIds($catId)
    {
        return (int) $catId;
    }
}
