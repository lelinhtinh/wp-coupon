<?php

function get_discount_string($item)
{
    $currency_symbol = '₫';
    return empty($item['value'])
        ? ''
        : ($item['type'] === 'numeric'
            ? number_format($item['value'], 0, '', ',')
            : $item['value']
        ) . ($item['type'] === 'numeric' ? $currency_symbol : '%');
}

function tz_strtodate($str, $to_timestamp = false)
{
    // This function behaves a bit like PHP's StrToTime() function, but taking into account the Wordpress site's timezone
    // CAUTION: It will throw an exception when it receives invalid input - please catch it accordingly
    // From https://mediarealm.com.au/

    $tz_string = get_option('timezone_string');
    $tz_offset = get_option('gmt_offset', 0);

    if (!empty($tz_string)) {
        // If site timezone option string exists, use it
        $timezone = $tz_string;
    } elseif ($tz_offset == 0) {
        // get UTC offset, if it isn’t set then return UTC
        $timezone = 'UTC';
    } else {
        $timezone = $tz_offset;

        if (substr($tz_offset, 0, 1) != '-' && substr($tz_offset, 0, 1) != '+' && substr($tz_offset, 0, 1) != 'U') {
            $timezone = '+' . $tz_offset;
        }
    }

    $datetime = new DateTime($str, new DateTimeZone($timezone));
    return $datetime->format($to_timestamp ? 'U' : 'Y-m-d H:i:s');
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}

function get_request_parameter($key, $default = '')
{
    if (!isset($_REQUEST[$key]) || empty($_REQUEST[$key])) {
        return $default;
    }
    if (is_array($_REQUEST[$key])) {
        return array_map(function ($v) {
            return strip_tags((string) wp_unslash($v));
        }, $_REQUEST[$key]);
    }
    return strip_tags((string) wp_unslash($_REQUEST[$key]));
}
