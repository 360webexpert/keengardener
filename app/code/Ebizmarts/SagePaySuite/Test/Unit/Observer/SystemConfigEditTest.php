<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Observer;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Observer\SystemConfigEdit;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SystemConfigEditTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testNoChecksRun()
    {
        $observerMock = $this->makeObserverMock();

        $eventMock = $this->makeEventMock();
        $eventMock
            ->expects($this->once())
            ->method('__call')
            ->with(
                $this->equalTo('getRequest')
            )
            ->willReturn($this->makeRequestMock('anothersection'));

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $suiteHelperMock = $this->makeSuiteHelperMock();
        $suiteHelperMock->expects($this->never())->method('verify');

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->never())->method('addError');

        $reportingApiMock = $this->makeReportingApiMock();

        $observerModel = $this->objectManagerHelper->getObject(
            SystemConfigEdit::class,
            [
                'suiteHelper'    => $suiteHelperMock,
                'messageManager' => $messageManagerMock,
                'reportingApi'   => $reportingApiMock,
            ]
        );

        $observerModel->execute($observerMock);
    }

    public function testLicenseAndReportingApiChecks()
    {
        $observerMock = $this->makeObserverMock();

        $eventMock = $this->makeEventMock();
        $this->configSectionPaymentAssert($eventMock);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $suiteHelperMock = $this->makeSuiteHelperMock();
        $suiteHelperMock->expects($this->once())->method('verify')->willReturn(true);

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->never())->method('addError');

        $reportingApiMock = $this->makeReportingApiMock();
        $reportingApiMock->expects($this->once())->method('getVersion')->willReturnSelf();

        $observerModel = $this->objectManagerHelper->getObject(
            SystemConfigEdit::class,
            [
                'suiteHelper'    => $suiteHelperMock,
                'messageManager' => $messageManagerMock,
                'reportingApi'   => $reportingApiMock,
            ]
        );

        $observerModel->execute($observerMock);
    }

    public function testLicenseAndReportingApiChecksFail()
    {
        $observerMock = $this->makeObserverMock();

        $eventMock = $this->makeEventMock();
        $this->configSectionPaymentAssert($eventMock);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $suiteHelperMock = $this->makeSuiteHelperMock();
        $suiteHelperMock->expects($this->once())->method('verify')->willReturn(false);

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock->expects($this->exactly(2))->method('addWarning')
            ->withConsecutive(
                ['Your Opayo Suite license is invalid.'],
                ['Can not establish connection with Opayo API.']
            );

        $reportingApiMock = $this->makeReportingApiMock();
        $reportingApiMock->expects($this->once())->method('getVersion')
        ->willThrowException(new \Exception('An error has ocurred.'));

        $observerModel = $this->objectManagerHelper->getObject(
            SystemConfigEdit::class,
            [
                'suiteHelper'    => $suiteHelperMock,
                'messageManager' => $messageManagerMock,
                'reportingApi'   => $reportingApiMock,
            ]
        );

        $observerModel->execute($observerMock);
    }

    public function testReportingApiApiException()
    {
        $observerMock = $this->makeObserverMock();

        $eventMock = $this->makeEventMock();
        $this->configSectionPaymentAssert($eventMock);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $suiteHelperMock = $this->makeSuiteHelperMock();
        $suiteHelperMock->expects($this->once())->method('verify')->willReturn(true);

        $warningMessage = "Invalid Opayo API credentials. <a target='_blank' href='http://wiki.ebizmarts.com/configuration-guide-1'>Configuration guide</a>";

        $messageManagerMock = $this->makeMessageManagerMock();
        $messageManagerMock
            ->expects($this->once())
            ->method('addWarning')
            ->with($warningMessage);

        $reportingApiMock = $this->makeReportingApiMock();
        $reportingApiMock->expects($this->once())->method('getVersion')
        ->willThrowException(new ApiException(
            new Phrase('Invalid Opayo API credentials.')
        ));

        $observerModel = $this->objectManagerHelper->getObject(
            SystemConfigEdit::class,
            [
                'suiteHelper'    => $suiteHelperMock,
                'messageManager' => $messageManagerMock,
                'reportingApi'   => $reportingApiMock,
            ]
        );

        $observerModel->execute($observerMock);
    }


    //Invalid Sage Pay API credentials.

    private function makeRequestMock($configSection)
    {
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()->getMock();

        $requestMock->expects($this->once())->method('getParam')->with('section')->willReturn($configSection);

        return $requestMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeObserverMock()
    {
        $observerMock = $this->getMockBuilder(Observer::class)->disableOriginalConstructor()->getMock();

        return $observerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeEventMock()
    {
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();

        return $eventMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeSuiteHelperMock()
    {
        $suiteHelperMock = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();

        return $suiteHelperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeMessageManagerMock()
    {
        $messageManagerMock = $this->getMockBuilder(ManagerInterface::class)->disableOriginalConstructor()->getMock();

        return $messageManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeReportingApiMock()
    {
        $reportingApiMock = $this->getMockBuilder(Reporting::class)->disableOriginalConstructor()->getMock();

        return $reportingApiMock;
    }

    /**
     * @param $eventMock
     */
    private function configSectionPaymentAssert($eventMock)
    {
        $eventMock->expects($this->once())->method('__call')->with($this->equalTo('getRequest'))->willReturn($this->makeRequestMock('payment'));
    }
}
