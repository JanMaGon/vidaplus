<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $returnType       = \App\Entities\Usuario::class;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'nome',
        'email',
        'password',
        'reset_hash',
        'reset_expira_em',
        //campo ativo:
        //não é recomendado colocar no allowedFields campos do tipo is_admin, ativo,
        //campos que podem elevar o nível de permissão de um usuário ou o nível de acesso de um usuário.
        //o campo ativo será parametrizado nas classes específicas e métodos específicos
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';
    protected $deletedField  = 'deletado_em';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];

    // Callbacks
    protected $beforeInsert   = [];
    protected $beforeUpdate   = [];
    
}
