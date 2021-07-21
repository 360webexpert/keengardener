<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Helper;

use Ebizmarts\SagePaySuite\Helper\RepositoryQuery;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;

class RepositoryQueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var FilterGroupBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterGroupBuilderMock;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var RepositoryQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryQuery;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->contextMock = $this
            ->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this
            ->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterGroupBuilderMock = $this
            ->getMockBuilder(FilterGroupBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->repositoryQuery = $objectManagerHelper->getObject(
            RepositoryQuery::class,
            [
                "context"               => $this->contextMock,
                "filterBuilder"         => $this->filterBuilderMock,
                "filterGroupBuilder"    => $this->filterGroupBuilderMock,
                "searchCriteriaBuilder" => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    // @codingStandardsIgnoreEnd

    public function testBuildSearchCriteriaWithOR()
    {
        $filters = [
            ['field' => 'increment_id', 'value' => '00000001', 'conditionType' => 'eq']
        ];
        $filtersAsObjects = [];

        $this->filterBuilderMock->method('setField')->with($filters[0]['field'])->willReturnSelf();
        $this->filterBuilderMock->method('setValue')->with($filters[0]['value'])->willReturnSelf();
        $this->filterBuilderMock->method('setConditionType')->with($filters[0]['conditionType'])->willReturnSelf();
        $this->filterBuilderMock->method('create')->willReturnSelf();

        $filtersAsObjects[0] = $this->filterBuilderMock;
        $this->filterGroupBuilderMock->method('setFilters')->with($filtersAsObjects)->willReturnSelf();
        $this->filterGroupBuilderMock->method('create')->willReturnSelf();

        $this->searchCriteriaBuilderMock->method('setFilterGroups')->with([$this->filterGroupBuilderMock])
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->method('create')->willReturnSelf();

        $this->assertNotNull($this->repositoryQuery->buildSearchCriteriaWithOR($filters));
    }

    public function testBuildSearchCriteriaWithAND()
    {
        $filters = [
            ['field' => 'increment_id', 'value' => '000000003', 'conditionType' => 'eq'],
            ['field' => 'store_id', 'value' => '1', 'conditionType' => 'eq']
        ];
        $filterBuilt1 = [$filters[0]];
        $filterBuilt2 = [$filters[1]];
        $filterGroup1 = [[[$filterBuilt1]]];
        $filterGroup2 = [[[$filterBuilt2]]];
        $expectedSearchCriteria = [
            [
                $filterGroup1,
                $filterGroup2
            ]
        ];

        $this->filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setField')
            ->withConsecutive([$filters[0]['field']], [$filters[1]['field']])
            ->willReturnSelf();
        $this->filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setValue')
            ->withConsecutive([$filters[0]['value']], [$filters[1]['value']])
            ->willReturnSelf();
        $this->filterBuilderMock
            ->expects($this->exactly(2))
            ->method('setConditionType')
            ->withConsecutive([$filters[0]['conditionType']], [$filters[1]['conditionType']])
            ->willReturnSelf();
        $this->filterBuilderMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($filterBuilt1, $filterBuilt2);

        $this->filterGroupBuilderMock
            ->expects($this->exactly(2))
            ->method('setFilters')
            ->withConsecutive([[$filterBuilt1]], [[$filterBuilt2]])
            ->willReturnSelf();
        $this->filterGroupBuilderMock
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($filterGroup1, $filterGroup2);

        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('setFilterGroups')
            ->with([$filterGroup1, $filterGroup2])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($expectedSearchCriteria);

        $this->assertEquals($expectedSearchCriteria, $this->repositoryQuery->buildSearchCriteriaWithAND($filters));
    }
}
