<?php

namespace Sulu\CamoMediaProxy;

class Formatter extends XFCP_Formatter
{
    public function getProxiedUrlIfActiveExtended($type, $url, array $options = [])
    {
        $proxyUrl = parent::getProxiedUrlIfActiveExtended($type, $url, $options);
        if ($proxyUrl == null) {
            return null;
        }
        
        $linkClassTarget = $this->getLinkClassTarget($url);
        if ($type !== 'image' || $linkClassTarget['type'] !== 'external') {
            return $proxyUrl;
        }

        $options = \XF::app()->options();
        $proxyUrl = $options->cmpProxUrl;
        $key = $options->cmpKey;
        $algorithm = $options->cmpAlgo;

        $digest = hash_hmac($algorithm, $url, $key, true);
        
        if ($options->cmpEncoding === 'base64') {
            return strtr($proxyUrl, [
                '{{url}}' => self::urlSafeBase64($url),
                '{{hmac}}' => self::urlSafeBase64($digest),
                '{{plainUrl}}' => $url,
                '{{algorithm}}' => $algorithm
            ]);
        }

        return strtr($proxyUrl, [
            '{{url}}' => bin2hex($url),
            '{{hmac}}' => bin2hex($digest),
            '{{plainUrl}}' => $url,
            '{{algorithm}}' => $algorithm
        ]);
    }

    public static function urlSafeBase64($string)
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}
