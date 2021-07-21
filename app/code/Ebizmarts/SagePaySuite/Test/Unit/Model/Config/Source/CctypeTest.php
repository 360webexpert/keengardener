<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

class CctypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config\Source\Cctype
     */
    private $cctypeModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->cctypeModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config\Source\Cctype',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testGetAllowedTypes()
    {
        $this->assertEquals(
            ['VI', 'MC', 'MI', 'AE', 'DN', 'JCB'],
            $this->cctypeModel->getAllowedTypes()
        );
    }
}
