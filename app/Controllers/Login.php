<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\Autenticacao;

use Firebase\JWT\JWT;

class Login extends BaseController
{
    public function index()
    {
        
		$autenticacao = new Autenticacao();

		$usuario = $autenticacao->login('maria.c@gmail.com', '123456');

		if (!$usuario) {            
			return $this->response
				->setStatusCode(401) // Unauthorized
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Credenciais inválidas.'
				]);
        }

		// Pegamos a chave secreta do JWT
        $key = getenv('JWT_SECRET');

        // Definimos o payload do token
        $payload = [
            'iss' => base_url(),      // emissor do token (seu domínio)
            'aud' => base_url(),      // audiência
            'iat' => time(),          // emitido em
            'exp' => time() + 3600,   // expiração (1h)
            'data' => [               // dados do usuário
                'id' => $usuario->id,
                'email' => $usuario->email,
            ]
        ];

		// Geramos o token
        $token = JWT::encode($payload, $key, 'HS256');

        // Retornamos o token
        return $this->response
			->setStatusCode(200)
			->setJSON([
				'status' => 'OK',
				'token' => $token,
				'usuario' => [
					'id' => $usuario->id,
					'email' => $usuario->email,
				]
			]);

    }
}
