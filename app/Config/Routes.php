<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Rotas API Usuários
$routes->group('api', function($routes) {
    // Listar todos os usuários
    $routes->get('usuarios', 'Usuarios::index');

    // Exibir um usuário específico
    $routes->get('usuarios/(:num)', 'Usuarios::exibir/$1');

    // Criar usuário (POST)
    $routes->post('usuarios', 'Usuarios::criar');

    // Atualizar usuário (PUT ou PATCH)
    $routes->put('usuarios/(:num)', 'Usuarios::atualizar/$1');
    $routes->patch('usuarios/(:num)', 'Usuarios::atualizar/$1');

    // Deletar usuário (DELETE)
    $routes->delete('usuarios/(:num)', 'Usuarios::remover/$1');
});
