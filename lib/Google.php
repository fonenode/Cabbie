<?php

namespace Cabbie;

use Cabbie\Http;

class Google
{

    // Constrained to Nigerian addresses
    const GEOCODING_URI = 'https://maps.googleapis.com/maps/api/geocode/json?components=country:Nigeria&address=';

    public function getCoords($addy) {
        $json = Http::curl(self::GEOCODING_URI.urlencode($addy));
        if ($json[0] != 200) {
            return false;
        }

        $result = json_decode($json[1], true);

        $coords = array();
        foreach ($result['results'] as $k => $v) {
            $coords[$k]['addy'] = $v['formatted_address'];
            $coords[$k]['geo'] = $v['geometry']['location']['lat'].','
                                    .$v['geometry']['location']['lng'];
        }

        return $coords;
    }
}
