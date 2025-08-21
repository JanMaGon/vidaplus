<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

	public function generate_jwt_key(): string
	{
		// Método para gerar uma chave JWT		
		$chave = bin2hex(random_bytes(32));
		return $chave;
	}
}
