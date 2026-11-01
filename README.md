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

## SEO

Implementado em `inc/seo.php` (desativado automaticamente se Yoast, Rank Math, AIOSEO ou SEOPress estiverem ativos):

- `<title>` e meta description por página (caixa **SEO** na lateral da edição de páginas), com fallback para o resumo e para a descrição padrão em **appMaster → SEO**
- Open Graph e Twitter Card com imagem 1200×630 (por página → padrão das opções → `assets/img/og-default.png`)
- JSON-LD: `Organization`, `WebSite` e `WebPage`/`AboutPage`/`CollectionPage` com `ItemList` dos projetos (`WebApplication` + repositório)
- `noindex` em 404, busca, arquivos e páginas marcadas como "Ocultar dos buscadores"
- Sitemap nativo (`/wp-sitemap.xml`) sem usuários, sem posts/taxonomias enquanto não houver blog e sem páginas ocultas
- Arquivos de autor e páginas de anexo redirecionados (301)
- Favicon: **Aparência → Personalizar → Identidade do site → Ícone do site**; se vazio, usa `assets/img/icon-*.png`

## Antispam

O site não recebe conteúdo de visitantes (`inc/antispam.php`):

- Comentários, pingbacks e trackbacks desativados em todos os tipos de post (inclusive conteúdos antigos)
- `wp-comments-post.php` responde 403; endpoint REST de comentários removido; feeds de comentários redirecionados
- `xmlrpc.php` responde 403 (bloqueia pingback e força bruta via `system.multicall`) e o cabeçalho `X-Pingback` é removido
- Cadastro de usuários desativado; `/wp-json/wp/v2/users` só para usuários logados; oEmbed sem nome do autor
- Busca desativada (`?s=` redireciona para a home)
- Menus, telas e widgets de comentários removidos do painel

## LiteSpeed Cache

Compatível com a predefinição **Avançado** (`inc/litespeed.php`):

- O JS do tema (`dist/`) fica fora de minificar/combinar/adiar do LiteSpeed. Ele já sai otimizado do Parcel e carrega o React sob demanda por caminho relativo; combinar esse arquivo quebraria o carregamento.
- Salvar as opções do appMaster, um projeto, um menu, o ícone ou o título do site limpa todo o cache.
- O CSS pode ser minificado/combinado normalmente.

## Deploy

### Tema (Git do cPanel)

O `.cpanel.yml` copia o tema compilado para `appmaster.com.br/wp-content/themes/appmaster-theme/` (sem `src/`, `node_modules/` e configs) e limpa o cache do LiteSpeed se o WP-CLI existir no servidor.

1. Local: `npm run build`, commit (incluindo `dist/`) e `git push`
2. cPanel → **Git Version Control** → appmaster → **Pull or Deploy** → **Update from Remote** → **Deploy HEAD Commit**

### Banco de dados (primeira vez)

1. Migrar o site (ex.: All-in-One WP Migration). A troca de domínio substitui as URLs gravadas nos campos de link.
2. Ativar **ACF PRO** e **Classic Editor** e o tema **appMaster**.
3. Conferir **Configurações → Links permanentes** em `/%postname%/`.
4. Conferir que **Configurações → Leitura → Visibilidade nos mecanismos de busca** está desmarcado.
5. Enviar `https://appmaster.com.br/wp-sitemap.xml` no Google Search Console.

O site é institucional: o front-end não exibe nenhum link de login (acesso apenas por `/wp-admin`).
