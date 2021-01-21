<?php

namespace Tradzero\DynamicUrl;

use Tradzero\DynamicUrl\Exceptions\NoAvailableEndpointException;
use Tradzero\DynamicUrl\Exceptions\ProviderNotSupportExcetion;
use Tradzero\DynamicUrl\Models\DynamicUrl as ModelsDynamicUrl;

class DynamicUrl
{
    public static function buildUrl($params)
    {
        $endpoint = self::getAvailableEndpoint();
        if (! $endpoint) {
            throw new NoAvailableEndpointException();
        }
        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $url = rtrim('/', $endpoint) . '/';
        return $url . $queryString;
    }

    public static function addUrl($url)
    {
        self::checkProvider();
        ModelsDynamicUrl::firstOrCreate([
            'enable' => true
        ], [
            'url' => $url
        ]);
        return true;
    }

    public static function removeUrl($url)
    {
        self::checkProvider();
        ModelsDynamicUrl::where(['url' => $url])->delete();
        return true;
    }

    public static function enableUrl($url)
    {
        self::checkProvider();
        ModelsDynamicUrl::where(['url' => $url])->update([
            'enable' => true
        ]);
        return true;
    }

    public static function disableUrl($url)
    {
        self::checkProvider();
        ModelsDynamicUrl::where(['url' => $url])->update([
            'enable' => false
        ]);
        return true;
    }

    protected static function checkProvider()
    {
        if (config('dynamic_url.provider') == 'env') {
            throw new ProviderNotSupportExcetion();
        }
    }

    public static function getAvailableEndpoint()
    {
        $endpoints = self::getAllEndpoints();
        $url = $endpoints->random();
        return $url;
    }

    public static function getAllEndpoints()
    {
        if (config('dynamic_url.provider' == 'env')) {
            $rawEndpoints = env('dynamic_url.endpoints');
            $endpoints = explode(',', $rawEndpoints);
            return collect($endpoints);
        }
        $model = new ModelsDynamicUrl();
        $endpoints = $model->getAvailableEndpoint();
        return $endpoints;
    }
}
