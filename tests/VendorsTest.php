<?php

use Jenssegers\Agent\Agent;
use PHPUnit\Framework\TestCase;

class VendorsTest extends TestCase
{
    protected Agent $detect;

    public function setUp(): void
    {
        $this->detect = new Agent();
    }

    public function testIPhone()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1');
        $this->assertTrue($this->detect->isMobile());
        $this->assertTrue($this->detect->is('iPhone'));
        $this->assertFalse($this->detect->isTablet());
    }

    public function testIPad()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (iPad; CPU OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1');
        $this->assertTrue($this->detect->isTablet());
        $this->assertFalse($this->detect->isMobile());
    }

    public function testAndroidPhone()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Mobile Safari/537.36');
        $this->assertTrue($this->detect->isMobile());
        $this->assertFalse($this->detect->isTablet());
    }

    public function testAndroidTablet()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (Linux; U; Android 4.0.3; en-us; ASUS Transformer Pad TF300T Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Safari/534.30');
        $this->assertTrue($this->detect->isTablet());
        $this->assertFalse($this->detect->isMobile());
    }

    public function testBlackBerry()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+');
        $this->assertTrue($this->detect->isMobile());
        $this->assertTrue($this->detect->is('BlackBerry'));
    }

    public function testSamsungGalaxy()
    {
        $this->detect->setUserAgent('Mozilla/5.0 (Linux; Android 9; SM-G960F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Mobile Safari/537.36');
        $this->assertTrue($this->detect->isMobile());
    }
}
