<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\App;

class GrupoModel extends Model
{
    protected $table            = 'grupos';
    protected $returnType       = \App\Entities\Grupo::class;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'nome',
        'descricao',
        'exibir'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules      = [
		'id'           => 'permit_empty|is_natural_no_zero',
		'nome'         => 'required|max_length[120]|is_unique[grupos.nome,id,{id}]',
        'descricao'    => 'required|max_length[240]',
	];
    protected $validationMessages   = [
		'nome' => [
			'required'   => 'O campo nome é obrigatório. Não pode ser vazio.',
			'max_length' => 'O campo nome não pode ter mais de 120 caracteres.',
			'is_unique' => 'Já existe um grupos cadastrado com este nome.'
		],
		'descricao' => [
			'required'   => 'O campo descrição é obrigatório. Não pode ser vazio.',
			'max_length' => 'O campo descrição não pode ter mais de 240 caracteres.'
		]
	];
    
}
