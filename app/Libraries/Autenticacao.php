<?php

namespace App\Libraries;

class Autenticacao
{

	private $usuario;
	private $usuarioModel;

	public function __construct()
	{
		
		$this->usuarioModel = new \App\Models\UsuarioModel();

	}

	public function login(string $email, string $password) 
	{
		
		$usuario = $this->usuarioModel->buscaUsuarioPorEmail($email);
		
		// Usuario não encontrado
		if ($usuario === null) {
			return false;
		}

		// Verifica se a senha está correta
		if ($usuario->verificaPassword($password) == false) {
			return false;
		}

		// Verifica se o usuário está ativo
		if ($usuario->ativo == false) {
			return false;
		}


		// Tudo ok, usuário autenticado
		return $usuario;
	}
	
}