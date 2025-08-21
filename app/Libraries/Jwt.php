<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private $secret;
    private $expire;

    public function __construct()
    {
        $this->secret = getenv('JWT_SECRET');
        $this->expire = getenv('JWT_EXPIRE_TIME');
    }

    public function gerarToken($usuario)
    {
        $agora = time();
        $payload = [
            'iss' => 'vidaplus',   // emissor
            'aud' => 'usuarios',  // público
            'iat' => $agora,      // emitido em
            'exp' => $agora + $this->expire, // expiração
            'data' => [
                'id' => $usuario->id,
                'email' => $usuario->email
            ]
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validarToken($token)
    {
        try {
            return JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}