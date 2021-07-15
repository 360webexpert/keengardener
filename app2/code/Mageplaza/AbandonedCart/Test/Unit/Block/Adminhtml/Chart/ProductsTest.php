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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Test\Unit\Block\Adminhtml\Chart;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Mageplaza\AbandonedCart\Block\Adminhtml\Chart\Products;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\ProductReport\Collection;
use Mageplaza\AbandonedCart\Model\ResourceModel\Grid\ProductReport\CollectionFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ProductsTest
 * @package Mageplaza\AbandonedCart\Test\Unit\Block\Adminhtml\Chart
 */
class ProductsTest extends TestCase
{
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $productReportsCollection;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $helperData;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var Products
     */
    private $object;

    protected function setUp()
    {
        $this->context                  = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productReportsCollection = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->helperData               = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->context->method('getRequest')->willReturn($this->request);

        $this->object = new Products(
            $this->context,
            $this->productReportsCollection,
            $this->helperData,
            []
        );
    }

    /**
     * @throws Exception
     */
    public function testGetCollectionData()
    {
        $params = [
            'mpFilter'  => [
                'startDate' => 'a',
                'endDate'   => 'b',
                'period'    => 'p'
            ],
            'startDate' => 'c',
            'endDate'   => 'd'
        ];

        $this->request->method('getParams')->willReturn($params);

        $count      = 0;
        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $this->productReportsCollection->expects($this->at($count++))->method('create')->willReturn($collection);

        $collection->method('setOrder')->willReturnSelf();
        $collection->method('setPageSize')->willReturnSelf();

        /** @var Document|PHPUnit_Framework_MockObject_MockObject $item */
        $item = $this->getMockBuilder(Document::class)
            ->setMethods(['getProductId', 'getProductName'])
            ->disableOriginalConstructor()->getMock();
        $collection->method('getItems')->willReturn([$item]);

        $prodCollection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $this->productReportsCollection->expects($this->at($count++))->method('create')->willReturn($prodCollection);

        $prodCollection->method('setGroupByPeriod')->willReturnSelf();
        $prodCollection->method('setProductId')->willReturnSelf();
        $prodCollection->method('load')->willReturnSelf();

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getAbandonedTime'])
            ->disableOriginalConstructor()->getMock();
        $dataObject->method('getAbandonedTime')->willReturn(11);
        $data = [
            'd-m' => $dataObject
        ];
        $this->helperData->method('getIntervals')->willReturn($data);

        /** @var Document $item */
        $prodItem = $this->getMockBuilder(Document::class)
            ->setMethods(['getPeriodTime', 'getAbandonedTime'])
            ->disableOriginalConstructor()->getMock();
        $prodCollection->method('getItems')->willReturn([]);
        $prodItem->method('getPeriodTime')->willReturn('d-m');

        $periods             = ['d-m'];
        $abandonedTime       = [11];
        $chartData['labels'] = $periods;

        $prodName = 'prod-name';
        $item->method('getProductName')->willReturn($prodName);

        $productsData = [
            [
                'label'           => $prodName,
                'data'            => $abandonedTime,
                'borderWidth'     => 1,
                'fill'            => false,
                'borderColor'     => '#20a8d8',
                'backgroundColor' => '#20a8d8'
            ]
        ];

        $chartData['datasets'] = $productsData;

        $this->assertEquals($chartData, $this->object->getCollectionData());
    }
}
