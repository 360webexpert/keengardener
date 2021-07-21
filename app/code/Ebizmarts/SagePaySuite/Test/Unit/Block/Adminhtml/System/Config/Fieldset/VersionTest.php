<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Adminhtml\System\Config\Fieldset;

class VersionTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    // @codingStandardsIgnoreStart
    const MODULE_VERSION = '1.2.8';

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }
    // @codingStandardsIgnoreEnd

    public function testGetVersion()
    {
        $moduleVersionMock =

        $moduleVersionMock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Config\ModuleVersion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleVersionMock
            ->expects($this->once())
            ->method('getModuleVersion')
            ->with('Ebizmarts_SagePaySuite')
            ->willReturn(self::MODULE_VERSION);

        /** @var \Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version $versionBlock */
        $versionBlock = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version',
            [
                'moduleVersion' => $moduleVersionMock
            ]
        );

        $this->assertEquals(
            self::MODULE_VERSION,
            $versionBlock->getVersion()
        );
    }

    public function testGetTemplate()
    {
        /** @var \Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version $versionBlock */
        $versionBlock = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version'
        );

        $this->assertEquals(
            'Ebizmarts_SagePaySuite::system/config/fieldset/version.phtml',
            $versionBlock->getTemplate()
        );
    }

    public function testRenderBlank()
    {
        /** @var \Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version $versionBlock */
        $versionBlock = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version'
        );

        $factoryMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()->getMock();

        $args = [
            'factoryElement'    => $factoryMock,
            'factoryCollection' => $collectionFactoryMock,
            'escaper'           => $escaperMock,
            'data'              => ['group' => ['id' => 'not_version']]
        ];
        $renderMock = $this->getMockForAbstractClass(\Magento\Framework\Data\Form\Element\AbstractElement::class, $args)
        ->setMethods(['getData']);

        $this->assertEquals('', $versionBlock->render($renderMock));
    }

    public function testRender()
    {
        /** @var \Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version $versionBlock */
        $versionBlock = $this
            ->getMockBuilder(\Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset\Version::class)
            ->setMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $versionBlock->expects($this->once())->method('toHtml')->willReturn('some html code');

        $factoryMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()->getMock();

        $args = [
            'factoryElement'    => $factoryMock,
            'factoryCollection' => $collectionFactoryMock,
            'escaper'           => $escaperMock,
            'data'              => ['group' => ['id' => 'version']]
        ];
        $renderMock = $this->getMockForAbstractClass(\Magento\Framework\Data\Form\Element\AbstractElement::class, $args)
            ->setMethods(['getData']);

        $this->assertEquals('some html code', $versionBlock->render($renderMock));
    }
}
