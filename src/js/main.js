import '@fontsource/jetbrains-mono/latin-400.css';
import '@fontsource/jetbrains-mono/latin-700.css';
import '../css/main.css';

import Swup from 'swup';
import SwupHeadPlugin from '@swup/head-plugin';
import SwupBodyClassPlugin from '@swup/body-class-plugin';
import SwupPreloadPlugin from '@swup/preload-plugin';

import { initNav, closeNav } from './nav';
import { mountIslands, unmountIslands } from './islands';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const swup = new Swup({
  containers: ['#swup', '#site-nav'],
  animateHistoryBrowsing: !reduceMotion,
  native: false,
  ignoreVisit: (url, { el } = {}) =>
    Boolean(el?.closest('[data-no-swup]')) ||
    el?.target === '_blank' ||
    /\/(wp-admin|wp-login\.php|wp-content|feed)\b/.test(url),
  plugins: [
    new SwupHeadPlugin({ persistAssets: true, awaitAssets: true }),
    new SwupBodyClassPlugin(),
    new SwupPreloadPlugin({ preloadHoveredLinks: true }),
  ],
});

window.swup = swup;

// Desmonta componentes React antes de trocar o conteúdo e remonta depois.
swup.hooks.before('content:replace', () => unmountIslands());

swup.hooks.on('content:replace', (visit) => {
  // Mantém a admin bar (usuário logado) sincronizada com a página atual.
  const current = document.getElementById('wpadminbar');
  const next = visit.to.document?.getElementById('wpadminbar');
  if (current && next) current.replaceWith(next);
});

swup.hooks.on('visit:start', () => closeNav());

swup.hooks.on('page:view', () => {
  mountIslands();
  // Leva o foco ao conteúdo novo (leitores de tela / teclado).
  document.getElementById('conteudo')?.focus({ preventScroll: true });
});

initNav();
mountIslands();
