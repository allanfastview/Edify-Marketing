<?php

namespace EdifyMarketing;

use EdifyMarketing\RdMarketingAuth;

class RdMarketingService {

    private const EVENT_SLUG= "platform/events";

    /**
     * Description: Cria novo evento no RD Marketing
     * @param Array $data Informações sobre o evento a ser criado
     * @return String Resposta da API
     */
    public function new_event( $data ){

        $curl = RdMarketingAuth::get_curl();
        $curl->post( EdifyMarketingConf::RD_API_DOMAIN . self::EVENT_SLUG, json_encode( $data ) );
        return $curl->response;
    
    }
}
