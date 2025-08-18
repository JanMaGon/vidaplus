<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Grupo;
use CodeIgniter\HTTP\ResponseInterface;

class Grupos extends BaseController
{

    private $grupoModel;
    private $grupoPermissaoModel;

    public function __construct()
    {
        $this->grupoModel = new \App\Models\GrupoModel();
        $this->grupoPermissaoModel = new \App\Models\GrupoPermissaoModel();
    }

    public function index()
    {
        
        $atributos = [
			'id',
			'nome',
			'descricao',
			'exibir',
		];

		$grupos = $this->grupoModel->select($atributos)->findAll();

		$data = [];

		foreach ($grupos as $grupo) {

			// Receberá o array de objetos de grupos
			$data[] = [
				'id' => (int) $grupo->id,
				'nome' => esc($grupo->nome),
				'descricao' => esc($grupo->descricao),
				'exibir' => (bool) $grupo->exibir,
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setStatusCode(200)->setJSON($retorno);

    }

	public function exibir($id = null)
	{

		$grupo = $this->buscaGrupoOu404($id);

		// Verifica se $grupo é uma resposta HTTP (ResponseInterface).
		// Se for, encerra a execução.
		if ($grupo instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupo; // Se já for a resposta 404, retorna direto
		}

		// Converte o objeto para array
		$dados = $grupo->toArray();

		return $this->response->setStatusCode(200)->setJSON($dados);
	}

	public function criar()
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		// Cria um novo objeto da Entidade Grupo
		$grupo = new Grupo($dados);

		if ($this->grupoModel->save($grupo)) {

			// Retornamos junto com o status o último ID inserido
			// Ou seja, o ID do grupo recém-criado
			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo criado com sucesso",
					'id' => $this->grupoModel->getInsertID()
				]);
		}

		// Alguma validação falhou
		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao criar o grupo',
				'erros_model' => $this->grupoModel->errors()
			]);
	}

	public function atualizar($id = null)
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		$grupo = $this->buscaGrupoOu404($id);

		if ($grupo instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupo; // Se já for a resposta 404, retorna direto
		}

		// Garantimos que os grupos Administrador e Paciente não sejam alterados
		// IDs 1 e 2 são reservados para Administrador e Paciente.
		if ($grupo->id < 3) {

			/*
			* aqui futuramente deve ser aplicado um métdoo para registrar 
			* em um log qual usuário tentou manipular os registros de ID 1 e 2 
			*/

			return $this->response
				->setStatusCode(500) // Erro interno do servidor
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Não é permitido alterações neste grupo.'
				]);

		}

		// Garante que a ID usada na atualização é a da URL
		$dados['id'] = $id;

		// Preenche os atributos do usuário com os valores do POST
		$grupo->fill($dados);

		if ($grupo->hasChanged() === false) {
			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => 'Nenhum dado foi modificado'
				]);
		}

		if ($this->grupoModel->save($grupo)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo atualizado com sucesso"
				]);
		}

		// Alguma validação falhou
		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao atualizar o grupo',
				'erros_model' => $this->grupoModel->errors()
			]);
	}

	public function remover($id = null)
	{

		$grupo = $this->buscaGrupoOu404($id);

		if ($grupo instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupo; // Se já for a resposta 404, retorna direto
		}

		// Garantimos que os grupos Administrador e Paciente não sejam alterados
		// IDs 1 e 2 são reservados para Administrador e Paciente.
		if ($grupo->id < 3) {

			/*
			* aqui futuramente deve ser aplicado um métdoo para registrar 
			* em um log qual usuário tentou manipular os registros de ID 1 e 2 
			*/

			return $this->response
				->setStatusCode(500) // Erro interno do servidor
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Não é permitido alterações neste grupo.'
				]);

		}

		if ($grupo->deletado_em != null) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => "Esse grupo já encontra-se excluído"
				]);
		}

		if ($this->grupoModel->delete($grupo->id)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo excluído com sucesso"
				]);
		}

		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar excluir o grupo'
			]);
	}

	public function lixeira()
	{
		$atributos = [
			'id',
			'nome',
			'descricao',
			'exibir',
			'deletado_em', // Inclui o campo deletado para exibir quando foi excluído
		];

		// Busca apenas os registros que foram soft deleted
		$grupos = $this->grupoModel
			->onlyDeleted()
			->select($atributos)
			->findAll();

		$data = [];

		foreach ($grupos as $grupo) {
			$data[] = [
				'id'    => (int) $grupo->id,
				'nome'  => esc($grupo->nome),
				'descricao' => esc($grupo->descricao),
				'exibir' => (bool) $grupo->exibir,
				'deletado_em' => $grupo->deletado_em,
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

		$grupo = $this->buscaGrupoOu404($id);

		if ($grupo instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupo; // Se já for a resposta 404, retorna direto
		}

		if ($grupo->deletado_em == null) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => "Apenas grupos excluídos podem ser restaurados"
				]);
		}

		$grupo->deletado_em = null; // Limpa o campo de exclusão
		
		if ($this->grupoModel->protect(false)->save($grupo)) {

			return $this->response
				->setStatusCode(200)
				->setJSON([
					'status' => 'OK',
					'mensagem' => "Grupo restaurado com sucesso"
				]);

		}

		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao tentar restaurar o grupo'
			]);
	}

	public function permissoes($id = null)
	{

		$grupo = $this->buscaGrupoOu404($id);

		if ($grupo instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $grupo; // Se já for a resposta 404, retorna direto
		}

		// Grupos Administrador e Paciente não podem ter atribuídas permissões
		// IDs 1 e 2 são reservados para Administrador e Paciente.
		if ($grupo->id < 3) {

			/*
			* aqui futuramente deve ser aplicado um métdoo para registrar 
			* em um log qual usuário tentou manipular os registros de ID 1 e 2 
			*/

			return $this->response
				->setStatusCode(500) // Erro interno do servidor
				->setJSON([
					'status' => 'error',
					'mensagem' => 'Não é necessário atribuir ou remover permissões de acesso para este grupo.'
				]);

		}

		if ($grupo->id > 2) {

			$permissoes = $this->grupoPermissaoModel->recuperaPermissoesDoGrupo($grupo->id);

			$data = [];

			foreach ($permissoes as $permissao) {
				$data[] = [
					'id'    => (int) $permissao->permissao_id,
					'nome'  => esc($permissao->nome),
				];
			}

			$retorno = [
				'data' => $data,
			];

			return $this->response
				->setStatusCode(200)
				->setJSON($retorno);
		}

	}

	/**
	 * Recupera o grupo pelo ID ou retorna resposta 404.
	 *
	 * @param int|null $id
	 * @return object|\CodeIgniter\HTTP\ResponseInterface
	 */
	private function buscaGrupoOu404($id = null)
	{

		if (!$id || !$grupo = $this->grupoModel->withDeleted(true)->find($id)) {
			return $this->response
				->setStatusCode(404)
				->setJSON([
					'status'  => 'error',
					'message' => "Não encontramos o grupo {$id}"
				]);
		}

		return $grupo;
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
