# appMaster — tema WordPress

Tema institucional do appMaster (laboratório de IA sem fins lucrativos que publica soluções de código livre).
Stack: WordPress + Parcel + TailwindCSS + Swup + React.

## Requisitos

- WordPress 6.4+ / PHP 8.0+
- Plugin **Advanced Custom Fields PRO** (campos editáveis)
- Recomendado: **Classic Editor**
- Node 18+ (apenas para desenvolvimento)

## Desenvolvimento

```bash
npm install
npm run dev     # parcel watch -> dist/
npm run build   # build de produção -> dist/
```

Os arquivos compilados ficam em `dist/` e **devem ir para o servidor** (node_modules não).

## Estrutura

| Caminho | O quê |
| --- | --- |
| `inc/acf.php` | Página de opções "appMaster" e grupos de campos (registrados em código) |
| `inc/cpt.php` | Post type **Projetos** (sem página própria, o card leva ao app e ao GitHub) |
| `inc/login.php` | Tela de login personalizada |
| `inc/seed.php` | Conteúdo inicial (roda uma vez, quando o ACF está ativo) |
| `page-templates/` | Templates **Sobre o projeto** e **Portfolio** |
| `src/js/components/Terminal.jsx` | Terminal interativo da home (React, carregado sob demanda) |
| `src/css/` | Tailwind (`main.css`) e login (`login.css`) |

## Onde editar no painel

- **appMaster** (menu lateral): nome, usuário do prompt, GitHub, e-mail, rodapé, tela de login, SEO e 404.
- **Páginas → Home**: destaque, selos, botões, terminal interativo, blocos de projetos e sobre.
- **Páginas → Sobre o projeto / Portfolio**: introdução, texto, lista de destaques e botão.
- **Projetos**: nome, endereço, GitHub, resumo, tags, status e ícone. A ordem é definida pelo campo "Ordem".
- **Aparência → Menus**: menu principal e links do rodapé.

## Deploy (appmaster.local → appmaster.com.br)

1. `npm run build`
2. Migrar o site (ex.: All-in-One WP Migration). A troca de domínio substitui as URLs gravadas nos campos de link.
3. Conferir **Configurações → Links permanentes** em `/%postname%/`.

O site é institucional: o front-end não exibe nenhum link de login (acesso apenas por `/wp-admin`).
