<?php

namespace Cabbie;

use Cabbie\Http;

class Uber
{

    const SANDBOX_ROOT = 'https://sandbox-api.uber.com';
    const ROOT = 'https://api.uber.com';

    const PRICE_ENDPOINT = '/v1/estimates/price';
    const TIME_ENDPOINT = '/v1/estimates/time';

    public $lastError;

    public function __construct() {
    }

    private function auth() {
        return array(
                    'Authorization: Token '.UBER_SERVER_TOKEN
                );
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function getPriceDetails($origin, $dest) {
        $originGeo = explode(',', $origin);
        $destGeo = explode(',', $dest);
        $query = array(
                        'start_latitude' => $originGeo[0],
                        'start_longitude' => $originGeo[1],
                        'end_latitude' => $destGeo[0],
                        'end_longitude' => $destGeo[1]
                    );

        $json = Http::curl(self::ROOT.self::PRICE_ENDPOINT.'?'.
            http_build_query($query), $this->auth());
        $result = json_decode($json[1], true);
        if ($json[0] != 200) {
            $this->lastError = $result['message'];
            return false;
        }


        return isset($result['prices']) ? $result['prices'] : false;
    }

    public function getETA($origin) {
        $originGeo = explode(',', $origin);
        $query = array(
                    'start_latitude' => $originGeo[0],
                    'start_longitude' => $originGeo[1]
                    );

        $json = Http::curl(self::ROOT.self::TIME_ENDPOINT.'?'.
            http_build_query($query), $this->auth());
        if ($json[0] != 200) {
            return false;
        }

        $result = json_decode($json[1], true);

        return isset($result['times']) ? $result['times'] : false;
    }
}
