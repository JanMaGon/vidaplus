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

	public function pegaUsuarioLogado(int $usuario_id) 
	{

		if ($this->usuario == null) {

			$this->usuario = $this->pagaUsuario($usuario_id);

		}

		return $this->usuario;

	}

	/**
	 * Método que verifica se o usuário está logado
	 * 
	 * @param int $usuario_id
	 * @return bool
	 */
	public function estaLogado(int $usuario_id) : bool 
	{
		return $this->pegaUsuarioLogado($usuario_id) !== null;
	}

	/**
	 * Método que pega os dados do usuário logado
	 * 
	 * @param int $usuario_id
	 * @return object|null
	 */
	private function pagaUsuario($usuario_id)
	{

		$usuario = $this->usuarioModel->find($usuario_id);

		if ($usuario == null || $usuario->ativo == false) {

			return null;

		}

		// Define as permissões do usuário logado
		$usuario = $this->definePermissoesDoUsuarioLogado($usuario);

		return $usuario;


	}

	/**
	 * Método que define as permissões que o usuário logado possui
	 * 
	 * @param object $usuario
	 * @return object
	 */
	private function definePermissoesDoUsuarioLogado(object $usuario) : object
	{

		$usuario->is_admin   = $this->isAdmin($usuario->id);

		if ($usuario->is_admin == true) {

			$usuario->is_paciente = false;

		} else {

			$usuario->is_paciente = $this->isPaciente($usuario->id);

		}

		if ($usuario->is_admin == false && $usuario->is_paciente == false) {

			$usuario->permissoes = $this->recuperaPermissoesDoUsuarioLogado($usuario->id);

		}

		return $usuario;

	}

	/**
	 * Método que recupera as permissões do usuário logado
	 * 
	 * @param int $usuario_id
	 * @return array
	 */
	private function recuperaPermissoesDoUsuarioLogado(int $usuario_id) : array 
	{
		$permissoes = $this->usuarioModel->recuperaPermissoesDoUsuarioLogado($usuario_id);

		return array_column($permissoes, 'permissao');

	}

	private function isAdmin($usuario_id = null) : bool 
	{
	
		$grupoAdmin = 1; // ID do grupo Admin

		$administrador = $this->grupoUsuarioModel->buscaGruposDoUsuario($grupoAdmin, $usuario_id);

		if ($administrador == null) {
			return false;
		}

		return true;

	}

	private function isPaciente($usuario_id = null) : bool 
	{
	
		$grupoPaciente = 2; // ID do grupo Paciente

		$paciente = $this->grupoUsuarioModel->buscaGruposDoUsuario($grupoPaciente, $usuario_id);

		if ($paciente == null) {
			return false;
		}

		return true;

	}
	
}