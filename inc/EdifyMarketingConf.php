<?php

namespace EdifyMarketing;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use EdifyMarketing\RdMarketingService;
use EdifyMarketing\PipedriveService;

class EdifyMarketingConf{

    public const RD_API_DOMAIN = "https://api.rd.services/";
    public const PIPEDRIVE_API_DOMAIN = "https://api.pipedrive.com/";

    //TODO: Mover para PipedriveService
    private static function get_person_fields(){

        $person_fields = PipedriveService::get_person_fields();
        $fields = array();
        foreach( $person_fields->data as $person_field ){
            $fields[ $person_field->key ] = $person_field->name;
        }
        return $fields;
    }

    //TODO: Mover para PipedriveService
    private static function get_orgaization_fields(){

        $entity_fields = PipedriveService::get_organization_fields();
        $fields = array();
        foreach( $entity_fields->data as $entity_field ){
            $fields[ $entity_field->key ] = $entity_field->name;
        }
        return $fields; 

    }

    //TODO: Mover para PipedriveService
    private static function get_deal_fields(){

        $entity_fields = PipedriveService::get_dealFields();
        $fields = array();
        foreach( $entity_fields->data as $entity_field ){
            $fields[ $entity_field->key ] = $entity_field->name;
        }
        return $fields;

    }

  
    public static function start(){

        self::register_endpoints();

        add_action('carbon_fields_fields_registered', function(){
            //TODO: Criar função separada para tratar criação dos fileds e passar como callback
            Container::make( 'theme_options', 'Edify Marketing' )
                ->add_tab('RD Marketing', array(

                    Field::make('separator', 'rdmarketing_api', 'Configuração API'),

                    Field::make( 'text', 'edify_marketing_client_id', 'Client ID' ),

                    Field::make( 'text', 'edify_marketing_client_secret', 'Client Secret' ),

                    Field::make( 'text', 'edify_marketing_refresh_token', 'Refresh Token' ),

                ))
                ->add_tab('Pipedrive', array(
                    
                    Field::make('separator', 'pipedrive_api', 'Configuração API'),

                    Field::make('text', 'pipedrive_token', 'Token API'),

                    /** Email para vínculo entre Pipedrive e RD Marketing */
                        Field::make('separator', 'pipe_rd_link', 'Editar vínculo entre Pipedrive e RD Marketing'),
                        Field::make('select', 'entity_pipedrive_rd', "Entidade Email")
                            ->add_options( array(
                                "person" => "Pessoa",
                                "deal" => "Negócio",
                                "organization" => "Organização"
                            ))
                            ->set_width(50),
                            //Custom Fields for person
                            Field::make('select', 'custom_fields_person', "Campo Email")
                            ->add_options( $person_fields = self::get_person_fields() )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd',
                                    'value'     => "person",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                            //Custom Fields for Organization
                            Field::make('select', 'custom_fields_organization', "Campo Email")
                            ->add_options( $organization_fields = self::get_orgaization_fields() )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd',
                                    'value'     => "organization",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                            //Custom Fields for Deal
                            Field::make('select', 'custom_fields_deal', "Campo Email")
                            ->add_options( $deal_fields = self::get_deal_fields() )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd',
                                    'value'     => "deal",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                    /** end  */

                    /** Nome para vínculo entre Pipedrive e RD Marketing */
                        Field::make('select', 'entity_pipedrive_rd_name', "Entidade Nome")
                            ->add_options( array(
                                "person" => "Pessoa",
                                "deal" => "Negócio",
                                "organization" => "Organização"
                            ))
                            ->set_width(50),
                            //Custom Fields for person
                            Field::make('select', 'custom_fields_person_name', "Campo Nome")
                            ->add_options( $person_fields )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd_name',
                                    'value'     => "person",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                            //Custom Fields for Organization
                            Field::make('select', 'custom_fields_organization_name', "Campo Nome")
                            ->add_options( $organization_fields )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd_name',
                                    'value'     => "organization",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                            //Custom Fields for Deal
                            Field::make('select', 'custom_fields_deal_name', "Campo Nome")
                            ->add_options( $deal_fields )
                            ->set_conditional_logic( [
                                [
                                    'field'     => 'entity_pipedrive_rd_name',
                                    'value'     => "deal",
                                    'compare'   => "="
                                ]
                            ] )
                            ->set_width(50),
                    /** end */
                ));
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


                        /** Resgata email em comúm entre Pipedrive e RD Marketing */
                
                            switch( carbon_get_theme_option('entity_pipedrive_rd') ){
                                case 'person':
                                    $field = carbon_get_theme_option( 'custom_fields_person' );
                                    $person = PipedriveService::get_person( $params['data']['person_id'] );
                                    $person_email = ( is_array( $person->data->$field ) )?  $person->data->$field[0]->value :  $person->data->$field;
                                    break;
                                case 'deal':
                                    $field = carbon_get_theme_option( 'custom_fields_deal' );
                                    $deal = PipedriveService::get_deal( $params['data']['id'] );
                                    $person_email = ( is_array( $deal->data->$field ) )?  $deal->data->$field[0]->value :  $deal->data->$field;
                                    break;
                                case 'organization':
                                    //TODO: Implementar código para quando email prover da entidade organization
                                    break;
                                default:
                            }

                        /** end  */

                       

                        /** Resgata nome em comúm entre Pipedrive e RD Marketing */

                            switch( carbon_get_theme_option('entity_pipedrive_rd_name') ){
                                case 'person':
                                    $field = carbon_get_theme_option( 'custom_fields_person_name' );
                                    $person = PipedriveService::get_person( $params['data']['person_id'] );
                                    $person_name = ( is_array( $person->data->$field ) )?  $person->data->$field[0]->value :  $person->data->$field;
                                    break;
                                case 'deal':
                                    $field = carbon_get_theme_option( 'custom_fields_deal_name' );
                                    $deal = PipedriveService::get_deal( $params['data']['id'] );
                                    $person_email = ( is_array( $deal->data->$field ) )?  $deal->data->$field[0]->value :  $deal->data->$field;
                                    break;
                                case 'organization':
                                    //TODO: Implementar código para quando email prover da entidade organization
                                    break;
                                default:
                            }

                        /** end  */

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