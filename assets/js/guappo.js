/* Guappo · camada de interação.
   Só ADICIONA comportamento. Não altera formulários, nomes de campos, rotas nem dados enviados. */
(() => {
    'use strict';

    const page = document.body.dataset.page || '';
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const norm = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const svg = p => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${p}</svg>`;

    const ICONS = {
        agendamentos: svg('<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17M8 14.5h3"/>'),
        clientes: svg('<circle cx="9" cy="8.5" r="3.3"/><path d="M3 19.5c.6-3.3 3-5 6-5s5.4 1.7 6 5M16 5.6a3.2 3.2 0 0 1 0 5.8M18 14.8c1.7.6 2.8 2.2 3.2 4.7"/>'),
        preferencias: svg('<circle cx="6" cy="6.5" r="2.6"/><circle cx="6" cy="17.5" r="2.6"/><path d="M8 8.2 20 17M8 15.8 20 7"/>'),
        relatorios: svg('<path d="M4 20V10M10 20V4M16 20v-7M21 20H3"/>'),
        search: svg('<circle cx="11" cy="11" r="6.5"/><path d="m20 20-3.8-3.8"/>'),
        eye: svg('<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/>')
    };

    /* ---------- Navegação fixa (páginas internas) ---------- */
    function buildNav() {
        const links = [['dashboard', 'Painel'], ['agendamentos', 'Agendamentos'], ['clientes', 'Clientes'], ['preferencias', 'Preferências']];
        const nav = document.createElement('header');
        nav.className = 'g-nav';
        nav.innerHTML =
            '<div class="g-nav__in">' +
            '<a class="g-brand" href="dashboard.php"><img src="assets/img/logo.svg" alt="" width="34" height="34"><span>Guappo Barbearia</span></a>' +
            '<nav aria-label="Principal">' +
            links.map(([k, t]) => `<a href="${k}.php"${k === page ? ' aria-current="page"' : ''}>${t}</a>`).join('') +
            '</nav><a class="g-nav__exit" href="logout.php">Sair</a></div>';
        document.body.prepend(nav);
    }

    /* ---------- Hero com data do dia ---------- */
    const TAGS = {
        dashboard: 'Tudo o que a barbearia precisa hoje.',
        agendamentos: 'Agenda organizada, sem choque de horários.',
        clientes: 'Quem passa pela cadeira, em um só lugar.',
        preferencias: 'O estilo de cada cliente, sempre à mão.'
    };
    function buildHero() {
        const box = $('.container');
        if (!box) return;
        const hero = document.createElement('section');
        hero.className = 'g-hero';
        const inner = document.createElement('div');
        inner.className = 'g-hero__in';
        const date = document.createElement('p');
        date.className = 'g-hero__date';
        date.textContent = new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' });
        const tag = document.createElement('p');
        tag.className = 'g-hero__tag';
        tag.textContent = TAGS[page] || '';
        inner.append(date, tag);
        hero.append(inner);
        box.before(hero);
    }

    /* ---------- Dashboard: contadores e ícones ---------- */
    function countUp() {
        if (reduce) return;
        $$('.stat-card h3').forEach(el => {
            const txt = el.textContent.trim();
            const m = txt.match(/^(R\$\s*)?(\d[\d.]*(?:,\d+)?)$/);
            if (!m) return;
            const money = !!m[1];
            const target = parseFloat(m[2].replace(/\./g, '').replace(',', '.'));
            const dec = money ? 2 : 0;
            const fmt = v => (money ? 'R$ ' : '') + v.toLocaleString('pt-BR', { minimumFractionDigits: dec, maximumFractionDigits: dec });
            const t0 = performance.now();
            const tick = now => {
                const p = Math.min((now - t0) / 900, 1);
                el.textContent = p < 1 ? fmt(target * (1 - Math.pow(1 - p, 3))) : txt;
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });
    }
    function cardIcons() {
        $$('.menu-card').forEach(card => {
            const key = card.tagName === 'A' ? (card.getAttribute('href') || '').replace('.php', '') : 'relatorios';
            if (!ICONS[key]) return;
            const i = document.createElement('span');
            i.className = 'g-icon';
            i.innerHTML = ICONS[key];
            card.prepend(i);
        });
    }

    /* ---------- Tabelas: busca, filtro por estado, avatares ---------- */
    function enhanceTables() {
        $$('.table-container').forEach(wrap => {
            const table = $('table', wrap);
            if (!table) return;
            const rows = $$('tr', table).filter(r => $('td', r));

            if (!rows.length) {
                const empty = document.createElement('p');
                empty.className = 'g-empty';
                empty.textContent = 'Ainda não há registros. Use o formulário acima para adicionar o primeiro.';
                wrap.append(empty);
                return;
            }

            rows.forEach(r => {
                const s = $('td:first-child strong', r);
                if (s) {
                    s.classList.add('g-person');
                    s.dataset.i = s.textContent.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
                }
                const b = $('.badge-estado', r);
                if (b) b.dataset.estado = b.textContent.trim();
            });

            const bar = document.createElement('div');
            bar.className = 'g-toolbar';
            const search = document.createElement('div');
            search.className = 'g-search';
            search.innerHTML = ICONS.search;
            const input = document.createElement('input');
            input.type = 'search';
            input.placeholder = 'Buscar na lista';
            input.autocomplete = 'off';
            input.setAttribute('aria-label', 'Buscar na lista');
            search.append(input);
            const count = document.createElement('span');
            count.className = 'g-count';
            bar.append(search);

            let estado = '';
            const states = [...new Set(rows.map(r => ($('.badge-estado', r) || {}).textContent).filter(Boolean).map(s => s.trim()))];
            if (page === 'agendamentos' && states.length > 1) {
                const chips = document.createElement('div');
                chips.className = 'g-chips';
                ['Todos', ...states].forEach((label, idx) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = label;
                    b.setAttribute('aria-pressed', idx === 0 ? 'true' : 'false');
                    b.addEventListener('click', () => {
                        estado = idx === 0 ? '' : label;
                        $$('button', chips).forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
                        apply();
                    });
                    chips.append(b);
                });
                bar.append(chips);
            }
            bar.append(count);

            const none = document.createElement('p');
            none.className = 'g-empty';
            none.hidden = true;
            none.textContent = 'Nenhum resultado para esse filtro.';
            wrap.append(none);

            function apply() {
                const q = norm(input.value.trim());
                let n = 0;
                rows.forEach(r => {
                    const st = ($('.badge-estado', r) || {}).textContent;
                    const ok = (!q || norm(r.textContent).includes(q)) && (!estado || (st && st.trim() === estado));
                    r.hidden = !ok;
                    if (ok) n++;
                });
                count.textContent = `${n} de ${rows.length}`;
                none.hidden = n > 0;
            }
            input.addEventListener('input', apply);
            apply();
            wrap.before(bar);
        });
    }

    /* ---------- Agendamentos: confirmação antes de concluir + atalhos de data ---------- */
    function confirmDialog(nome) {
        return new Promise(resolve => {
            const d = document.createElement('dialog');
            d.className = 'g-dialog';
            d.innerHTML = '<form method="dialog"><h4>Marcar como concluído?</h4><p></p>' +
                '<div><button value="no" class="g-btn-ghost">Cancelar</button><button value="yes" class="g-btn-gold">Marcar concluído</button></div></form>';
            const p = $('p', d);
            if (nome) {
                const s = document.createElement('strong');
                s.textContent = nome;
                p.append('O atendimento de ', s, ' entra no faturamento.');
            } else {
                p.textContent = 'O atendimento entra no faturamento.';
            }
            document.body.append(d);
            d.addEventListener('close', () => { resolve(d.returnValue === 'yes'); d.remove(); });
            d.showModal();
        });
    }
    function agendamentosUX() {
        document.addEventListener('click', async e => {
            const a = e.target.closest('a.btn-concluir');
            if (!a || a.dataset.ok || typeof HTMLDialogElement === 'undefined') return;
            e.preventDefault();
            const nome = a.closest('tr') && a.closest('tr').children[0] ? a.closest('tr').children[0].textContent.trim() : '';
            if (await confirmDialog(nome)) { a.dataset.ok = '1'; location.href = a.href; }
        });

        const d = $('input[name="data_agendamento"]');
        if (!d) return;
        const row = document.createElement('div');
        row.className = 'g-chips g-chips--date';
        [['Hoje', 0], ['Amanhã', 1], ['Em 7 dias', 7]].forEach(([t, n]) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = t;
            b.addEventListener('click', () => {
                const x = new Date();
                x.setDate(x.getDate() + n);
                d.value = [x.getFullYear(), String(x.getMonth() + 1).padStart(2, '0'), String(x.getDate()).padStart(2, '0')].join('-');
                d.dispatchEvent(new Event('change', { bubbles: true }));
            });
            row.append(b);
        });
        d.after(row);
    }

    /* ---------- Preferências: sugestões (campos continuam livres) ---------- */
    function preferenciasUX() {
        const SUG = {
            estilo_corte: ['Low Fade', 'Mid Fade', 'High Fade', 'Corte Social', 'Undercut', 'Pompadour', 'Degradê na Navalha'],
            acabamento_barba: ['Desenhada a Navalha', 'Barba Cheia', 'Aparada na Máquina', 'Barba Baixa'],
            tratamento_facial: ['Toalha Quente', 'Limpeza de Pele', 'Hidratação', 'Esfoliação'],
            produto_fixacao: ['Pomada Efeito Mate', 'Pomada com Brilho', 'Cera', 'Gel', 'Pó Modelador']
        };
        Object.entries(SUG).forEach(([name, items]) => {
            const field = $(`input[name="${name}"]`);
            if (!field) return;
            const dl = document.createElement('datalist');
            dl.id = 'sug_' + name;
            items.forEach(v => { const o = document.createElement('option'); o.value = v; dl.append(o); });
            field.setAttribute('list', dl.id);
            field.after(dl);
        });
    }

    /* ---------- Login ---------- */
    function loginUX() {
        const area = $('.logo-area');
        if (area) {
            const img = new Image();
            img.src = 'assets/img/logo.svg';
            img.alt = '';
            img.width = img.height = 64;
            img.className = 'g-logo';
            area.prepend(img);
        }
        const pw = $('input[type="password"]');
        if (pw && pw.parentElement) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'g-eye';
            b.setAttribute('aria-pressed', 'false');
            b.setAttribute('aria-label', 'Mostrar palavra-passe');
            b.innerHTML = ICONS.eye;
            b.addEventListener('click', () => {
                const show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                b.setAttribute('aria-pressed', String(show));
                b.setAttribute('aria-label', show ? 'Ocultar palavra-passe' : 'Mostrar palavra-passe');
            });
            pw.parentElement.append(b);
        }
        const mail = $('input[type="email"]');
        const hint = $('.hint');
        if (hint && mail) {
            const list = hint.textContent.match(/[\w.+-]+@[\w-]+\.[\w.-]+/g);
            if (list) {
                hint.textContent = 'Equipa autorizada: escolha para preencher o e-mail';
                const w = document.createElement('div');
                w.className = 'g-chips';
                list.forEach(m => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.textContent = m.split('@')[0];
                    b.title = m;
                    b.addEventListener('click', () => { mail.value = m; if (pw) pw.focus(); });
                    w.append(b);
                });
                hint.append(w);
            }
        }
        if (mail) mail.focus();
    }

    /* ---------- Comuns ---------- */
    function common() {
        $$('.msg').forEach(m => {
            m.setAttribute('role', 'status');
            setTimeout(() => { m.classList.add('is-out'); setTimeout(() => m.remove(), 400); }, 6000);
        });
        $$('.erro, .erro-box').forEach(m => m.setAttribute('role', 'alert'));

        $$('form').forEach(f => f.addEventListener('submit', e => {
            if (e.defaultPrevented) return;
            const b = $('button[type="submit"]', f);
            if (!b) return;
            setTimeout(() => { b.disabled = true; b.classList.add('is-loading'); }, 0);
        }));
        addEventListener('pageshow', () => $$('button.is-loading').forEach(b => { b.disabled = false; b.classList.remove('is-loading'); }));
    }

    /* ---------- Início ---------- */
    if (page === 'login') {
        loginUX();
    } else {
        buildNav();
        buildHero();
        enhanceTables();
        if (page === 'dashboard') { cardIcons(); countUp(); }
        if (page === 'agendamentos') agendamentosUX();
        if (page === 'preferencias') preferenciasUX();
    }
    common();
})();
