# VOXEL Copilot

> Sistema Operacional de Laudos com Inteligência Artificial — Integrado ao VOXEL PACS

---

## Visão Geral

O VOXEL Copilot é um módulo de laudo avançado com IA que se integra ao VOXEL PACS e a outros sistemas PACS. O sistema aprende o perfil de cada médico, adapta o vocabulário e o estilo de redação, e oferece assistência inteligente durante a elaboração de laudos radiológicos.

## Arquitetura

```
voxelcopilot/
├── app/
│   ├── bootstrap.php          # Inicialização da aplicação
│   ├── autoload.php           # Autoloader customizado (sem Composer)
│   ├── Controllers/           # Controllers da aplicação
│   │   └── Platform/          # Controllers do painel Super Admin
│   ├── Core/                  # Núcleo do framework
│   │   ├── Auth.php           # Autenticação e sessão
│   │   ├── Controller.php     # Classe base dos controllers
│   │   ├── Database.php       # Conexão PDO com MySQL/MariaDB
│   │   ├── Logger.php         # Sistema de logs
│   │   ├── Router.php         # Roteador HTTP
│   │   ├── View.php           # Renderização de views
│   │   └── Audit/             # Sistema de auditoria
│   ├── Middlewares/           # Middlewares de autenticação e tenant
│   ├── Models/                # Modelos de dados
│   ├── Services/              # Serviços (e-mail, IA, PACS)
│   └── Views/                 # Templates PHP
│       ├── auth/              # Login, cadastro
│       ├── dashboard/         # Dashboard do médico
│       ├── layout/            # Layouts (auth, copilot)
│       ├── platform/          # Painel Super Admin
│       └── workspace/         # Workspace de laudos
├── database/
│   ├── migrations/            # Scripts SQL de criação
│   └── seeds/                 # Dados iniciais
├── public/
│   ├── index.php              # Front Controller
│   ├── .htaccess              # Roteamento Apache
│   └── assets/                # CSS, JS, imagens
├── routes/
│   └── web.php                # Definição de rotas
└── storage/
    ├── logs/                  # Logs da aplicação
    ├── sessions/              # Sessões PHP
    └── uploads/               # Uploads de arquivos
```

## Requisitos

- PHP 7.4+ (compatível com HostGator/cPanel)
- MySQL 5.7 / MariaDB 10.3+
- Apache com `mod_rewrite` habilitado
- Extensões PHP: `pdo_mysql`, `mbstring`, `openssl`

## Instalação

### 1. Configurar o ambiente

```bash
cp .env.example .env
# Edite o .env com suas credenciais de banco e SMTP
```

### 2. Criar o banco de dados

Execute o script de migration no seu MySQL/phpMyAdmin:

```sql
-- Execute o arquivo:
database/migrations/2026-07-05_copilot_schema.sql
```

### 3. Configurar o servidor web

Aponte o DocumentRoot do Apache para a pasta `public/`:

```apache
DocumentRoot /caminho/para/voxelcopilot/public
```

Ou em hospedagem compartilhada, faça upload de todos os arquivos e configure o `.htaccess` raiz.

## Credenciais Padrão

| Usuário | E-mail | Senha |
|---------|--------|-------|
| Super Admin | admin@voxelpacs.com.br | Admin259087@ |

> **Importante:** Altere a senha do Super Admin após o primeiro acesso.

## Módulos

| Módulo | Status | Descrição |
|--------|--------|-----------|
| Autenticação Multitenant | ✅ Implementado | Login, cadastro, impersonação |
| Super Admin | ✅ Implementado | Dashboard, médicos, planos |
| Dashboard Médico | ✅ Implementado | Visão geral, laudos recentes |
| Workspace de Laudos | 🔄 Em desenvolvimento | Editor com IA |
| Templates | 🔄 Em desenvolvimento | Máscaras de laudo |
| Autotextos | 🔄 Em desenvolvimento | Frases rápidas |
| Integração PACS | 🔄 Em desenvolvimento | API VOXEL PACS |
| IA Copilot | 🔄 Em desenvolvimento | GPT-4o assistente |
| Speech-to-Text | 📋 Planejado | Ditado de laudos |
| Vision AI | 📋 Planejado | Análise de imagens |

## Segurança

- Senhas com bcrypt (custo 12)
- Proteção CSRF em todos os formulários
- Sessões seguras com HttpOnly e SameSite
- Headers de segurança (X-Frame-Options, XSS-Protection)
- Auditoria completa de ações

## Licença

Proprietário — VOXEL Copilot © 2026
