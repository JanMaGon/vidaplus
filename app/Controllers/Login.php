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

		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}
        
		$autenticacao = new Autenticacao();

		$usuario = $autenticacao->login($dados['email'], $dados['password']);

		if (!$usuario) {            
			return $this->response
				->setStatusCode(401) // Unauthorized
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Credenciais inválidas.'
				]);
        }

		// Registra ação do usuário no log de atividades
		$this->registraAcaoDoUsuario('Logou na aplicação', $usuario->id);

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

	/**
	 * Lê dados enviados via JSON.
	 *
	 * @return array|\CodeIgniter\HTTP\ResponseInterface
	 * Retorna um array associativo com os dados da requisição
	 * ou um objeto ResponseInterface (400) em caso de JSON inválido.
	 */
	private function getRequestData()
	{
		$dados = [];

		$contentType = $this->request->getHeaderLine('Content-Type');

		if (stripos($contentType, 'application/json') === false) {
			// Retorna erro se não for JSON
			return $this->response
				->setStatusCode(415) // 415 Unsupported Media Type
				->setJSON([
					'status' => 'erro',
					'mensagem' => 'Formato inválido. Envie os dados como JSON.'
				]);
		}

		try {
			$dados = $this->request->getJSON(true); // retorna array
		} catch (\Exception $e) {
			return $this->response
				->setStatusCode(400) // JSON inválido
				->setJSON([
					'status' => 'erro',
					'mensagem' => 'JSON inválido'
				]);
		}

		return $dados;
	}
}
