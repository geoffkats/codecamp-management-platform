import { normalizeScratch } from './scratch-detect';

let scratchblocksPromise = null;
let highlighterPromise = null;

function loadScratchblocks() {
    scratchblocksPromise ??= import('scratchblocks').then(({ default: scratchblocks }) => {
        if (!document.getElementById('scratchblocks-styles')) {
            scratchblocks.appendStyles();
            const marker = document.createElement('meta');
            marker.id = 'scratchblocks-styles';
            document.head.appendChild(marker);
        }
        return scratchblocks;
    });

    return scratchblocksPromise;
}

function loadHighlighter() {
    highlighterPromise ??= Promise.all([
        import('highlight.js/lib/core'),
        import('highlight.js/lib/languages/python'),
        import('highlight.js/lib/languages/javascript'),
        import('highlight.js/lib/languages/xml'),
        import('highlight.js/lib/languages/css'),
        import('highlight.js/lib/languages/sql'),
        import('highlight.js/lib/languages/java'),
        import('highlight.js/lib/languages/cpp'),
    ]).then(([core, python, javascript, xml, css, sql, java, cpp]) => {
        const hljs = core.default;
        hljs.registerLanguage('python', python.default);
        hljs.registerLanguage('javascript', javascript.default);
        hljs.registerLanguage('html', xml.default);
        hljs.registerLanguage('css', css.default);
        hljs.registerLanguage('sql', sql.default);
        hljs.registerLanguage('java', java.default);
        hljs.registerLanguage('cpp', cpp.default);
        return hljs;
    });

    return highlighterPromise;
}

/**
 * Turns a <div data-scratch> holding scratchblocks syntax into real Scratch 3 blocks.
 * The plain-text source stays in the DOM (hidden) so it still reads if rendering fails.
 */
export async function renderScratch(el) {
    if (!el || el.dataset.rendered === '1') return;

    const source = el.querySelector('[data-source]');
    const target = el.querySelector('[data-target]');
    if (!source || !target) return;

    try {
        const svg = await renderScratchSvg(source.textContent, Number(el.dataset.scale || 0.7));
        target.replaceChildren(svg);
        source.classList.add('hidden');
        el.dataset.rendered = '1';
    } catch (error) {
        console.warn('Could not render Scratch blocks', error);
    }
}

export async function renderScratchSvg(text, scale = 0.7) {
    const scratchblocks = await loadScratchblocks();
    const doc = scratchblocks.parse(normalizeScratch(text), { languages: ['en'] });

    return scratchblocks.render(doc, { style: 'scratch3', scale });
}

export async function highlightCode(el) {
    if (!el || el.dataset.highlighted === 'yes') return;

    try {
        const hljs = await loadHighlighter();
        const language = el.dataset.language;
        const result = language && hljs.getLanguage(language)
            ? hljs.highlight(el.textContent, { language })
            : hljs.highlightAuto(el.textContent, ['python', 'javascript', 'html', 'css', 'sql', 'java', 'cpp']);
        el.innerHTML = result.value;
        el.dataset.highlighted = 'yes';
    } catch (error) {
        console.warn('Could not highlight code', error);
    }
}
