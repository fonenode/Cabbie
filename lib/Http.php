<?php

namespace Cabbie;

class Http
{

    static public function curl($url, array $headers = null, $data = null, array $opts = null) {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, TRUE);
        if ($data) {
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($handle, CURLOPT_CAINFO, dirname(__FILE__).'/cert_bundle.crt');
            // 0 to not verify ssl. Don't do this kids
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 1);
        }
        if ($headers) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        }
        if ($opts) {
            foreach ($opts as $k => $v) {
                curl_setopt($handle, $k, $v);
            }
        }
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($handle);
        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return array($statusCode, $result);
    }
}
