<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->set404Override(function() {
    $response = service('response');
    $response->setStatusCode(404)
             ->setJSON([
                 'status'   => 'error',
                 'mensagem' => 'Endpoint não encontrado. Verifique se a URL está correta e sem barra final.'
             ])
             ->send();   // força saída

    exit; // evita que CI tente processar de novo
});


$routes->get('/', 'Home::index');

/*********************** 
* || Rotas API ||      * 
***********************/
// ** Autenticação **
// Login
$routes->post('api/login', 'Login::index');

// ** Usuários **
// Listar todos os usuários
$routes->get('api/usuarios', 'Usuarios::index');
// Usuários deletados (soft delete)
$routes->get('api/usuarios/deletados', 'Usuarios::lixeira');
// Restaurar usuário (GET)
$routes->get('api/usuarios/restaurar/(:num)', 'Usuarios::restaurar/$1');
// Exibir um usuário específico
$routes->get('api/usuarios/(:num)', 'Usuarios::exibir/$1');
// Exibir os grupos que o usuário pertence e os que não pertence
$routes->get('api/usuarios/(:num)/grupos', 'Usuarios::grupos/$1');
// Salvar grupos que o usuário pertence (POST)
$routes->post('api/usuario/(:num)/grupos/salvar', 'Usuarios::salvarGrupos/$1');
// Criar usuário (POST)
$routes->post('api/usuario', 'Usuarios::criar');
// Atualizar usuário (PUT ou PATCH)
$routes->put('api/usuario/(:num)', 'Usuarios::atualizar/$1');
$routes->patch('api/usuario/(:num)', 'Usuarios::atualizar/$1');
// Deletar usuário (DELETE)
$routes->delete('api/usuario/(:num)', 'Usuarios::remover/$1');
// Remover grupos de um usuario (DELETE)
$routes->delete('api/usuario/(:num)/grupos/(:num)/remover', 'Usuarios::removerGrupos/$1/$2');

// ** Grupos **
// Listar todos os grupos
$routes->get('api/grupos', 'Grupos::index');
// Gupos deletados (soft delete)
$routes->get('api/grupos/deletados', 'Grupos::lixeira');
// Restaurar grupo (GET)
$routes->get('api/grupos/restaurar/(:num)', 'Grupos::restaurar/$1');
// Exibir um grupo específico
$routes->get('api/grupos/(:num)', 'Grupos::exibir/$1');
// Exibir as permissões que o grupo possui e as que não possui
$routes->get('api/grupo/(:num)/permissoes', 'Grupos::permissoes/$1');
// Salvar permissões do grupo (POST)
$routes->post('api/grupo/(:num)/permissoes/salvar', 'Grupos::salvarPermissoes/$1');
// Criar grupo (POST)
$routes->post('api/grupo', 'Grupos::criar');
// Atualizar grupo (PUT ou PATCH)
$routes->put('api/grupo/(:num)', 'Grupos::atualizar/$1');
$routes->patch('api/grupo/(:num)', 'Grupos::atualizar/$1');
// Deletar grupo (DELETE)
$routes->delete('api/grupo/(:num)', 'Grupos::remover/$1');
// Remover permissões de um grupo (DELETE)
$routes->delete('api/grupo/(:num)/permissoes/(:num)/remover', 'Grupos::removerPermissoes/$1/$2');

// ** Pacientes **
// Listar todos os pacientes
$routes->get('api/pacientes', 'Pacientes::index');
// Pacientes deletados (soft delete)
$routes->get('api/pacientes/deletados', 'Pacientes::lixeira');
// Restaurar pacientes (GET)
$routes->get('api/pacientes/restaurar/(:num)', 'Pacientes::restaurar/$1');
// Exibir um paciente específico
$routes->get('api/pacientes/(:num)', 'Pacientes::exibir/$1');
// Criar paciente (POST)
$routes->post('api/paciente', 'Pacientes::criar');
// Atualizar paciente (PUT ou PATCH)
$routes->put('api/paciente/(:num)', 'Pacientes::atualizar/$1');
$routes->patch('api/paciente/(:num)', 'Pacientes::atualizar/$1');
// Deletar paciente (DELETE)
$routes->delete('api/paciente/(:num)', 'Pacientes::remover/$1');