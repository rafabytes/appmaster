import { useEffect, useRef, useState } from 'react';

/**
 * Terminal interativo da home: atalho para navegar pelos projetos via comandos.
 * Todo o conteúdo também está disponível em HTML na página (o terminal é um extra).
 */

const COMMANDS = ['help', 'ls', 'info', 'open', 'github', 'cd', 'clear'];

const normalize = (value = '') =>
  value
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]/g, '');

const host = (url = '') => {
  try {
    return new URL(url).host.replace(/^www\./, '');
  } catch {
    return url;
  }
};

function navigate(url) {
  if (window.swup) window.swup.navigate(url);
  else window.location.href = url;
}

export default function Terminal({ user = 'visitante', host: machine = 'appmaster', intro = '', github = '', pages = {}, projects = [] }) {
  const [lines, setLines] = useState(() => (intro ? [{ kind: 'dim', text: intro }] : []));
  const [value, setValue] = useState('');
  const [history, setHistory] = useState([]);
  const [cursor, setCursor] = useState(-1);
  const inputRef = useRef(null);
  const outputRef = useRef(null);

  const prompt = `${user}@${machine}:~$`;
  const names = projects.map((p) => p.slug);

  useEffect(() => {
    const el = outputRef.current;
    if (el) el.scrollTop = el.scrollHeight;
  }, [lines]);

  const findProject = (arg) => {
    const key = normalize(arg);
    if (!key) return null;
    const exact = projects.find((p) => normalize(p.slug) === key || normalize(p.name) === key);
    if (exact) return exact;
    const partial = projects.filter((p) => normalize(p.slug).startsWith(key) || normalize(p.name).startsWith(key));
    return partial.length === 1 ? partial[0] : null;
  };

  const openUrl = (url, label) => {
    // Com "noopener" o retorno é sempre null; o link fica na tela caso o pop-up seja bloqueado.
    window.open(url, '_blank', 'noopener');
    return [{ kind: 'out', text: 'abrindo ', href: url, label: label || host(url) }];
  };

  const run = (raw) => {
    const [cmd = '', ...rest] = raw.trim().split(/\s+/);
    const arg = rest.join(' ');
    const needsProject = () => [{ kind: 'err', text: `uso: ${cmd} <projeto>  (projetos: ${names.join(', ')})` }];
    const notFound = () => [{ kind: 'err', text: `${cmd}: ${arg}: projeto não encontrado. digite "ls".` }];

    switch (cmd.toLowerCase()) {
      case '':
        return [];
      case 'help':
        return [
          'comandos disponíveis:',
          '  ls                  lista os projetos',
          '  info <projeto>      mostra o resumo do projeto',
          '  open <projeto>      abre o projeto em uma nova aba',
          '  github [projeto]    abre o repositório no GitHub',
          `  cd <página>         navega pelo site (${Object.keys(pages).join(', ')})`,
          '  clear               limpa a tela',
        ].map((text) => ({ kind: 'out', text }));
      case 'ls':
        return projects.map((p) => ({ kind: 'out', text: `${p.slug.padEnd(16)} `, href: p.url, label: host(p.url) }));
      case 'info': {
        if (!arg) return needsProject();
        const p = findProject(arg);
        if (!p) return notFound();
        const out = [
          { kind: 'title', text: p.name },
          { kind: 'out', text: p.summary },
          { kind: 'out', text: 'url:    ', href: p.url, label: host(p.url) },
        ];
        if (p.github) out.push({ kind: 'out', text: 'github: ', href: p.github, label: p.github.replace(/^https?:\/\/(www\.)?/, '') });
        return out;
      }
      case 'open': {
        if (!arg) return needsProject();
        const p = findProject(arg);
        return p ? openUrl(p.url) : notFound();
      }
      case 'github': {
        if (!arg) return github ? openUrl(github) : [{ kind: 'err', text: 'github: nenhum perfil configurado.' }];
        const p = findProject(arg);
        if (!p) return notFound();
        return p.github ? openUrl(p.github) : [{ kind: 'err', text: `github: ${p.name} não tem repositório público.` }];
      }
      case 'cd': {
        const target = normalize(arg.replace(/^[~./]+/, '')) || 'home';
        const url = pages[target];
        if (!url) return [{ kind: 'err', text: `cd: ${arg}: página não encontrada. opções: ${Object.keys(pages).join(', ')}` }];
        navigate(url);
        return [{ kind: 'dim', text: `indo para ${target}...` }];
      }
      case 'clear':
        return null;
      default:
        return [{ kind: 'err', text: `${cmd}: comando não encontrado. digite "help".` }];
    }
  };

  const submit = (event) => {
    event.preventDefault();
    const raw = value;
    const output = run(raw);
    setValue('');
    setCursor(-1);
    if (raw.trim()) setHistory((h) => [raw, ...h].slice(0, 50));
    if (output === null) {
      setLines([]);
      return;
    }
    setLines((prev) => [...prev, { kind: 'cmd', text: raw }, ...output].slice(-200));
  };

  const complete = () => {
    const parts = value.split(/\s+/);
    if (parts.length === 1) {
      const matches = COMMANDS.filter((c) => c.startsWith(parts[0].toLowerCase()));
      if (matches.length === 1) setValue(`${matches[0]} `);
      return;
    }
    const pool = parts[0] === 'cd' ? Object.keys(pages) : names;
    const last = parts[parts.length - 1].toLowerCase();
    const matches = pool.filter((n) => n.startsWith(last));
    if (matches.length === 1) setValue([...parts.slice(0, -1), matches[0]].join(' '));
  };

  const onKeyDown = (event) => {
    if (event.key === 'Tab' && value.trim()) {
      event.preventDefault();
      complete();
    } else if (event.key === 'ArrowUp' && history.length) {
      event.preventDefault();
      const next = Math.min(cursor + 1, history.length - 1);
      setCursor(next);
      setValue(history[next]);
    } else if (event.key === 'ArrowDown' && cursor > -1) {
      event.preventDefault();
      const next = cursor - 1;
      setCursor(next);
      setValue(next === -1 ? '' : history[next]);
    }
  };

  return (
    <div onClick={() => window.getSelection()?.isCollapsed && inputRef.current?.focus()}>
      <div ref={outputRef} className="mini-terminal__output" aria-live="polite">
        {lines.map((line, i) => (
          <p key={i} className={`mini-terminal__line ${lineClass[line.kind] || ''}`}>
            {line.kind === 'cmd' && <span className="text-term-green">{prompt} </span>}
            {line.text}
            {line.href && (
              <a href={line.href} target="_blank" rel="noopener">
                {line.label}
              </a>
            )}
          </p>
        ))}
      </div>
      <form className="mini-terminal__input mt-2" onSubmit={submit}>
        <label htmlFor="am-terminal-input" className="shrink-0 text-term-green">
          {prompt}
          <span className="sr-only"> digite um comando</span>
        </label>
        <input
          id="am-terminal-input"
          ref={inputRef}
          value={value}
          onChange={(e) => setValue(e.target.value)}
          onKeyDown={onKeyDown}
          autoComplete="off"
          autoCapitalize="off"
          autoCorrect="off"
          spellCheck="false"
          enterKeyHint="send"
          placeholder="help"
        />
      </form>
    </div>
  );
}

const lineClass = {
  cmd: 'text-term-bright',
  dim: 'text-term-dim',
  err: 'text-term-red',
  title: 'font-bold text-term-bright',
  out: 'text-term-text',
};
