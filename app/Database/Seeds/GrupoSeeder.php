<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GrupoSeeder extends Seeder
{
    public function run()
    {
        
        $grupoModel = new \App\Models\GrupoModel();

        $grupos = [
            [ // ID 1 Administrador
                'nome' => 'Administrador', 
                'descricao' => 'Grupo com acesso total ao sistema.',
                'exibir' => false, // é false pois o Administrador não pode ser exibido como responsável pela criação de pedidos ou serviços 
            ],
            [ // ID 2 Pacientes
                'nome' => 'Pacientes',
                'descricao' => 'Grupo destinado para atribuição de pacientes, que poderão logar no sistema para acessar informações sobre suas consultas e exames realizados.',
                'exibir' => false,
            ],
            [
                'nome' => 'Atendentes',
                'descricao' => 'Grupo com acesso ao sistema para realizar atendimento os pacientes.',
                'exibir' => true, // é true pois o Atendente pode ser exibido como responsável pela criação de pedidos ou serviços
            ],
        ];

        foreach ($grupos as $grupo) {
            $grupoModel->insert($grupo);
        }

        echo "Grupos criados com sucesso!\n";

    }
}
