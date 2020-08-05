<?php
namespace Dapatchi\LaravelCore\Helpers;

class StringHelper
{
    /**
     * Removes instances of multiple forward slashes, so // becomes /, /// becomes /
     *
     * @param string $string
     *
     * @return string
     */
    public static function deslash(string $string)
    {
        return preg_replace('#/+#', '/', $string);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function deslashUrl(string $url)
    {
        $parsedUrl = parse_url($url);

        $urlNoHost = $parsedUrl['host'] .
            (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') .
            (isset($parsedUrl['path']) ? $parsedUrl['path'] : '') .
            (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

        return $parsedUrl['scheme'] . '://' . self::deslash($urlNoHost);
    }
}
