<?php

namespace EdifyMarketing;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use EdifyMarketing\RdMarketingService;
use EdifyMarketing\PipedriveService;

class EdifyMarketingConf{

    public const RD_API_DOMAIN = "https://api.rd.services/";
    public const PIPEDRIVE_API_DOMAIN = "https://api.pipedrive.com/";

    public static function start(){

        self::register_endpoints();

        add_action('carbon_fields_fields_registered', function(){
            Container::make( 'theme_options', 'Edify Marketing' )
                ->add_fields( array(
                    Field::make('separator', 'rdmarketing_separator', 'RD Marketing'),
                    Field::make( 'text', 'edify_marketing_client_id', 'Client ID' ),
                    Field::make( 'text', 'edify_marketing_client_secret', 'Client Secret' ),
                    Field::make( 'text', 'edify_marketing_refresh_token', 'Refresh Token' ),
                    Field::make('separator', 'pipedrive_separator', 'Pipedrive'),
                    Field::make('text', 'pipedrive_token', 'Token API'),
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
					if( $params['data']['pipeline_id'] == 22 ){ //TODO: As pipelines rastreadas serão definidas dinamicamente através do painel administrativo do plugin
						
                        //TODO: Esta relação de campos será definida de forma dinâmica através do painel administrativo do plugin, através de uma comunicação com a API do RD MARKETING e do Pipedrive
						$campos_rastreados = [
							'a328990fd05072244a9c2eb74ce4a7509dc5f61e' => "cf_pipedrive_classificacao_do_agendamento_inbound", //Classificação do agendamento INBOUND
							'bbbcde8b312afdf4842fdc9c983fa6b9d82cbda8' => "cf_pipedrive_classificacao_do_lead_inbound", //Classificação do Lead INBOUND
							'bde6b6eab60d74426fe90f01b784af88aa12be9c' => "cf_pipedrive_qualificacao_do_lead_inbound", //Qualificação do Lead INBOUND
							'undone_activities_count' => "cf_pipedrive_atividades_para_fazer" //Atividades para fazer
						];

						$campos_a_serem_enviados_rd_marketing = [];
                        $dealFields = [];

                        //itera por todos os custom fields alterados
                        foreach( $params['previous']['custom_fields'] as $key => $fieldValue ){

                            //Verifica se o custom field alterado é um campo rastreado
                            if( isset( $campos_rastreados[ $key ] ) ){

                                //** caso campo seja do tipo enum busca valor textual do mesmo, caso contrário utiliza o respectivo valor */
                                    if( $params['data']['custom_fields'][ $key ]['type'] == 'enum' ){

                                        if( empty( $dealFields ) )
                                            $dealFields = PipedriveService::get_dealFields( ['key', 'id'] );

                                        foreach($dealFields->data as $dealField){
                                            
                                            if( $dealField->key == $key){
                                               
                                                $dealField = PipedriveService::get_dealField( $dealField->id );
                                                
                                                foreach( $dealField->data->options as $option ){
                                                    if( $option->id == $params['data']['custom_fields'][ $key ]['id'] ){
                                                        $campos_a_serem_enviados_rd_marketing[ $campos_rastreados[ $key ] ] = $option->label;
                                                        break;
                                                    }
                                                }

                                                break;
                                            }
                                        }
                                    }else{
                                        $campos_a_serem_enviados_rd_marketing[ $campos_rastreados[ $key ] ] = $params['data']['custom_fields'][ $key ][ 'id' ];
                                    }
                                //** end */
                            }
                        }

                        //Obtem email do lead vinculado ao negócio alterado
                        $person = PipedriveService::get_person( $params['data']['person_id'] );
                        $person_email = $person->data->primary_email;
                        $person_name = $person->data->name;

                        $campos_a_serem_enviados_rd_marketing = array_merge( $campos_a_serem_enviados_rd_marketing, 
                            [
                                "email" => $person_email,
                                "conversion_identifier" => "pipedrive-deal-update",
                                "name" => $person_name,
                            ] 
                        );

                        $data = [
                            "event_type" => "CONVERSION",
                            "event_family" => "CDP",
                            "payload" => $campos_a_serem_enviados_rd_marketing
                        ];

                        //Atualiza RD Marketing
                        ( new RdMarketingService() )->new_event( $data );
                    	
						return new \WP_REST_response( $data );

					}
                },
                "permission_callback" => '__return_true',
            ));
			
        });

    }
    
}