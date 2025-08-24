<?php

namespace App\Models;

use CodeIgniter\Model;

class PacienteModel extends Model
{
    protected $table            = 'pacientes';
    protected $returnType       = \App\Entities\Paciente::class;
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'usuario_id',
        'nome',
        'cpf',        
        'data_nascimento',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules      = [
		'id'           => 'permit_empty|is_natural_no_zero',
		'nome'         => 'required|min_length[3]|max_length[125]',
		'email'        => 'required|valid_email|max_length[125]|is_unique[pacientes.email,id,{id}]', // Não pode ter espaços
		'cpf'          => 'required|exact_length[14]|validaCPF|is_unique[pacientes.cpf,id,{id}]',
		'telefone'     => 'required|max_length[15]|is_unique[pacientes.telefone,id,{id}]',
		'cep'          => 'required|exact_length[9]'
	];
    protected $validationMessages   = [
		'nome' => [
			'required'   => 'O campo nome é obrigatório. Não pode ser vazio.',
			'min_length' => 'O campo nome deve ter pelo menos 3 caracteres.',
			'max_length' => 'O campo nome não pode ter mais de 125 caracteres.'
		],
		'email' => [
			'required'   => 'O campo email é obrigatório. Não pode ser vazio.',
			'valid_email' => 'O campo email deve conter um endereço de email válido.',
			'max_length' => 'O campo email não pode ter mais de 230 caracteres.',
			'is_unique' => 'Já existe um paciente cadastrado com este email.'
		],
		'cpf' => [
			'required'   => 'O campo cpf é obrigatório. Não pode ser vazio.',
			'exact_length' => 'O campo email deve conter 14 caracteres.',
			'is_unique' => 'Já existe um paciente cadastrado com este cpf.'
		],
		'telefone' => [
			'required'   => 'O campo telefone é obrigatório. Não pode ser vazio.',
			'max_length' => 'O campo telefone não pode ter mais de 15 caracteres.',
			'is_unique' => 'Já existe um paciente cadastrado com este telefone.'
		],
		'cep' => [
			'required'   => 'O campo cep é obrigatório. Não pode ser vazio.',
			'exact_length' => 'O campo cep deve conter 9 caracteres.',
		],
	];
}
