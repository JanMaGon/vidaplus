<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

/*********************** 
* || Rotas API ||      * 
***********************/
// ** Usuários **
// Listar todos os usuários
$routes->get('api/usuarios', 'Usuarios::index');
// Usuários deletados (soft delete)
$routes->get('api/usuarios/deletados', 'Usuarios::lixeira');
// Restaurar usuário (GET)
$routes->get('api/usuarios/restaurar/(:num)', 'Usuarios::restaurar/$1');
// Exibir um usuário específico
$routes->get('api/usuarios/(:num)', 'Usuarios::exibir/$1');
// Criar usuário (POST)
$routes->post('api/usuarios', 'Usuarios::criar');
// Atualizar usuário (PUT ou PATCH)
$routes->put('api/usuarios/(:num)', 'Usuarios::atualizar/$1');
$routes->patch('api/usuarios/(:num)', 'Usuarios::atualizar/$1');
// Deletar usuário (DELETE)
$routes->delete('api/usuarios/(:num)', 'Usuarios::remover/$1');

// ** Grupos **