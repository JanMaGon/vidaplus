<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioFakerSeeder extends Seeder
{
    public function run()
    {
        
        $usuarioModel = new \App\Models\UsuarioModel();

        $faker = \Faker\Factory::create();

        $criarQuantosUsuarios = 50;

        $usuariosPush = [];

        for ($i = 0; $i < $criarQuantosUsuarios; $i++) {
            
            array_push($usuariosPush, [
                'nome' => $faker->unique()->name(),
                'email' => $faker->unique()->email(),
                'password_hash' => '123456',
                'ativo' => true,
            ]);

        }

        $usuarioModel->skipValidation(true) //bypass na valiação
                     ->protect(false) // baypass na proteção dos campos allowedFiels
                     ->insertBatch($usuariosPush);

        echo "$criarQuantosUsuarios usuários semeados com sucesso!";

    }
}
