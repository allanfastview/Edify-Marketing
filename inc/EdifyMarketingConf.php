<?php

namespace EdifyMarketing;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use EdifyMarketing\RdMarketingService;

class EdifyMarketingConf{

    public const RD_API_DOMAIN = "https://api.rd.services/";

    public static function start(){

        self::register_endpoints();

        add_action('carbon_fields_fields_registered', function(){
            Container::make( 'theme_options', 'Edify Marketing' )
                ->add_fields( array(
                    Field::make( 'text', 'edify_marketing_client_id', 'Client ID' ),
                    Field::make( 'text', 'edify_marketing_client_secret', 'Client Secret' ),
                    Field::make( 'text', 'edify_marketing_refresh_token', 'Refresh Token' ),
                ) );
        });
    }

    private static function register_endpoints(){

        add_action('rest_api_init', function(){
    
            register_rest_route( 'edify-marketing', 'conversion', array(
                'methods' => 'POST',
                'callback' => function( \WP_REST_request $request ){
    
                    $data = [
                        "event_type" => "CONVERSION",
                        "event_family" => "CDP",
                        "payload" => $request->get_body_params()
                    ];
                    
                   return new \WP_REST_response( ( new RdMarketingService() )->new_event( $data ) );
    
                },
                'permission_callback' => '__return_true',
              ) );
        
        });

        add_action('rest_api_init', function(){
            register_rest_route('edify-marketing', 'pipehook', array(
                "methods" => 'POST',
                "callback" => function( \WP_REST_request $request ){
                    update_option('pipehook', 'webhook pipedrive run '. date("h:i:sa"));
                },
                "permission_callback" => '__return_true',
            ));
        });

    }
    
}