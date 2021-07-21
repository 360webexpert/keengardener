<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 2020-02-21
 * Time: 11:31
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\Cart;

use Ebizmarts\SagePaySuite\Controller\Cart\Recover;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RecoverTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $recoverCartMock = $this
            ->getMockBuilder(RecoverCart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $recoverCartMock
            ->expects($this->once())
            ->method('setShouldCancelOrder')
            ->with(false)
            ->willReturnSelf();
        $recoverCartMock
            ->expects($this->once())
            ->method('execute');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $recover = $objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Controller\Cart\Recover',
            [
                'recoverCart' => $recoverCartMock
            ]
        );

        $recover->execute();
    }

}
