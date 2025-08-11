# VidaPlus - Projeto Final

**Projeto:** Desenvolvimento Back-end  
**Tecnologias:** PHP 8.1+, CodeIgniter 4, Composer, MySQL, XAMPP (Windows)  

---

## Sumário

- [Sobre o Projeto](#sobre-o-projeto)  
- [Pré-requisitos](#pré-requisitos)  
- [Instalação](#instalação)  
- [Configuração](#configuração)  
- [Estrutura Importante](#estrutura-importante)  
- [Considerações Finais](#considerações-finais)  

---

## Sobre o Projeto

VidaPlus é uma aplicação back-end desenvolvida para fins acadêmicos utilizando CodeIgniter 4.   

---

## Pré-requisitos

Antes de rodar o projeto, certifique-se de ter instalado:

- PHP 8.1 ou superior com extensões necessárias (mysqli, intl, etc.)  
- Composer (gerenciador de dependências PHP)  
- Servidor MySQL (MariaDB)  
- Servidor web local (XAMPP, WAMP ou similar)  

---

## Instalação

1. Clone o repositório:  
   ```bash
   git clone https://github.com/JanMaGon/vidaplus.git
   cd vidaplus

2. Instale as dependências via Composer:
   ```bash
   composer install

3. Crie o arquivo de ambiente copiando o modelo:
   ```bash
   cp .env.example .env

---

## Configuração

1. Abra o arquivo .env e configure as variáveis do banco de dados:
   database.default.hostname = localhost
   database.default.database = nome_do_banco
   database.default.username = root
   database.default.password = 
   database.default.DBDriver = MySQLi
   database.default.port = 3306

2. Garanta que as pastas dentro de writable/ estejam criadas com permissões de escrita:
  - writable/cache
  - writable/logs
  - writable/session
  - writable/uploads (se usar uploads)

3. Crie o banco de dados, rode as migrations e seeders para criar a estrutura inicial do banco:
   ```bash
   php spark db:create vidaplus
   php spark migrate
   php spark db:seed NomeDoSeeder

---

## Estrutura Importante

app/ → Código fonte do projeto (Controllers, Models, Views, Configs, etc.)

public/ → Pasta pública para acesso via navegador

writable/ → Pasta para cache, logs, sessões, uploads — necessária para gravação

vendor/ → Dependências do Composer (não versionado no Git)

spark → CLI do CodeIgniter para manutenção e comandos (migrations, seeders etc.)

---

## Considerações Finais

As pastas writable/cache e writable/logs possuem arquivo .gitkeep para garantir sua presença após o clone.



