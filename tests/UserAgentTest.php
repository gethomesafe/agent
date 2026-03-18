<?php

use Jenssegers\Agent\Agent;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
    protected Agent $detect;

    private array $mobileUserAgents = [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Mobile Safari/537.36',
        'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+',
        'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
    ];

    private array $tabletUserAgents = [
        'Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; U; Android 4.0.3; en-us; ASUS Transformer Pad TF300T Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30',
    ];

    private array $desktopUserAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0',
    ];

    public function setUp(): void
    {
        $this->detect = new Agent();
    }

    public function testMobileUserAgents()
    {
        foreach ($this->mobileUserAgents as $ua) {
            $this->detect->setUserAgent($ua);
            $this->assertTrue($this->detect->isMobile(), "Expected mobile: $ua");
        }
    }

    public function testTabletUserAgents()
    {
        foreach ($this->tabletUserAgents as $ua) {
            $this->detect->setUserAgent($ua);
            $this->assertTrue($this->detect->isTablet(), "Expected tablet: $ua");
        }
    }

    public function testDesktopUserAgents()
    {
        foreach ($this->desktopUserAgents as $ua) {
            $this->detect->setUserAgent($ua);
            $this->assertFalse($this->detect->isMobile(), "Expected not mobile: $ua");
            $this->assertFalse($this->detect->isTablet(), "Expected not tablet: $ua");
        }
    }

    public function testSetUserAgentOverridesOnSubsequentCalls()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)');
        $this->assertTrue($this->detect->isMobile());

        $this->detect->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $this->assertFalse($this->detect->isMobile());
    }
}
