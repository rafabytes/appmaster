const toggle = () => document.querySelector('.nav-toggle');

export function closeNav() {
  document.documentElement.classList.remove('nav-open');
  toggle()?.setAttribute('aria-expanded', 'false');
}

export function initNav() {
  const button = toggle();
  if (!button) return;

  button.addEventListener('click', () => {
    const open = document.documentElement.classList.toggle('nav-open');
    button.setAttribute('aria-expanded', String(open));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && document.documentElement.classList.contains('nav-open')) {
      closeNav();
      button.focus();
    }
  });

  window.matchMedia('(min-width: 768px)').addEventListener('change', closeNav);
}
