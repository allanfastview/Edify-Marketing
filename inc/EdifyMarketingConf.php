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
					
					$params = $request->get_params();
                    			
					//verifica se o negócio atualizado encontra-se em um funil rastreado
					if( $params['current']['pipeline_id'] == 22 ){ //TODO: As pipelines rastreadas serão definidas dinamicamente através do painel administrativo do plugin
						
                        //TODO: Esta relação de campos será definida de forma dinâmica através do painel administrativo do plugin através de uma comunicação com a API do RD MARKETING e do Pipedrive
						$campos_rastreados = [
							'a328990fd05072244a9c2eb74ce4a7509dc5f61e' => "cf_pipedrive_classificacao_do_agendamento_inbound", //Classificação do agendamento INBOUND
							'bbbcde8b312afdf4842fdc9c983fa6b9d82cbda8' => "cf_pipedrive_classificacao_do_lead_inbound", //Classificação do Lead INBOUND
							'bde6b6eab60d74426fe90f01b784af88aa12be9c' => "cf_pipedrive_qualificacao_do_lead_inbound", //Qualificação do Lead INBOUND
							'undone_activities_count' => "cf_pipedrive_atividades_para_fazer" //Atividades para fazer
						];
						$campos_a_serem_enviados = [];

						foreach( $campos_rastreados as $campo_pipedrive => $campo_rdstation ){

							//verifica se houve atualização em algum dos campos rastreados
							if( $params['current'][$campo_pipedrive] != $params['previous'][$campo_pipedrive] )
							    $campos_a_serem_enviados[ $campos_rastreados[$campo_pipedrive] ]= $params['current'][$campo_pipedrive];
							
						}

                        $campos_a_serem_enviados = array_merge( $campos_a_serem_enviados, 
                            [
                                "email" => "dantas.alves.allan@gmail.com",
                                "identificador" => "pipedrive-deal-update",
                            ] 
                        );

                        $data = [
                            "event_type" => "CONVERSION",
                            "event_family" => "CDP",
                            "payload" => $campos_a_serem_enviados
                        ];
                    	
						return new \WP_REST_response( $data );

					}
                },
                "permission_callback" => '__return_true',
            ));
			
        });

    }
    
}