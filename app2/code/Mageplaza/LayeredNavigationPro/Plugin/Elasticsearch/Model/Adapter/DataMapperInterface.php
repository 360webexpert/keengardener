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
 * @package     Mageplaza_LayeredNavigationPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter;

/**
 * Interface DataMapperInterface
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter
 */
interface DataMapperInterface
{
    /**
     * Prepare index data for using in search engine metadata
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []);

    /**
     * @return bool
     */
    public function isAllowed();
}
