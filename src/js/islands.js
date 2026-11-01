/**
 * Componentes React montados em elementos [data-react="nome"].
 * As props vêm de um <script type="application/json"> dentro do elemento.
 * React e o componente só são baixados quando a página contém a ilha.
 */
const components = {
  terminal: () => import('./components/Terminal'),
};

const roots = new Map();

export async function mountIslands(scope = document) {
  const elements = [...scope.querySelectorAll('[data-react]')].filter((el) => components[el.dataset.react] && !roots.has(el));
  if (!elements.length) return;

  const [{ createElement }, { createRoot }] = await Promise.all([import('react'), import('react-dom/client')]);

  for (const el of elements) {
    if (roots.has(el) || !el.isConnected) continue;

    let props = {};
    const json = el.querySelector('script[type="application/json"]');
    if (json) {
      try {
        props = JSON.parse(json.textContent);
      } catch (error) {
        console.error('[appmaster] props inválidas para', el.dataset.react, error);
        continue;
      }
    }

    const { default: Component } = await components[el.dataset.react]();
    if (!el.isConnected) continue;

    const root = createRoot(el);
    root.render(createElement(Component, props));
    roots.set(el, root);
  }
}

export function unmountIslands() {
  roots.forEach((root) => root.unmount());
  roots.clear();
}
