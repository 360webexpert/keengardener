<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Test\Unit\Helper;

use Amasty\ShippingTableRates\Helper\Data;
use Amasty\ShippingTableRates\Test\Unit\Traits;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DataTest
 *
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const REGIONS = [
        [
            'value' => [
                'country_id' => 0,
                'label' => 'testLabel'
            ]
        ]
    ];

    const ALL_COUNTRIES = [
        'value' => [
            'country_id' => 0,
            'label' => 'All/testLabel',
            'value' => 'All'
        ]
    ];

    /**
     * @var Data
     */
    private $model;

    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var CountryCollection
     */
    private $countryCollection;

    protected function setUp(): void
    {
        $this->countryCollectionFactory = $this->createMock(CountryCollectionFactory::class);
        $this->countryCollection = $this->createMock(CountryCollection::class);

        $this->countryCollectionFactory->expects($this->any())
            ->method('create')->willReturn($this->countryCollection);

        $this->countryCollection->expects($this->any())
            ->method('toOptionArray')->willReturn(self::ALL_COUNTRIES);

        $this->model = $this->getObjectManager()->getObject(
            Data::class,
            [
                'countryCollectionFactory' => $this->countryCollectionFactory
            ]
        );
    }

    /**
     * @covers Data::getDataFromZip
     *
     * @dataProvider getDataFromZipDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetDataFromZip($zip, $expectedResult)
    {
        /** @var Data $helper */
        $helper = $this->getObjectManager()->getObject(Data::class);
        $result = $helper->getDataFromZip($zip);
        $this->assertEquals($expectedResult, $result['district']);
        $this->assertArrayHasKey('district', $result);
    }

    /**
     * Data provider for getDataFromZip test
     * @return array
     */
    public function getDataFromZipDataProvider()
    {
        return [
            [85001, 85001],
            [72201, 72201],
            [-95814, 95814],
        ];
    }

    /**
     * @covers Data::_addCountriesToStates
     *
     * @throws \ReflectionException
     */
    public function testAddCountriesToStates()
    {
        /** @var AbstractCollection|MockObject $collection */
        $collection = $this->createMock(AbstractCollection::class);
        $collection->expects($this->any())->method('toOptionArray')->willReturn([]);

        $result = $this->model->addCountriesToStates(self::REGIONS);

        $this->assertEquals($result,self::REGIONS);
    }
}
