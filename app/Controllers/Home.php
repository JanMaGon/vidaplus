<?php

namespace App\Controllers;

use App\Traits\ValidacoesTrait;
class Home extends BaseController
{
	use ValidacoesTrait;

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

	public function rotaProtegida()
	{
		$request = service('request'); 	// $request->user->id vem do filtro JWTFilter. Só é possivel acessar esse serviço de um controller

		//$autenticacao = new Autenticacao(); // 1ª forma de usar o serviço
		//$autenticacao = service('autenticacao'); // 2ª forma de usar o serviço
		//$usuario = $autenticacao->pegaUsuarioLogado($request->user->id); // Pega o usuário logado

		 // Se o ID do usuário não estiver no token
		if (empty($request->user->id)) {
			return $this->response->setStatusCode(400)->setJSON([
				'status' => 'ERRO',
				'mensagem' => 'ID do usuário não encontrado no token.'
			]);
		}

		$dados = [
			'status' => 'OK',
			//'temPermissao' => $usuario->temPermissaoPara('listar-usuarios'), // Exemplo de verificação de permissao
			'usuario' => usuario_logado($request->user->id), // usuario_logado() é um função do autenticacao_helper instanciado no basecontroller para ser usado em toda a aplicação
			//'usuario' => $usuario // Outra forma de retornar o usuário
		];
		
		return $this->response->setStatusCode(200)->setJSON($dados);
		
	}

	public function cep()
	{

		$cep = "96412-600";

		return $this->response->setStatusCode(200)->setJSON([
			'cep' => $cep,
			'valido' => $this->consultaViaCep($cep)
		]);

	}
}
