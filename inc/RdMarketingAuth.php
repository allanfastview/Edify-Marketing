<?php

namespace EdifyMarketing;

use Curl\Curl;

class RdMarketingAuth{

    private const PATH = "auth/token";
    private const CONTENT_TYPE = "application/json";

    private static function get_client_id(){
        return carbon_get_theme_option( 'edify_marketing_client_id' );
    }

    private static function get_client_secret(){
        return carbon_get_theme_option( 'edify_marketing_client_secret' );
    }

    private static function get_refresh_token(){
        return carbon_get_theme_option( 'edify_marketing_refresh_token' );
    }

    private static function refresh_token(){

        $curl = new Curl();
        $curl->setHeader('Content-Type', self::CONTENT_TYPE);

        $data = [
            "client_id" => self::get_client_id(),
            "client_secret" => self::get_client_secret(),
            "refresh_token" => self::get_refresh_token()
        ];

        $curl->post( EdifyMarketingConf::RD_API_DOMAIN . self::PATH, json_encode( $data ) );
        return json_decode($curl->response)->access_token;
    }

    private static function get_token(){
      return self::refresh_token();
    }

    public static function get_curl(){

        $curl = new Curl();
        $curl->setHeader('Content-Type', self::CONTENT_TYPE);
        $curl->setHeader('Authorization', 'Bearer '. self::get_token());

        return $curl;
    }

}