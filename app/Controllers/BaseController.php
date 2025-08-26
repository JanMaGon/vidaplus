<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['autenticacao'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Registra uma ação do usuário no log de atividades.
     *
     * @param string $texto Ação realizada pelo usuário.
     * @return void
     */
    protected function registraAcaoDoUsuario(string $texto, int $usuario_id)
    {

        $usuario = usuario_logado($usuario_id); // Pega o usuário logado

        // Gera mensagem como: Usuário 123 logou no sistema com IP 127.0.0.1
        $info = [
            'id'         => $usuario->id,
            'nome'       => $usuario->nome,
            'email'      => $usuario->email,
            'ip_address' => $this->request->getIPAddress(),
        ];

        log_message('info', "[ACAO-USUARIO-ID-{id}] Usuário: {nome} | $texto | com o e-mail {email} e com IP {ip_address}", $info);
    }
}
