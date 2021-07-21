<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Commands;

class CheckFraudCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckFraud()
    {
        $inputMock = $this->getMockBuilder(\Symfony\Component\Console\Input\InputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $outputMock = $this->getMockBuilder(\Symfony\Component\Console\Output\OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $outputMock->expects($this->exactly(2))->method('writeln')
        ->withConsecutive(
            ["<comment>Checking fraud...</comment>"],
            ["<info>Done.</info>"]
        );

        $cronMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Model\Cron::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cronMock->expects($this->once())->method('checkFraud');

        $appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $appStateMock->expects($this->once())->method('setAreaCode')->with('adminhtml');

        $subject = new \Ebizmarts\SagePaySuite\Commands\CheckFraudCommand($cronMock, $appStateMock);

        $this->invokeMethod(
            $subject,
            'execute',
            [$inputMock, $outputMock]
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
