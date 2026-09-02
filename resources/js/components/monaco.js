const MONACO_VERSION = '0.52.2'
const MONACO_VS = `https://cdn.jsdelivr.net/npm/monaco-editor@${MONACO_VERSION}/min/vs`

let monacoPromise = null

export function monacoTheme() {
    return document.documentElement.classList.contains('dark') ? 'vs-dark' : 'vs'
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[data-monaco-loader="true"]`)
        if (existing) {
            if (existing.dataset.loaded === 'true' || window.require) {
                resolve()
                return
            }
            existing.addEventListener('load', () => resolve(), { once: true })
            existing.addEventListener('error', () => reject(new Error('Monaco loader failed')), { once: true })
            return
        }

        const script = document.createElement('script')
        script.src = src
        script.async = true
        script.dataset.monacoLoader = 'true'
        script.addEventListener('load', () => {
            script.dataset.loaded = 'true'
            resolve()
        }, { once: true })
        script.addEventListener('error', () => reject(new Error('Monaco loader failed')), { once: true })
        document.head.appendChild(script)
    })
}

export async function loadMonaco() {
    if (window.monaco) {
        return window.monaco
    }

    if (monacoPromise) {
        return monacoPromise
    }

    monacoPromise = (async () => {
        await loadScript(`${MONACO_VS}/loader.js`)

        const amdRequire = window.require
        if (typeof amdRequire !== 'function' || typeof amdRequire.config !== 'function') {
            throw new Error('Monaco AMD loader is unavailable.')
        }

        amdRequire.config({ paths: { vs: MONACO_VS } })

        return new Promise((resolve, reject) => {
            amdRequire(['vs/editor/editor.main'], () => {
                resolve(window.monaco)
            }, reject)
        })
    })()

    return monacoPromise
}

export async function createMonacoEditor(container, options = {}) {
    const monaco = await loadMonaco()

    const editor = monaco.editor.create(container, {
        automaticLayout: true,
        minimap: { enabled: false },
        fontSize: 14,
        fontLigatures: true,
        fontFamily: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace',
        scrollBeyondLastLine: false,
        wordWrap: 'on',
        tabSize: options.tabSize ?? 2,
        theme: monacoTheme(),
        padding: { top: 12, bottom: 12 },
        ...options,
    })

    return { monaco, editor }
}
