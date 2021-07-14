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
 * @package     Mageplaza_
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter;

/**
 * Class AdditionalBatchDataMapper
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter
 */
class AdditionalBatchDataMapper
{
    /**
     * @var DataMapperInterface[]
     */
    protected $dataMappers = [];

    /**
     * AdditionalDataMapper constructor.
     * @param array $dataMappers
     */
    public function __construct(array $dataMappers = [])
    {
        $this->dataMappers = $dataMappers;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $subject
     * @param callable $proceed
     * @param array $documentData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        array $documentData,
        $storeId,
        $context = []
    ) {
        $documentData = $proceed($documentData, $storeId, $context);
        foreach ($documentData as $productId => $document) {
            $context['document'] = $document;
            foreach ($this->dataMappers as $key => $mapper) {
                if ($mapper instanceof DataMapperInterface && $mapper->isAllowed()) {
                    // @codingStandardsIgnoreLine
                    $document = array_merge($document, $mapper->map($productId, $document, $storeId, $context));
                }
            }
            $documentData[$productId] = $document;
        }

        return $documentData;
    }
}
