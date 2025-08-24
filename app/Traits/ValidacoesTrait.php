<?php

namespace App\Traits;

trait ValidacoesTrait
{

    public function consultaViaCep(string $cep) 
    {

        $cep = str_replace('-', '', $cep);

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        // Abri a conexão cURL
        $ch = curl_init();
        // Definir a URL 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Executa a consulta
        $response = curl_exec($ch);
        // Captura erros
        $erro = curl_error($ch);

        $retorno = [];

        if ($erro) {

            $retorno['erro'] = $erro;
            return $retorno;

        }
        
        $consulta = json_decode($response);

       
        if (empty($consulta)) {

            session()->set('blockCep', true);

            $retorno['erro'] = 'CEP não encontrado.';

            return $retorno;
        }

        session()->set('blockCep', false);

        $retorno = [
            'endereco' => esc($consulta->logradouro),
            'bairro'   => esc($consulta->bairro),
            'cidade'   => esc($consulta->localidade),
            'estado'   => esc($consulta->uf),
        ];

        return $retorno;
        

    }

}