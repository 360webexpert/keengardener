<?php

namespace Ebizmarts\SagePaySuite\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\ObjectManager;

class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var string **/
    private $subjectModuleName;

    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->subjectModuleName = 'Ebizmarts_SagePaySuite';
        /** @var ObjectManager objectManager */
        $this->objectManager = ObjectManager::getInstance();
    }

    public function testTheModuleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $this->assertArrayHasKey('Ebizmarts_SagePaySuite', $registrar->getPaths(ComponentRegistrar::MODULE));
    }

    public function testTheModuleIsConfiguredInTheTestEnvironment()
    {
        /** @var $moduleList ModuleList */
        $moduleList = $this->objectManager->create(ModuleList::class);
        $this->assertTrue($moduleList->has($this->subjectModuleName));
    }

    public function testTheModuleIsConfiguredInTheRealEnvironment()
    {
        // The tests by default point to the wrong config directory for this test.
        $directoryList = $this->objectManager->create(
            \Magento\Framework\App\Filesystem\DirectoryList::class,
            ['root' => BP]
        );
        $deploymentConfigReader = $this->objectManager->create(
            \Magento\Framework\App\DeploymentConfig\Reader::class,
            ['dirList' => $directoryList]
        );
        $deploymentConfig = $this->objectManager->create(
            \Magento\Framework\App\DeploymentConfig::class,
            ['reader' => $deploymentConfigReader]
        );

        /** @var $moduleList ModuleList */
        $moduleList = $this->objectManager->create(
            ModuleList::class,
            ['config' => $deploymentConfig]
        );
        $this->assertTrue($moduleList->has($this->subjectModuleName));
    }
}
