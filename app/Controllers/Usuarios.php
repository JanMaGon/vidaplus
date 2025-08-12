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
            'imagem',
        ];
        
        $usuarios = $this->usuarioModel->select($atributos)
                                       ->findAll();
        
        $data = [];

        foreach($usuarios as $usuario){

            // Receberá o array de objetos de usuários
            $data[] = [
                'id' => (int) $usuario->id,
                'imagem' => $usuario->imagem,
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

        if(!$id || !$usuario = $this->usuarioModel->withDeleted(true)->find($id)){
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status'  => 'error',
                    'message' => "Não encontramos o usuário {$id}"
                ]);
        }

        return $this->response->setStatusCode(200)->setJSON($usuario);

    }
}
