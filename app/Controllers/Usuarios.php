<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Usuario;
use CodeIgniter\HTTP\ResponseInterface;

class Usuarios extends BaseController
{

	private $usuarioModel;

	public function __construct()
	{
		$this->usuarioModel = new \App\Models\UsuarioModel();
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
					'mensagem' => "Usuário {$id} atualizado com sucesso"
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
					'message' => "Não encontramos o usuário {$id}"
				]);
		}

		return $usuario;
	}

	/**
	 * Lê dados enviados via JSON ou form-data, independente do método HTTP.
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
