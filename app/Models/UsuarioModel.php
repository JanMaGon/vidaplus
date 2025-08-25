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
    protected $validationRules      = [
		'id'           => 'permit_empty|is_natural_no_zero',
		'nome'         => 'required|min_length[3]|max_length[125]',
		'email'        => 'required|valid_email|max_length[230]|is_unique[usuarios.email,id,{id}]', // Não pode ter espaços
		'password'     => 'required|min_length[6]',
		'password_confirmation' => 'required_with[password]|matches[password]'
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
			'is_unique' => 'Já existe um usuário cadastrado com este email.'
		],
		'password' => [
			'required'   => 'O campo password é obrigatório. Não pode ser vazio.',
			'min_length' => 'O campo password deve ter pelo menos 6 caracteres.'
		],
		'password_confirmation' => [
			'required_with' => 'Por favor confirme a sua senha.',
			'matches'       => 'As senhas precisam combinar.'
		]
	];

    // Callbacks
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

	// Método para hashear a senha antes de inserir ou atualizar o usuário
    protected function hashPassword(array $data)
    {

        if (isset($data['data']['password'])) {

            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

            // Remove dos dados a serem salvos
            unset($data['data']['password']);
            unset($data['data']['password_confirmation']);

        }

        return $data;

    }

	/**
	* Método que recupera o usuário para logar na aplicação
	* 
	* @param string $email
	* @return object|null
	*/
	public function buscaUsuarioPorEmail(string $email)
	{
		return $this->where('email', $email)->where('deletado_em', null)->first();
	}

	/**
	 * Método que recupera as permissões do usuário logado
	 * 
	 * @param int $usuario_id
	 * @return null|array
	 */
	public function recuperaPermissoesDoUsuarioLogado(int $usuario_id)
	{

		$atributos = [
			'usuarios.id',
			'usuarios.nome AS usuario',
			'grupos_usuarios.*',
			'permissoes.nome AS permissao'
		];

		return $this->select($atributos)
					->asArray()
					->join('grupos_usuarios', 'grupos_usuarios.usuario_id = usuarios.id')
					->join('grupos_permissoes', 'grupos_permissoes.grupo_id = grupos_usuarios.grupo_id')
					->join('permissoes', 'permissoes.id = grupos_permissoes.permissao_id')
					->where('usuarios.id', $usuario_id)
					->groupBy('permissoes.nome')
					->findAll();

	}

	/**
	 * Método que atualiza o email do paciente na tabela de usuários
	 * 
	 * @param int $usuario_id
	 * @param string $email
	 * @return bool
	 */
	public function atualizaEmailPaciente(int $usuario_id, string $email)
	{

		return $this->protect(false)
			 		->where('id', $usuario_id)
			 		->set('email', $email)
			 		->update();

	}

	/**
	* Método que valida se o e-mail do paciente e único em usuário
	* 
	* @param string $email
	* @return object|null
	*/
	public function validaEmailPaciente($usuario_id = null, string $email)
	{
		if ($usuario_id === null) {

			return $this->where('email', $email)->first();

		} else {

			return $this->where('email', $email)
						->where('id !=', $usuario_id)
						->first();
		}
	}
    
}
