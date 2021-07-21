<?php
declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config;

class CspWhitelistingTest extends \PHPUnit\Framework\TestCase
{
    public function testCspUrlsValues()
    {
        $filePath = realpath(__DIR__) . '/../../../../etc/';
        $dom = new \DOMDocument();
        $dom->load($filePath . 'csp_whitelist.xml');

        $converted = $this->convert($dom); //@see \Magento\WebsiteRestriction\Model\Config\Converter::convert

        $this->assertArrayNotHasKey('register', $converted);

        $this->assertArrayHasKey('script-src', $converted);
        $this->assertArrayHasKey('style-src', $converted);
        $this->assertArrayHasKey('img-src', $converted);
        $this->assertArrayHasKey('connect-src', $converted);
        $this->assertArrayHasKey('font-src', $converted);
        $this->assertArrayHasKey('frame-src', $converted);
        $this->assertArrayHasKey('form-action', $converted);

        $this->assertContains('*.sagepay.com', $converted['script-src']);
        $this->assertContains('*.sagepay.com', $converted['style-src']);
        $this->assertContains('*.sagepay.com', $converted['img-src']);
        $this->assertContains('*.paypal.com', $converted['img-src']);
        $this->assertContains('*.sagepay.com', $converted['connect-src']);
        $this->assertContains('*.paypal.com', $converted['connect-src']);
        $this->assertContains('*.sagepay.com', $converted['font-src']);
        $this->assertContains('*.sagepay.com', $converted['frame-src']);
        $this->assertContains('*.sagepay.com', $converted['form-action']);
    }

    private function convert($source)
    {
        $output = [];
        /** @var \DOMNodeList $actions */
        $policies = $source->getElementsByTagName('policy');
        /** @var DOMNode $actionConfig */
        foreach ($policies as $policyConfig) {
            $policyId = $policyConfig->attributes->getNamedItem('id')->nodeValue;
            $actions = $policyConfig->getElementsByTagName('value');

            foreach ($actions as $actionConfig) {
                $policyValue = $actionConfig->textContent;
                $output[$policyId][] = $policyValue;
            }
        }
        return $output;
    }
}
