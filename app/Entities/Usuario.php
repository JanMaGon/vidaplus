<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Usuario extends Entity
{
    protected $dates   = [
		'criado_em', 
		'atualizado_em', 
		'deletado_em',
	];

	/**
	 * Método que verifica se a senha é válida
	 * 
	 * @param string $password
	 * @return bool
	 */
	public function verificaPassword(string $password): bool
	{
		// password_hash é o nome do campo na tabela
		return password_verify($password, $this->password_hash);
	}
}
