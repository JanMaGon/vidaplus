<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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

		$usuarios = $this->usuarioModel->select($atributos)
			->findAll();

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

		if (!$id || !$usuario = $this->usuarioModel->withDeleted(true)->find($id)) {
			return $this->response
				->setStatusCode(404)
				->setJSON([
					'status'  => 'error',
					'message' => "Não encontramos o usuário {$id}"
				]);
		}


		return $this->response->setStatusCode(200)->setJSON($usuario);
	}

	/**
     * Criar usuário
     */
    public function criar()
    {
        $dados = $this->getRequestData();

        return $this->response->setJSON([
            'status' => 'OK',
            'mensagem' => 'Usuário recebido com sucesso',
            'dados_recebidos' => $dados
        ]);
    }

    /**
     * Atualizar usuário
     */
    public function atualizar($id = null)
    {
        $dados = $this->getRequestData();

        return $this->response->setJSON([
            'status' => 'OK',
            'mensagem' => "Usuário {$id} atualizado com sucesso",
            'dados_recebidos' => $dados
        ]);
    }

    /**
     * Excluir usuário
     */
    public function remover($id = null)
    {
        return $this->response->setJSON([
            'status' => 'OK',
            'mensagem' => "Usuário {$id} excluído com sucesso"
        ]);
    }

	/**
    * Lê dados enviados via JSON ou form-data, independente do método HTTP
    */
    private function getRequestData()
    {
        $dados = [];

        // Se o Content-Type for JSON
        if (stripos($this->request->getHeaderLine('Content-Type'), 'application/json') !== false) {
            try {
                $dados = $this->request->getJSON(true); // retorna array
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'erro',
                    'mensagem' => 'JSON inválido'
                ])->setStatusCode(400);
            }
        } else {
            // Para POST, PUT, PATCH ou DELETE com form-data ou x-www-form-urlencoded
            $dados = $this->request->getRawInput();

            // POST comum também pode usar getPost()
            if (empty($dados) && $this->request->getMethod() === 'post') {
                $dados = $this->request->getPost();
            }
        }

        return $dados;
    }
}
