<?php

namespace App\Controllers;

use App\Libraries\Autenticacao;

class Home extends BaseController
{
	public function index(): string
	{
		return view('welcome_message');
	}

	public function generate_jwt_key(): string
	{
		// Método para gerar uma chave JWT		
		$chave = bin2hex(random_bytes(32));
		return $chave;
	}

	public function teste()
	{
		$request = service('request');

		$autenticacao = new Autenticacao();

		if ($autenticacao->isAdmin($request->user->id)) {
			$dados = [
				'status' => 'OK',
				'mensagem' => 'Usuário é administrador.',
				'usuario' => $request->user
			];
			return $this->response->setStatusCode(200)->setJSON($dados);
		}

		// Caso não seja admin, você pode retornar outra resposta
		return $this->response->setStatusCode(403)->setJSON([
			'status' => 'ERRO',
			'mensagem' => 'Usuário não é administrador.'
		]);
	}
}
