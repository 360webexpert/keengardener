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

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface;

/**
 * Class Term
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\SearchAdapter\Aggregation\Builder
 */
class Term
{
    /**
     * @var BucketBuilderInterface[]
     */
    protected $bucketBuilders = [];

    /**
     * Term constructor.
     *
     * @param array $bucketBuilders
     */
    public function __construct(array $bucketBuilders = [])
    {
        $this->bucketBuilders = $bucketBuilders;
    }

    /**
     * @param $subject
     * @param \Closure $closure
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        $subject,
        \Closure $closure,
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $builtCustomFilter = $this->buildCustomFiltersData($bucket, $queryResult);
        return $builtCustomFilter ?: $closure($bucket, $dimensions, $queryResult, $dataProvider);
    }

    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     * @return array
     */
    private function buildCustomFiltersData(RequestBucketInterface $bucket, array $queryResult)
    {
        if (isset($this->bucketBuilders[$bucket->getField()])) {
            $builder = $this->bucketBuilders[$bucket->getField()];
            if ($builder instanceof BucketBuilderInterface) {
                return $builder->build($bucket, $queryResult);
            }
        }
    }
}
