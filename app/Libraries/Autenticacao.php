<?php

namespace App\Libraries;

class Autenticacao
{

	private $usuario;
	private $usuarioModel;
	private $grupoUsuarioModel;

	public function __construct()
	{
		
		$this->usuarioModel      = new \App\Models\UsuarioModel();
		$this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();

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

	public function isAdmin($usuario_id = null) : bool 
	{
	
		$grupoAdmin = 1; // ID do grupo Admin

		$administrador = $this->grupoUsuarioModel->buscaGruposDoUsuario($grupoAdmin, $usuario_id);

		if ($administrador == null) {
			return false;
		}

		return true;

	}
	
}