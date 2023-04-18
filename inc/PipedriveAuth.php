<?php

namespace EdifyMarketing;

use Curl\Curl;

class PipedriveAuth{

    private const CONTENT_TYPE = "application/json";

    private static function get_api_token(){
        return carbon_get_theme_option( 'pipedrive_token' );
    }

    public static function get_curl(){

        $curl = new Curl();
        $curl->setHeader('Content-Type', self::CONTENT_TYPE);
        $curl->setOpt('CURLOPT_URLFUNCTION', function( $url ){
            return $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'api_token=' . urlencode( self::get_api_token() );
        });

        return $curl;
    }

}