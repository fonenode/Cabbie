<?php

// Get details
// Process sms
// Number?
//  See if existing request and booking info
//    (No open requests. Text your loccation and destination to check availabilty)
//    If number authenticated
//      Make request
//    Send authentication link
// Not number
//  Get geocoding details
//  Get price request
//  Send sms

namespace Cabbie;

//use Cabbie\Utils;
use Cabbie\Google;
use Cabbie\Uber;

class Sms
{

    const BLANK_ERROR_TEXT = 'You sent a blank text. Send me ur location & destination
 in the form "location to destination". e.g: CChub, Yaba Lagos to Marina Lagos';
    const MATCH_ERROR_TEXT = 'I couldn\'t match the location & destination in ur text.
 Send in the form "location to destination". e.g: CChub, Yaba Lagos to Marina Lagos';
    const OCOORDS_ERROR_TEXT = 'Coordinates for the origin address was not found.
 Try using a more detailed address or address of closest point of interest';
    const DCOORDS_ERROR_TEXT = 'Coordinates for the destination address was not found.
 Try using a more detailed address or address of closest point of interest';
    const NO_CAB_TEXT = 'Ooops. No cab found for the specified route at this moment :/';

    private $uber;


    public function __construct()
    {
        $this->uber = new Uber;
        $this->google = new Google;
    }

    // Process incoming SMS
    public function processIncoming($data)
    {
        // Validate

        if (!isset($data['from'])) {
            // Throw an exception? But to?
            return;
        }

        // Remove all non-digits
        $sender = preg_replace('|[^0-9]|', '', $data['from']);

        // Mobile numbers alone for now (response cost :D)
        if (strlen($sender) < 8) {
            // Only mobile numbers supported for now
            return;
        }
        // Validate blank text
        if (empty($data['text'])) {
            $this->text($sender, self::BLANK_ERROR_TEXT);
            return;
        }

        $text = $data['text'];

        // todo: To or Response?

        // Get text parts (location and destination)
        // Should be as simple as splitting by " to "
        // Probably kill other location words like 'from' later
        $parts = preg_split('|\s+to\s+|i', $text, 2, PREG_SPLIT_NO_EMPTY);

        // Convert locations to coords with Google API
        if (count($parts) != 2) {
            $this->text($sender, self::MATCH_ERROR_TEXT);
            return;
        }

        $originGeo = $this->google->getCoords($parts[0]);
        if (!$originGeo) {
            $this->text($sender, self::OCOORDS_ERROR_TEXT);
            return;
        }
        // Multiple address matched for origin geo
        if (count($originGeo) > 1) {
            $text = "Ur location matched multiple addresses. ";
            $text .= "Kindly resend request with preferred location.\n";
            foreach ($originGeo as $v) {
                $text .= $v['addy']."\n";
            }
            $this->text($sender, $text);
            return;
        }
        $destGeo = $this->google->getCoords($parts[1]);
        if (!$destGeo) {
            $this->text($sender, self::DCOORDS_ERROR_TEXT);
            return;
        }
        // Multiple address matched for destination geo
        if (count($destGeo) > 1) {
            $text = "Ur destination matched multiple addresses. ";
            $text .= "Kindly resend request with preferred destination.\n";
            foreach ($destGeo as $v) {
                $text .= $v['addy']."\n";
            }
            $this->text($sender, $text);
            return;
        }

        $originCoords = $originGeo[0]['geo'];
        $destCoords = $destGeo[0]['geo'];

        // Get Price estimates from uber
        $prices = $this->uber->getPriceDetails($originCoords, $destCoords);
        if (!$prices) {
            $this->text($sender, $this->uber->getLastError());
            return;
        }
        // Get time estimates too
        $etas = $this->uber->getETA($originCoords);
        // Are there prices?
        if (count($prices) < 1) {
            $this->text($sender, self::NO_CAB_TEXT);
            return;
        }
        // todo. Save product id here
        $message = '';
        foreach ($prices as $price) {
            $message .= $price['display_name'].'. '.$price['estimate'];
            // Get eta
            foreach ($etas as $eta) {
                // todo: confirm same product id really
                if ($eta['product_id'] == $price['product_id']) {
                    $message .= '. ETA: '.gmdate("H:i:s", $eta['estimate']);
                    break;
                }
            }
            $message .= "\n";
        }
        $this->text($sender, $message);
    }

    public static function text($receiver, $text)
    {
        // Send sms

        $receiver = strpos($receiver, '0') !== 0 ? $receiver :
                        '234'.substr($receiver, 1);

        $data = array(
                'text' => urlencode($text),
                'to' => $receiver,
                'from' => SENDER
            );
        Http::curl(SMS_URL, array('CURLOPT_USERPWD' => FONENODE_USERNAME.':'.FONENODE_PASSWORD), $data);

        return;
    }
}
