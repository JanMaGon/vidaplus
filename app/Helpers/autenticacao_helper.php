<?php

if (!function_exists('usuario_logado')) {

    function usuario_logado($usuario_id = null)
    {
        return service('autenticacao')->pegaUsuarioLogado($usuario_id);
    }
    
}