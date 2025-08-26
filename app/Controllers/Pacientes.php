<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Entities\Paciente;

class Pacientes extends BaseController
{

	private $pacienteModel;
	private $usuarioModel;
	private $grupoUsuarioModel;

	public function __construct()
	{
		$this->pacienteModel     = new \App\Models\PacienteModel();
		$this->usuarioModel      = new \App\Models\UsuarioModel();
		$this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();
	}

	public function index()
	{

		$atributos = [
			'id',
			'nome',
			'cpf',
			'email',
			'deletado_em',
		];

		$pacientes = $this->pacienteModel->select($atributos)->findAll();

		$data = [];

		foreach ($pacientes as $paciente) {

			// Receberá o array de objetos de pacientes
			$data[] = [
				'id'          => (int) $paciente->id,
				'nome'        => esc($paciente->nome),
				'cpf'         => esc($paciente->cpf),
				'email'       => esc($paciente->email),
				'deletado_em' => $paciente->deletado_em,
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setStatusCode(200)->setJSON($retorno);
	}

	public function exibir($id = null)
	{

		$paciente = $this->buscaPacienteOu404($id);

		// Verifica se $paciente é uma resposta HTTP (ResponseInterface).
		// Se for, encerra a execução.
		if ($paciente instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $paciente; // Se já for a resposta 404, retorna direto
		}

		// Converte o objeto para array
		$dados = $paciente->toArray();


		return $this->response->setStatusCode(200)->setJSON($dados);
	}

	public function criar()
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		// Cria um novo objeto da Entidade Paciente
		$paciente = new Paciente($dados);

		if ($this->pacienteModel->protect(false)->save($paciente)) {

			$this->criaUsuarioParaPaciente($paciente);

			// Retornamos junto com o status o último ID inserido
			// Ou seja, o ID do paciente recém-criado
			return $this->response
						->setStatusCode(200)
						->setJSON([
							'status' => 'OK',
							'mensagem' => "Paciente criado com sucesso",
							'id' => $this->pacienteModel->getInsertID()
						]);
		}

		// Alguma validação falhou
		return $this->response
					->setStatusCode(500) // Erro interno do servidor
					->setJSON([
						'status' => 'error',
						'mensagem' => 'Erro ao criar o paciente',
						'erros_model' => $this->pacienteModel->errors()
					]);
	}

	public function atualizar($id = null)
	{
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		$paciente = $this->buscaPacienteOu404($id);

		if ($paciente instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $paciente; // Se já for a resposta 404, retorna direto
		}

		// Garante que a ID usada na atualização é a da URL
		$dados['id'] = $id;

		// Preenche os atributos do paciente com os valores do POST
		$paciente->fill($dados);

		if ($paciente->hasChanged() === false) {

			return $this->response
						->setStatusCode(200)
						->setJSON([
							'status' => 'OK',
							'mensagem' => 'Nenhum dado foi modificado'
						]);

		}


		if ($this->pacienteModel->save($paciente)) {

			if ($paciente->hasChanged('email')) {

				$this->usuarioModel->atualizaEmailPaciente($paciente->usuario_id, $paciente->email);

				return $this->response
							->setStatusCode(200)
							->setJSON([
								'status' => 'OK',
								'mensagem' => "Paciente atualizado com sucesso. IMPORTANTE: informe ao paciente o novo e-mail de acesso ao sistema.",
								'novo_email' => $paciente->email,
							]);
			}

			return $this->response
						->setStatusCode(200)
						->setJSON([
							'status' => 'OK',
							'mensagem' => "Paciente atualizado com sucesso"
						]);

		}

		// Alguma validação falhou
		return $this->response
			->setStatusCode(500) // Erro interno do servidor
			->setJSON([
				'status' => 'error',
				'mensagem' => 'Erro ao atualizar o paciente',
				'erros_model' => $this->pacienteModel->errors()
			]);
	}

	public function remover($id = null)
	{

		$paciente = $this->buscaPacienteOu404($id);

		if ($paciente instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $paciente; // Se já for a resposta 404, retorna direto
		}

		if ($paciente->deletado_em != null) {

			return $this->response
						->setStatusCode(400)
						->setJSON([
							'status' => 'error',
							'mensagem' => "Esse paciente já encontra-se excluído"
						]);

		}

		if ($this->pacienteModel->delete($paciente->id)) {

			$usuario = [
				'id'    => $paciente->usuario_id,
				'ativo' => false,
			];

			// Desativa o usuário do paciente
			$this->usuarioModel->skipValidation(true)->protect(false)->save($usuario);

			// Deleta o usuário do paciente
			$this->usuarioModel->delete($paciente->usuario_id);

			return $this->response
						->setStatusCode(200)
						->setJSON([
							'status' => 'OK',
							'mensagem' => "Paciente excluído com sucesso"
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
			'cpf',
			'email',
		];

		// Busca apenas os registros que são soft deleted
		$pacientes = $this->pacienteModel
						  ->onlyDeleted()
						  ->select($atributos)
						  ->findAll();

		$data = [];

		foreach ($pacientes as $paciente) {
			$data[] = [
				'id'    => (int) $paciente->id,
				'nome'  => esc($paciente->nome),
				'cpf'   => esc($paciente->cpf),
				'email' => esc($paciente->email),
			];
		}

		$retorno = [
			'data' => $data,
		];

		return $this->response->setStatusCode(200)->setJSON($retorno);

	}

	public function restaurar($id = null)
	{

		$paciente = $this->buscaPacienteOu404($id);

		if ($paciente instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $paciente; // Se já for a resposta 404, retorna direto
		}

		if ($paciente->deletado_em == null) {
			return $this->response
				->setStatusCode(400)
				->setJSON([
					'status' => 'error',
					'mensagem' => "Apenas pacientes excluídos podem ser restaurados"
				]);
		}

		$paciente->deletado_em = null; // Limpa o campo de exclusão

		if ($this->pacienteModel->protect(false)->save($paciente)) {

			$usuario = [
				'id'    => $paciente->usuario_id,
				'deletado_em' => null,
				'ativo' => true,
			];

			// Restaura e reativa o usuário do paciente
			$this->usuarioModel->skipValidation(true)->protect(false)->save($usuario);

			return $this->response
						->setStatusCode(200)
						->setJSON([
							'status' => 'OK',
							'mensagem' => "Paciente restaurado com sucesso"
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
	 * Recupera o paciente pelo ID ou retorna resposta 404.
	 *
	 * @param int|null $id
	 * @return object|\CodeIgniter\HTTP\ResponseInterface
	 */
	private function buscaPacienteOu404($id = null)
	{

		if (!$id || !$paciente = $this->pacienteModel->withDeleted(true)->find($id)) {

			return $this->response
						->setStatusCode(404) // Not Found
						->setJSON([
							'status'  => 'error',
							'message' => "Não encontramos o paciente"
						]);

		}

		return $paciente;
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
	 * Cria um usuário para o paciente recém-criado.
	 *
	 * A senha padrão será os 6 primeiros dígitos do CPF.
	 *
	 * @param object $paciente Objeto paciente recém-criado
	 * @return void
	 */
	private function criaUsuarioParaPaciente($paciente)
	{
		// Remove tudo que não for número
		$cpfNumerico = preg_replace('/\D/', '', $paciente->cpf); // "11111111111"

		// Pega os 6 primeiros dígitos
		$senhaPadrao = substr($cpfNumerico, 0, 6); // "111111"

		$usuario = [
			'nome'          => $paciente->nome,
			'email'         => $paciente->email,
			'password'      => $senhaPadrao,
			'ativo'         => true,
		];

		// Criar o usuário
		$this->usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

		$grupoUsuario = [
			'usuario_id' => $this->usuarioModel->getInsertID(),
			'grupo_id'   => 2, // Pacientes
		];

		// Associar o usuário ao grupo de pacientes
		$this->grupoUsuarioModel->protect(false)->insert($grupoUsuario);

		// Atualizar o paciente com o ID do usuário criado
		$this->pacienteModel->protect(false)
							->where('id', $this->pacienteModel->getInsertID())
							->set('usuario_id', $this->usuarioModel->getInsertID())
							->update();

	}
}
