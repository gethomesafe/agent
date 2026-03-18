<?php

namespace Jenssegers\Agent;

use BadMethodCallException;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use \Detection\MobileDetect;

class Agent extends MobileDetect
{
    /**
     * List of desktop devices.
     * @var array
     */
    protected static $desktopDevices = [
        'Macintosh' => 'Macintosh',
    ];

    /**
     * List of additional operating systems.
     * @var array
     */
    protected static $additionalOperatingSystems = [
        'Windows' => 'Windows',
        'Windows NT' => 'Windows NT',
        'OS X' => 'Mac OS X',
        'Debian' => 'Debian',
        'Ubuntu' => 'Ubuntu',
        'Macintosh' => 'PPC',
        'OpenBSD' => 'OpenBSD',
        'Linux' => 'Linux',
        'ChromeOS' => 'CrOS',
    ];

    /**
     * List of additional browsers.
     * @var array
     */
    protected static $additionalBrowsers = [
        'Opera Mini' => 'Opera Mini',
        'Opera' => 'Opera|OPR',
        'Edge' => 'Edge|Edg',
        'Coc Coc' => 'coc_coc_browser',
        'UCBrowser' => 'UCBrowser',
        'Vivaldi' => 'Vivaldi',
        'Chrome' => 'Chrome',
        'Firefox' => 'Firefox',
        'Safari' => 'Safari',
        'IE' => 'MSIE|IEMobile|MSIEMobile|Trident/[.0-9]+',
        'Netscape' => 'Netscape',
        'Mozilla' => 'Mozilla',
        'WeChat'  => 'MicroMessenger',
    ];

    /**
     * List of additional properties.
     * @var array
     */
    protected static $additionalProperties = [
        // Operating systems
        'Windows' => 'Windows NT [VER]',
        'Windows NT' => 'Windows NT [VER]',
        'OS X' => 'OS X [VER]',
        'BlackBerryOS' => ['BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'],
        'AndroidOS' => 'Android [VER]',
        'ChromeOS' => 'CrOS x86_64 [VER]',

        // Browsers
        'Opera Mini' => 'Opera Mini/[VER]',
        'Opera' => [' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]', 'Opera [VER]'],
        'Netscape' => 'Netscape/[VER]',
        'Mozilla' => 'rv:[VER]',
        'IE' => ['IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];', 'rv:[VER]'],
        'Edge' => ['Edge/[VER]', 'Edg/[VER]'],
        'Vivaldi' => 'Vivaldi/[VER]',
        'Coc Coc' => 'coc_coc_browser/[VER]',
    ];

    public const VERSION_TYPE_STRING = 'text';
    public const VERSION_TYPE_FLOAT = 'float';

    /**
     * @var CrawlerDetect
     */
    protected static $crawlerDetect;

    /**
     * @return CrawlerDetect
     */
    public function getCrawlerDetect()
    {
        if (static::$crawlerDetect === null) {
            static::$crawlerDetect = new CrawlerDetect();
        }

        return static::$crawlerDetect;
    }

    public static function getBrowsers(): array
    {
        return static::mergeRules(
            static::$additionalBrowsers,
            static::$browsers
        );
    }

    public static function getOperatingSystems(): array
    {
        return static::mergeRules(
            static::$operatingSystems,
            static::$additionalOperatingSystems
        );
    }

    public static function getPlatforms()
    {
        return static::mergeRules(
            static::$operatingSystems,
            static::$additionalOperatingSystems
        );
    }

    public static function getDesktopDevices()
    {
        return static::$desktopDevices;
    }

    public static function getProperties(): array
    {
        return static::mergeRules(
            static::$additionalProperties,
            static::$properties
        );
    }

    /**
     * Get accept languages.
     * @param string $acceptLanguage
     * @return array
     */
    public function languages($acceptLanguage = null)
    {
        if ($acceptLanguage === null) {
            $acceptLanguage = $this->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        }

        if (!$acceptLanguage) {
            return [];
        }

        $languages = [];

        // Parse accept language string.
        foreach (explode(',', $acceptLanguage) as $piece) {
            $parts = explode(';', $piece);
            $language = strtolower($parts[0]);
            $priority = empty($parts[1]) ? 1. : floatval(str_replace('q=', '', $parts[1]));

            $languages[$language] = $priority;
        }

        // Sort languages by priority.
        arsort($languages);

        return array_keys($languages);
    }

    /**
     * Match a detection rule and return the matched key.
     * @param  array $rules
     * @param  string|null $userAgent
     * @return string|bool
     */
    protected function findDetectionRulesAgainstUA(array $rules, $userAgent = null)
    {
        $ua = $userAgent ?? $this->getUserAgent() ?? '';

        // Loop given rules
        foreach ($rules as $key => $regex) {
            if (empty($regex)) {
                continue;
            }

            // v4 match() only accepts strings; join array patterns
            if (is_array($regex)) {
                $regex = implode('|', $regex);
            }

            // Check match
            if ($this->match($regex, $ua)) {
                return $key ?: reset($this->matchesArray);
            }
        }

        return false;
    }

    /**
     * Override is() to also check Agent's augmented rule sets (browsers, desktop OS, desktop devices).
     */
    public function is(string $key): bool
    {
        $allRules = array_merge(
            static::getBrowsers(),
            static::getPlatforms(),
            static::getDesktopDevices(),
        );

        if (isset($allRules[$key])) {
            $regex = is_array($allRules[$key]) ? implode('|', $allRules[$key]) : $allRules[$key];
            $ua = $this->getUserAgent() ?? '';
            if ($ua !== '' && $this->match($regex, $ua)) {
                return true;
            }
        }

        return parent::is($key);
    }

    /**
     * Get the browser name.
     * @param  string|null $userAgent
     * @return string|bool
     */
    public function browser($userAgent = null)
    {
        return $this->findDetectionRulesAgainstUA(static::getBrowsers(), $userAgent);
    }

    /**
     * Get the platform name.
     * @param  string|null $userAgent
     * @return string|bool
     */
    public function platform($userAgent = null)
    {
        return $this->findDetectionRulesAgainstUA(static::getPlatforms(), $userAgent);
    }

    /**
     * Get the device name.
     * @param  string|null $userAgent
     * @return string|bool
     */
    public function device($userAgent = null)
    {
        $rules = static::mergeRules(
            static::getDesktopDevices(),
            static::getPhoneDevices(),
            static::getTabletDevices()
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /**
     * Check if the device is a desktop computer.
     * @param  string|null $userAgent deprecated
     * @param  array $httpHeaders deprecated
     * @return bool
     */
    public function isDesktop($userAgent = null, $httpHeaders = null)
    {
        // Check specifically for cloudfront headers if the useragent === 'Amazon CloudFront'
        if ($this->getUserAgent() === 'Amazon CloudFront') {
            $cfHeaders = $this->getCloudFrontHttpHeaders();
            if (array_key_exists('HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER', $cfHeaders)) {
                return $cfHeaders['HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER'] === 'true';
            }
        }

        if ($userAgent !== null) {
            $this->setUserAgent($userAgent);
        }
        if ($httpHeaders !== null) {
            $this->setHttpHeaders($httpHeaders);
        }

        return !$this->isMobile() && !$this->isTablet() && !$this->isRobot();
    }

    /**
     * Check if the device is a mobile phone.
     * @param  string|null $userAgent deprecated
     * @param  array $httpHeaders deprecated
     * @return bool
     */
    public function isPhone($userAgent = null, $httpHeaders = null)
    {
        if ($userAgent !== null) {
            $this->setUserAgent($userAgent);
        }
        if ($httpHeaders !== null) {
            $this->setHttpHeaders($httpHeaders);
        }

        return $this->isMobile() && !$this->isTablet();
    }

    /**
     * Get the robot name.
     * @param  string|null $userAgent
     * @return string|bool
     */
    public function robot($userAgent = null)
    {
        if ($this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent)) {
            return ucfirst($this->getCrawlerDetect()->getMatches());
        }

        return false;
    }

    /**
     * Check if device is a robot.
     * @param  string|null $userAgent
     * @return bool
     */
    public function isRobot($userAgent = null)
    {
        return $this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent);
    }

    /**
     * Get the device type
     * @param null $userAgent
     * @param null $httpHeaders
     * @return string
     */
    public function deviceType($userAgent = null, $httpHeaders = null)
    {
        if ($this->isDesktop($userAgent, $httpHeaders)) {
            return "desktop";
        } elseif ($this->isPhone($userAgent, $httpHeaders)) {
            return "phone";
        } elseif ($this->isTablet($userAgent, $httpHeaders)) {
            return "tablet";
        } elseif ($this->isRobot($userAgent)) {
            return "robot";
        }

        return "other";
    }

    public function version(string $propertyName, string $type = self::VERSION_TYPE_STRING): string|float|bool
    {
        if (empty($propertyName)) {
            return false;
        }

        // set the $type to the default if we don't recognize the type
        if ($type !== self::VERSION_TYPE_STRING && $type !== self::VERSION_TYPE_FLOAT) {
            $type = self::VERSION_TYPE_STRING;
        }

        $properties = self::getProperties();

        // Check if the property exists in the properties array.
        if (true === isset($properties[$propertyName])) {

            // Prepare the pattern to be matched.
            // Make sure we always deal with an array (string is converted).
            $properties[$propertyName] = (array) $properties[$propertyName];

            foreach ($properties[$propertyName] as $propertyMatchString) {
                if (is_array($propertyMatchString)) {
                    $propertyMatchString = implode("|", $propertyMatchString);
                }

                $propertyPattern = str_replace('[VER]', static::VERSION_REGEX, $propertyMatchString);

                // Identify and extract the version.
                preg_match(sprintf('#%s#is', $propertyPattern), $this->userAgent, $match);

                if (false === empty($match[1])) {
                    $version = ($type === self::VERSION_TYPE_FLOAT ? $this->prepareVersionNo($match[1]) : $match[1]);

                    return $version;
                }
            }
        }

        return false;
    }

    /**
     * Merge multiple rules into one array.
     * @param array $all
     * @return array
     */
    protected static function mergeRules(...$all)
    {
        $merged = [];

        foreach ($all as $rules) {
            foreach ($rules as $key => $value) {
                if (empty($merged[$key])) {
                    $merged[$key] = $value;
                } elseif (is_array($merged[$key])) {
                    $merged[$key][] = $value;
                } else {
                    $merged[$key] .= '|' . $value;
                }
            }
        }

        return $merged;
    }
}
