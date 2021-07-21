<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config;

class WebsiteRestrictionsTest extends \PHPUnit\Framework\TestCase
{
    const TOTAL_ROUTES = 10;

    public function testTheModuleIsKnownAndEnabledInTheRealEnvironment()
    {
        $filePath = realpath(__DIR__) . '/../../../../etc/';
        $dom = new \DOMDocument();
        $dom->load($filePath . 'webrestrictions.xml');

        $converted = $this->convert($dom); //@see \Magento\WebsiteRestriction\Model\Config\Converter::convert

        $this->assertArrayNotHasKey('register', $converted);
        $this->assertArrayHasKey('generic', $converted);
        $this->assertCount(self::TOTAL_ROUTES, $converted['generic']);
        $this->assertContains('sagepaysuite_server_success', $converted['generic']);
        $this->assertContains('sagepaysuite_server_notify', $converted['generic']);
        $this->assertContains('sagepaysuite_server_cancel', $converted['generic']);
        $this->assertContains('sagepaysuite_server_redirectToSuccess', $converted['generic']);
        $this->assertContains('sagepaysuite_pi_callback3D', $converted['generic']);
        $this->assertContains('sagepaysuite_pi_callback3DV2', $converted['generic']);
        $this->assertContains('sagepaysuite_paypal_callback', $converted['generic']);
        $this->assertContains('sagepaysuite_paypal_processing', $converted['generic']);
        $this->assertContains('sagepaysuite_form_failure', $converted['generic']);
        $this->assertContains('sagepaysuite_form_success', $converted['generic']);
    }

    private function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $actions */
        $actions = $source->getElementsByTagName('action');
        /** @var DOMNode $actionConfig */
        foreach ($actions as $actionConfig) {
            $actionPath = $actionConfig->attributes->getNamedItem('path')->nodeValue;
            $type = $actionConfig->attributes->getNamedItem('type')->nodeValue;
            $output[$type][] = $actionPath;
        }
        return $output;
    }
}
