<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PacienteFakerSeeder extends Seeder
{
    public function run()
    {
        
        $pacienteModel     = new \App\Models\PacienteModel();
        $usuarioModel      = new \App\Models\UsuarioModel();
        $grupoUsuarioModel = new \App\Models\GrupoUsuarioModel();

        $faker = \Faker\Factory::create('pt_BR');

        $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));
        $faker->addProvider(new \Faker\Provider\pt_BR\PhoneNumber($faker));

        $criarQuantosPacientes = 10;

        for ($i = 0; $i < $criarQuantosPacientes; $i++) {

            $nomeGerado = $faker->unique()->name;
            $emailGerado = $faker->unique()->email;

            $paciente = [
                'nome'            => $nomeGerado,
                'cpf'             => $faker->unique()->cpf,
                'data_nascimento' => $faker->date($format = 'Y-m-d'),
                'telefone'        => $faker->unique()->cellphoneNumber,
                'email'           => $emailGerado,
                'cep'             => $faker->postcode,
                'endereco'        => $faker->streetName,
                'numero'          => $faker->buildingNumber,
                'bairro'          => $faker->city,
                'cidade'          => $faker->city,
                'estado'          => $faker->stateAbbr,
            ];

            // Criar o paciente
            $pacienteModel->skipValidation(true)->insert($paciente);

            $usuario = [
                'nome'          => $nomeGerado,
                'email'         => $emailGerado,
                'password'      => '123456',
                'ativo'         => true,
            ];

            // Criar o usuário
            $usuarioModel->skipValidation(true)->protect(false)->insert($usuario);

            $grupoUsuario = [
                'usuario_id' => $usuarioModel->getInsertID(),
                'grupo_id'   => 2, // Pacientes
            ];

            // Associar o usuário ao grupo de pacientes
            $grupoUsuarioModel->protect(false)->insert($grupoUsuario);

            // Atualizar o paciente com o ID do usuário criado
            $pacienteModel->protect(false)
                          ->where('id', $pacienteModel->getInsertID())
                          ->set('usuario_id', $usuarioModel->getInsertID())
                          ->update();
        }

        echo "$criarQuantosPacientes pacientes semeaos com sucesso!";

    }
}
