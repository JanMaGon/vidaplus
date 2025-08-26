<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Logs extends BaseController
{

	private $usuarioModel;
	private $grupoUsuarioModel;

	public function __construct()
	{
		$this->usuarioModel = new \App\Models\UsuarioModel();
		$this->grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();

		helper('filesystem');
	}

	public function index()
	{

		$datas = $this->recuperaDatasLog();

		return $this->response
					->setStatusCode(200)
					->setJSON([
						'status' => 'OK',
						'datas' => $datas
					]);

	}

    public function consultar()
    {
        
		$dados = $this->getRequestData();

		if ($dados instanceof \CodeIgniter\HTTP\ResponseInterface) {
			return $dados; // JSON inválido, já retorna a resposta 400
		}

		$validacao = service('validation');

		$regras = [
			'data_escolhida' => 'required',
			'usuario_id'     => 'required'
		];

		$validacao->setRules($regras);

		if (! $validacao->withRequest($this->request)->run()) {

			return $this->response
						->setStatusCode(500) // Erro interno do servidor
						->setJSON([
							'status' => 'error',
							'mensagem' => 'Não é permitido enviar o(s) campo(s) vazio(s)',
							'erros_model' => $validacao->getErrors()
						]);

		}

		$grupoUsuario = $this->grupoUsuarioModel->verificaUsuarioPaciente($dados['usuario_id']);
		
		// Não lista os logs de usuários do grupo paciente 
		if (!empty($grupoUsuario) AND $grupoUsuario->grupo_id == 2) {

			return $this->response
						->setStatusCode(400) // Bad Request
						->setJSON([
							'status' => 'error',
							'mensagem' => 'Usuário pertence ao grupo de pacientes.'
						]);

		}

		$resultadoLog = $this->consultaLog($dados['data_escolhida'], $dados['usuario_id']);

		if ($resultadoLog === null) {

			return $this->response
						->setStatusCode(400) // Bad Request
						->setJSON([
							'status' => 'error',
							'mensagem' => 'Não foram encontrados registros com os parâmetros informados.'
						]);

		}

		return $this->response
					->setStatusCode(200)
					->setJSON([
						'status' => 'OK',
						'logs' => $resultadoLog
					]);
		
    }

	/**
	 * Método que recupera as datas disponíveis para analisar os log
	 * 
	 * @return array|null
	 */
	private function recuperaDatasLog()
	{

		$arquivosLogs = get_filenames(WRITEPATH . 'logs/');

		$datasDisponiveis = [];

		if (empty($arquivosLogs)) {

			return [];

		}

		foreach ($arquivosLogs as $key => $arquivo) {

			if (strpos($arquivo, 'html')) {
				unset($arquivosLogs[$key]);
			} else {
				$datasDisponiveis[] = substr($arquivo, 4, 10);
			}

		}


		return $datasDisponiveis;
	}

	/**
	 * Método que recupera no arquivo de log as ações do usuário
	 * 
	 * @param string $dataEscolhida
	 * @param int $usuarioId
	 * @return string|null
	 */
	private function consultaLog(string $dataEscolhida, int $usuarioId)
	{
		
		$arquivo = WRITEPATH . "logs/log-$dataEscolhida.log";

		if (! is_file($arquivo)) {

			return null;

		}

		$procuraPor = "[ACAO-USUARIO-ID-$usuarioId]";

		$arquivo = file_get_contents($arquivo);

		$padrao = preg_quote($procuraPor, '/');

		$padrao = "/^.*$padrao.*\$/m";

		if (preg_match_all($padrao, $arquivo, $correspondencias)) {

			$resultado = nl2br(implode("\n\r", $correspondencias[0]));

			return $resultado;

		}

		return null;

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
