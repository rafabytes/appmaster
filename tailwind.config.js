/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './inc/**/*.php',
    './page-templates/**/*.php',
    './template-parts/**/*.php',
    './src/js/**/*.{js,jsx}',
  ],
  // Classes montadas dinamicamente no PHP (status--<?php echo $status ?>).
  safelist: ['status--online', 'status--beta', 'status--dev', 'status--offline'],
  theme: {
    extend: {
      colors: {
        term: {
          bg: '#0a0c0b',
          panel: '#0f1311',
          line: '#1f2a24',
          dim: '#6b7a71',
          text: '#c9d4cc',
          bright: '#eef5f0',
          green: '#5ff08d',
          amber: '#ffb347',
          red: '#ff6b6b',
        },
      },
      fontFamily: {
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'monospace'],
      },
      maxWidth: {
        term: '68rem',
      },
    },
  },
  corePlugins: {
    container: false, // .container é definido em src/css/main.css
  },
  plugins: [],
};
