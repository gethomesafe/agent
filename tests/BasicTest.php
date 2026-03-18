<?php

use Jenssegers\Agent\Agent;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    protected Agent $detect;

    public function setUp(): void
    {
        $this->detect = new Agent();
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(Agent::class, $this->detect);
        $this->assertInstanceOf(\Detection\MobileDetect::class, $this->detect);
    }

    public function testSetAndGetUserAgent()
    {
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)';
        $this->detect->setUserAgent($ua);
        $this->assertEquals($ua, $this->detect->getUserAgent());
    }

    public function testSetHttpHeaders()
    {
        $this->detect->setHttpHeaders([
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 10; SM-G975F)',
        ]);
        $this->assertNotEmpty($this->detect->getHttpHeaders());
    }

    public function testIsMobileReturnsBool()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)');
        $this->assertTrue($this->detect->isMobile());
    }

    public function testIsTabletReturnsBool()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X)');
        $this->assertTrue($this->detect->isTablet());
    }

    public function testIsMethodWithKnownRule()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1');
        $this->assertTrue($this->detect->is('iPhone'));
    }

    public function testDesktopIsNotMobile()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/90.0.4430.93 Safari/537.36');
        $this->assertFalse($this->detect->isMobile());
        $this->assertFalse($this->detect->isTablet());
    }
}
