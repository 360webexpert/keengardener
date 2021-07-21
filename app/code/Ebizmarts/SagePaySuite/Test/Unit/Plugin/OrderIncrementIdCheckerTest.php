<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Plugin;

use Ebizmarts\SagePaySuite\Plugin\OrderIncrementIdChecker;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Sales\Model\OrderIncrementIdChecker as MagentoOrderIncrementIdChecker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class OrderIncrementIdCheckerTest extends TestCase
{
    const ORDER_INCREMENT_ID = '1000000010';
    const STORE_ID = 1;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var OrderInterface */
    private $orderInterfaceMock;

    /** @var MagentoOrderIncrementIdChecker */
    private $magentoOrderIncrementIdCheckerMock;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderInterfaceMock = $this
            ->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->magentoOrderIncrementIdCheckerMock = $this
            ->getMockBuilder(MagentoOrderIncrementIdChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider isIncrementIdProvider
     */
    public function testAfterIsIncrementIdUsed($data)
    {
        $filterMock1 = $this
            ->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock2 = $this
            ->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterBuilderMock = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setField')
            ->withConsecutive(['increment_id'], ['store_id'])
            ->willReturnSelf();
        $filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setConditionType')
            ->with('eq')
            ->willReturnSelf();

        $storeMock = $this
            ->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(self::STORE_ID);

        $storeManagerMock = $this
            ->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setValue')
            ->withConsecutive([self::ORDER_INCREMENT_ID], [self::STORE_ID])
            ->willReturnSelf();
        $filterBuilderMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($filterMock1, $filterMock2);

        $searchCriteriaBuilderMock = $this
            ->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock
            ->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive([$filterMock1], [$filterMock2]);

        $searchCriteriaMock = $this
            ->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $orderSearchResultInterfaceMock = $this
            ->getMockBuilder(OrderSearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($orderSearchResultInterfaceMock);

        $orderSearchResultInterfaceMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($data['expectGetItems']);

        $sut = $this->objectManagerHelper->getObject(
            OrderIncrementIdChecker::class,
            [
                'orderRepository' => $orderRepositoryMock,
                'storeManager' => $storeManagerMock,
                'filterBuilder' => $filterBuilderMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock
            ]
        );

        $this->assertEquals(
            $data['expectedReturn'],
            $sut->afterIsIncrementIdUsed(
                $this->magentoOrderIncrementIdCheckerMock,
                $data['resultParam'],
                self::ORDER_INCREMENT_ID
            )
        );
    }

    public function isIncrementIdProvider()
    {
        return [
            'testTrnDontExist' => [
                [
                    'resultParam' => true,
                    'expectedReturn' => false,
                    'expectGetItems' => []
                ]
            ],
            'testTrnExist' => [
                [
                    'resultParam' => true,
                    'expectedReturn' => true,
                    'expectGetItems' => [$this->orderInterfaceMock]
                ]
            ]
        ];
    }
}
