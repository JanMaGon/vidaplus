<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Entities\Usuario;

class Usuarios extends BaseController
{

	private $usuarioModel;
	private $grupoUsuarioModel;
	private $grupoModel;

	public function __construct()
	{
		$this->usuarioModel      = new \App\Models\UsuarioModel();
		$this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
		$this->grupoModel        = new \App\Models\GrupoModel();
	}

	public function index()
	{

		$atributos = [
			'id',
			'nome',
			'email',
			'ativo',
		];

		$usuarios = $this->usuarioModel->select($atributos)->findAll();

		$data = [];

		foreach ($usuarios as $usuario) {

			// Receberá o array de objetos de usuários
			$data[] = [
				'id' => (int) $usuario->id,
				'nome' => esc($usuario->nome),
				'email' => esc($usuario->email),
				'ativo' => (bool) $usuario->ativo,
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setStatusCode(200)->setJSON($retorno);
	}

	public function exibir($id = null)
	{

		$usuario = $this->buscaUsuarioOu404($id);

		// Verifica se $usuario é uma resposta HTTP (ResponseInterface).
		// Se for, encerra a execução.
		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		// Converte o objeto para array
		$dados = $usuario->toArray();

		// Remove campos sensíveis
		unset(
			$dados['password_hash'],
			$dados['reset_hash'],
			$dados['reset_expira_em']
		);

		return $this->response->setStatusCode(200)->setJSON($dados);
	}

	public function criar()
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		// Cria um novo objeto da Entidade Usuario
		$usuario = new Usuario($dados);

		if ($this->usuarioModel->protect(false)->save($usuario)) {

			// Retornamos junto com o status o último ID inserido
			// Ou seja, o ID do usuário recém-criado
			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Usuário criado com sucesso",
					'id' => $this->usuarioModel->getInsertID()
				]);
		}

		// Alguma validação falhou
		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao criar o usuário',
				'erros_model' => $this->usuarioModel->errors()
			]);
	}

	public function atualizar($id = null)
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		$usuario = $this->buscaUsuarioOu404($id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		// Se informou senha mas não confirmou, retorna erro
		if (!empty($dados['password']) && empty($dados['password_confirmation'])) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Por favor confirme a sua senha.'
				]);
		}

		// Senão for informado, não atualiza a senha
		// Se não fizer desta forma, o hashPassword do Model fará o hash de uma string vazia
		if (empty($dados['password'])) {

			unset($dados['password']);
			unset($dados['password_confirmation']);
		}

		// Garante que a ID usada na atualização é a da URL
		$dados['id'] = $id;

		// Preenche os atributos do usuário com os valores do POST
		$usuario->fill($dados);

		if ($usuario->hasChanged() === false) {
			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => 'Nenhum dado foi modificado'
				]);
		}

		if ($this->usuarioModel->protect(false)->save($usuario)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Usuário atualizado com sucesso"
				]);
		}

		// Alguma validação falhou
		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao atualizar o usuário',
				'erros_model' => $this->usuarioModel->errors()
			]);
	}

	public function remover($id = null)
	{

		$usuario = $this->buscaUsuarioOu404($id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		if ($usuario->deletado_em != null) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => "Esse usuário já encontra-se excluído"
				]);
		}

		if ($this->usuarioModel->delete($usuario->id)) {

			$usuario->ativo = false;
			$this->usuarioModel->protect(false)->save($usuario);

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Usuário excluído com sucesso"
				]);
		}

		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar excluir o usuário'
			]);
	}

	public function lixeira()
	{
		$atributos = [
			'id',
			'nome',
			'email',
			'ativo',
			'deletado_em', // Inclui o campo deletado para exibir quando foi excluído
		];

		// Busca apenas os registros que foram soft deleted
		$usuarios = $this->usuarioModel
			->onlyDeleted()
			->select($atributos)
			->findAll();

		$data = [];

		foreach ($usuarios as $usuario) {
			$data[] = [
				'id'    => (int) $usuario->id,
				'nome'  => esc($usuario->nome),
				'email' => esc($usuario->email),
				'ativo' => (bool) $usuario->ativo,
				'deletado_em' => $usuario->deletado_em,
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response
			->setStatusCode(200)
			->setJSON($retorno);
	}

	public function restaurar($id = null)
	{

		$usuario = $this->buscaUsuarioOu404($id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		if ($usuario->deletado_em == null) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => "Apenas usuários excluídos podem ser restaurados"
				]);
		}

		$usuario->deletado_em = null; // Limpa o campo de exclusão

		if ($this->usuarioModel->protect(false)->save($usuario)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Usuário restaurado com sucesso"
				]);
		}

		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar restaurar o usuário'
			]);
	}

	public function grupos($usuario_id = null)
	{

		$usuario = $this->buscaUsuarioOu404($usuario_id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		$usuario->grupos = $this->grupoUsuarioModel->recuperaGruposDoUsuario($usuario->id);

		// Quando o usuário é um paciente, não pode adicionar grupos
		if (in_array(2, array_column($usuario->grupos, 'grupo_id'))) {

			return $this->response
				->setStatusCode(403) // Forbidden
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Este usuário é um paciente. Não é permitido adicionar ou remover grupo.'
				]);
		}

		$pertence = [];
		$naoPertence = [];

		foreach ($usuario->grupos as $grupo) {
			$pertence[] = [
				'id' => (int) $grupo->id,
				'grupo_id'    => (int) $grupo->grupo_id,
				'nome'  => esc($grupo->nome),
				'descricao'  => esc($grupo->nome),
			];
		}

		if (!empty($usuario->grupos)) {

			// Recupera os grupos que o usuário não pertence
			$gruposExistentes = array_column($usuario->grupos, 'grupo_id');

			// Não recupera o grupo de ID 2 (Paciente)
			$naoPertence = $this->grupoModel->where('id !=', 2)->whereNotIn('id', $gruposExistentes)->findAll();
		} else {

			// Recupera todos os grupos, exceto o de ID 2 (Paciente)
			$naoPertence = $this->grupoModel->where('id !=', 2)->findAll();
		}

		$retorno = [
			'pertence' => $pertence,
			'nao_pertence' => $naoPertence,
		];

		return $this->response
			->setStatusCode(200)
			->setJSON($retorno);
	}

	public function salvarGrupos($usuario_id = null)
	{

		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		$usuario = $this->buscaUsuarioOu404($usuario_id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		// Grupos já atribuídos ao usuário. Retorna só os IDs já salvos
		$gruposExistentes = $this->grupoUsuarioModel->where('usuario_id', $usuario->id)->findColumn('grupo_id');

		// Validações extraídas
		$erro = $this->validarGrupos($gruposExistentes, $dados['grupo_id']);
		if ($erro) {
			return $this->response
				->setStatusCode($erro['status'])
				->setJSON($erro['mensagem']);
		}

		// Se o usuário for um administrador, atribui o grupo de Administradores
		// e remove os outros grupos, exceto o de Administradores
		if (in_array(1, $dados['grupo_id'])) {

			$grupoAdmin = [
				'usuario_id' => $usuario->id,
				'grupo_id' => 1 // ID do grupo de Administradores
			];

			$this->grupoUsuarioModel->insert($grupoAdmin);
			$this->grupoUsuarioModel->where('usuario_id', $usuario->id)
				->where('grupo_id !=', 1)
				->delete();

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo(s) salvo(s) com sucesso"
				]);
		}

		// Rceberá os grupos do POST
		$grupoPush = [];

		// Filtar os grupos que já existem
		// e só inserir os que não estão atribuídos ao usuário
		foreach ($dados['grupo_id'] as $grupo) {

			if (! in_array($grupo, $gruposExistentes)) {
				$grupoPush[] = [
					'usuario_id'     => $usuario->id,
					'grupo_id' => $grupo
				];
			}
		}

		if ($this->grupoUsuarioModel->insertBatch($grupoPush)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo(s) salvo(s) com sucesso"
				]);
		}


		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar salvar os grupos do usuário',
				'erros_model' => $this->grupoUsuarioModel->errors()
			]);
	}

	public function removerGrupos($usuario_id = null, $grupo_usuario_id = null)
	{

		$usuario = $this->buscaUsuarioOu404($usuario_id);

		if ($usuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $usuario; // Se já for a resposta 404, retorna direto
		}

		// Verifica se o grupo do usuário existe
		$grupoUsuario = $this->buscaGrupoUsuarioOu404($grupo_usuario_id);

		if ($grupoUsuario instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupoUsuario; // Se já for a resposta 404, retorna direto
		}

		if ($grupoUsuario->grupo_id == 2) {

			return $this->response
				->setStatusCode(403) // Forbidden
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Não é permitida a exclusão do usuário do grupo de Pacientes.'
				]);

		}

		if ($this->grupoUsuarioModel->delete($grupo_usuario_id)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo removido com sucesso"
				]);
		}

		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar excluir o grupo o usuário',
				'erros_model' => $this->grupoUsuarioModel->errors()
			]);

	}

	/**
	 * Recupera o usuário pelo ID ou retorna resposta 404.
	 *
	 * @param int|null $id
	 * @return object|\CodeIgniter\HTTP\ResponseInterface
	 */
	private function buscaUsuarioOu404($id = null)
	{

		if (!$id || !$usuario = $this->usuarioModel->withDeleted(true)->find($id)) {
			return $this->response
				->setStatusCode(404)
				->setJSON([
					'status'  => 'error',
					'message' => "Não encontramos o usuário"
				]);
		}

		return $usuario;
	}

	/**
	 * Recupera o grupo usuário pelo ID ou retorna resposta 404.
	 *
	 * @param int|null $id
	 * @return object|\CodeIgniter\HTTP\ResponseInterface
	 */
	private function buscaGrupoUsuarioOu404($id = null)
	{

		if (!$id || !$grupo_usuario = $this->grupoUsuarioModel->find($id)) {
			return $this->response
				->setStatusCode(404)
				->setJSON([
					'status'  => 'error',
					'message' => "Grupo do usuário não encontrado"
				]);
		}

		return $grupo_usuario;
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

	/**
	 * Verifica se os grupos são válidos de acordo com as regras de negócio.
	 * @param array $gruposExistentes
	 * @param array $gruposNovos
	 * @return array|null
	 * Retorna null se for válido, ou um array com 'status' e 'mensagem' se inválido.
	 */
	private function validarGrupos(array $gruposExistentes, array $gruposNovos)
	{

		// Verifica se o array de grupos novos está vazio
		if (empty($gruposNovos)) {
			return [
				'status'   => 400,
				'mensagem' => [
					'status' => 'error',
					'mensagem' => 'Nenhum grupo foi informado para atribuir ao usuário.'
				]
			];
		}

		// Paciente já existente
		// Quando o usuário é um paciente, não pode adicionar grupos
		if (in_array(2, $gruposExistentes)) {
			return [
				'status'   => 403,
				'mensagem' => [
					'status' => 'error',
					'mensagem' => 'Este usuário é um paciente. Não é permitido adicionar ou remover grupo.'
				]
			];
		}

		// Administrador já existente
		// Quando o usuário é um administrador, não adiciona outros grupos
		// informa que deve remover o grupo de Administradores para adicionar outros
		if (in_array(1, $gruposExistentes)) {
			return [
				'status'   => 403,
				'mensagem' => [
					'status' => 'error',
					'mensagem' => 'Este usuário já é um administrador. Remova este grupo do usuário para adicionar outros.'
				]
			];
		}

		// Não permitir atribuir grupo paciente
		// Quando o usuário é um paciente, não pode adicionar grupos
		if (in_array(2, $gruposNovos)) {
			return [
				'status'   => 403,
				'mensagem' => [
					'status' => 'error',
					'mensagem' => 'Não é permitido adicionar o grupo paciente para este usuário.'
				]
			];
		}

		return null; // válido
	}

}
