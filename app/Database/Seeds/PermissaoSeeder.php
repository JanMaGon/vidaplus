<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    public function run()
    {
        $permissaoModel = new \App\Models\PermissaoModel();

		$permissoes = [
			['nome' => 'listar-usuarios'],
			['nome' => 'criar-usuarios'],
			['nome' => 'editar-usuarios'],
			['nome' => 'excluir-usuarios'],
			['nome' => 'listar-grupos'],
			['nome' => 'criar-grupos'],
			['nome' => 'editar-grupos'],
			['nome' => 'excluir-grupos'],
			['nome' => 'listar-pacientes'],
			['nome' => 'criar-pacientes'],
			['nome' => 'editar-pacientes'],
			['nome' => 'excluir-pacientes'],
		];

		foreach ($permissoes as $permissao) {
			$permissaoModel->protect(false)->insert($permissao);
		}

		// Exibir mensagem de sucesso
		echo "Permissões criadas com sucesso.\n";
    }
}
