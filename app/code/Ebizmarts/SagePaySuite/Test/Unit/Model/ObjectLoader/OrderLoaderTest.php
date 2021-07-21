<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\ObjectLoader;

use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\TestCase;

class OrderLoaderTest extends TestCase
{
    const RESERVER_ORDER_ID = "10000000024";
    const STORE_ID = 1;
    const ORDER_ID = 231;

    /** @var Quote */
    private $quoteMock;

    /** @var Order */
    private $orderMock;

    /** @var OrderRepository */
    private $orderRepositoryMock;

    /** @var RepositoryQuery */
    private $repositoryQueryMock;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    private $sut;

    public function setUp()
    {
        $this->orderRepositoryMock = $this
            ->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repositoryQueryMock = $this
            ->getMockBuilder(RepositoryQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchCriteriaMock = $this
            ->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchResultMock = $this
            ->getMockBuilder(SearchResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this
            ->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock
            ->expects($this->once())
            ->method('getReservedOrderId')
            ->willReturn(self::RESERVER_ORDER_ID);
        $this->quoteMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(self::STORE_ID);

        $this->orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filters = [
            ['field' => 'increment_id', 'value' => self::RESERVER_ORDER_ID, 'conditionType' => 'eq'],
            ['field' => 'store_id', 'value' => self::STORE_ID, 'conditionType' => 'eq']
        ];
        $this->repositoryQueryMock
            ->expects($this->once())
            ->method('buildSearchCriteriaWithAND')
            ->with($filters)
            ->willReturn($searchCriteriaMock);

        $this->orderRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->willReturn($searchResultMock);

        $searchResultMock
            ->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(1);

        $searchResultMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->orderMock]);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->sut = $this->objectManagerHelper->getObject(
            OrderLoader::class,
            [
                'orderRepository' => $this->orderRepositoryMock,
                'repositoryQuery' => $this->repositoryQueryMock,
            ]
        );
    }

    public function testLoadOrderFromQuoteSuccess()
    {
        $this->orderMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(self::ORDER_ID);

        $this->sut->loadOrderFromQuote($this->quoteMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testLoadOrderFromQuoteException()
    {
        $this->orderMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->sut->loadOrderFromQuote($this->quoteMock);
    }
}
