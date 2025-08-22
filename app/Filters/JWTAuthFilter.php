<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine("Authorization");

        if (!$header) {
            return service('response')->setJSON([
                'error' => 'Token não informado.'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // O header deve ser no formato: Bearer <token>
        if (strpos($header, 'Bearer ') !== 0) {
            return service('response')->setJSON([
                'error' => 'Formato de token inválido. Use: Bearer {seu_token}'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = trim(str_replace('Bearer', '', $header));

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));

            // Aqui podemos adicionar o usuário decodificado na requisição
            $request->user = $decoded->data;
        } catch (\Exception $e) {
            return service('response')->setJSON([
                'error' => 'Token inválido ou expirado.',
                'message' => $e->getMessage()
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nada a fazer depois da resposta
    }
}
