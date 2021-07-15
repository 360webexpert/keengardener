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

namespace Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\BucketBuilder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface;

/**
 * Class OnSale
 * @package Mageplaza\LayeredNavigationPro\Plugin\Elasticsearch\Model\Adapter\BucketBuilder
 */
class OnSale implements BucketBuilderInterface
{
    const ON_SALE_INDEX = 1;

    /**
     * @param RequestBucketInterface $bucket
     * @param array $queryResult
     *
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $queryResult
    ) {
        $values = [];
        if (isset($queryResult['aggregations'][$bucket->getName()]['buckets'])) {
            foreach ($queryResult['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
                if ($resultBucket['key'] == self::ON_SALE_INDEX) {
                    $values[self::ON_SALE_INDEX] = [
                        'value' => self::ON_SALE_INDEX,
                        'count' => $resultBucket['doc_count'],
                    ];
                }
            }
        }
        return $values;
    }
}
