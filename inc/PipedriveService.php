<?php

namespace EdifyMarketing;

use EdifyMarketing\PipedriveAuth;

class PipedriveService {

    private const PERSON_SLUG= "v1/persons";
    private const DEAL_FIELD_SLUG = "v1/dealFields";

    /**
     * Description: Retorna uma entidade pessoa do Pipedrive
     * @param Int $id Identificador único da entidade Pessoa
     * @return Oject
     */
    public static function get_person( $id ){

        $curl = PipedriveAuth::get_curl();
        //TODO: O token deve ser inserido automaticamente ao final de todo URL, sendo portanto desnecessário faze-lo manualmente como esta sendo realizado aqui
        $curl->get( EdifyMarketingConf::PIPEDRIVE_API_DOMAIN . self::PERSON_SLUG . '/' . $id . '?api_token=' . get_option('_pipedrive_token', true) );
        return json_decode($curl->response);
    }

    /**
     * Description: Retorna coleção de fields vinculada aos negócios
     * @param Array $fields: Fields da coleção que devem ser trazidos no retorno
     * @return Object
     */
    public static function get_dealFields( $fields ){

        $curl = PipedriveAuth::get_curl();
        //TODO: O token deve ser inserido automaticamente ao final de todo URL, sendo portanto desnecessário faze-lo manualmente como esta sendo realizado aqui
        $curl->get( EdifyMarketingConf::PIPEDRIVE_API_DOMAIN . self::DEAL_FIELD_SLUG . ':(' .  implode(',', $fields) .')' . '?api_token=' . get_option('_pipedrive_token', true) );
        return json_decode( $curl->response );

    }

    /**
     * Description: Retorna um field específico
     * @param Int @id: ID do field
     * @return Object
     */
    public static function get_dealField( $id ){
        $curl = PipedriveAuth::get_curl();
        //TODO: O token deve ser inserido automaticamente ao final de todo URL, sendo portanto desnecessário faze-lo manualmente como esta sendo realizado aqui
        $curl->get( EdifyMarketingConf::PIPEDRIVE_API_DOMAIN . self::DEAL_FIELD_SLUG . '/' . $id . '?api_token=' . get_option('_pipedrive_token', true) );
        return json_decode( $curl->response );
    }
}
