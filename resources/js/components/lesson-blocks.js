import { Extension, Node } from '@tiptap/core'

// Keep in sync with app/Support/LessonBlocks.php.
export const BOXES = {
    key: { label: 'Key idea', background: '#eff6ff', accent: '#2563eb', text: '#1e3a8a' },
    tip: { label: 'Tip', background: '#ecfdf5', accent: '#059669', text: '#064e3b' },
    warn: { label: 'Watch out', background: '#fffbeb', accent: '#d97706', text: '#78350f' },
    try: { label: 'Try it', background: '#f5f3ff', accent: '#7c3aed', text: '#4c1d95' },
    teacher: { label: 'Teacher note', background: '#f8fafc', accent: '#475569', text: '#1e293b' },
    think: { label: 'Think about it', background: '#fff1f2', accent: '#e11d48', text: '#881337' },
}

export const PILLS = {
    ml: ['#0d9488', '#ffffff', 'Machine learning'],
    event: ['#ffbf00', '#3b2f00', 'Events'],
    control: ['#ffab19', '#3b2600', 'Control'],
    looks: ['#9966ff', '#ffffff', 'Looks'],
    sound: ['#cf63cf', '#ffffff', 'Sound'],
    operator: ['#59c059', '#ffffff', 'Operators'],
    data: ['#ff8c1a', '#ffffff', 'Variables'],
    robot: ['#2563eb', '#ffffff', 'Robot'],
}

const HERO_STYLE = 'background:#1e3a8a;color:#ffffff;border-radius:16px;padding:28px 32px;margin:0 0 28px 0;'
const CHECKLIST_STYLE = 'background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:16px 20px;margin:20px 0;color:#1e293b;'
const H2_STYLE = 'margin-top:36px;padding-bottom:6px;border-bottom:3px solid #f97316;'
const TH_STYLE = 'background:#1e3a8a;color:#ffffff;text-align:left;padding:10px 12px;'
const TD_STYLE = 'padding:10px 12px;vertical-align:top;'

const boxStyle = (box) => `background:${box.background};border-left:6px solid ${box.accent};border-radius:12px;padding:16px 20px;margin:20px 0;color:${box.text};`
const labelStyle = (box) => `margin:0 0 6px 0;font-weight:700;color:${box.accent};text-transform:uppercase;font-size:13px;letter-spacing:.06em;`
export const pillStyle = (kind) => {
    const [background, color] = PILLS[kind] || PILLS.ml
    return `display:inline-block;background:${background};color:${color};font-family:ui-monospace,Consolas,monospace;font-size:14px;font-weight:600;padding:2px 10px;border-radius:999px;margin:2px 0;`
}

const escapeHtml = (text) => String(text).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]))

/**
 * Drops CSS properties that another editor extension already owns (text-align, color…), so the toolbar keeps working.
 */
function stripStyleProps(style, props) {
    if (!style) {
        return null
    }
    const kept = style
        .split(';')
        .map((rule) => rule.trim())
        .filter((rule) => rule && !props.includes(rule.split(':')[0].trim().toLowerCase()))
    return kept.length ? kept.join(';') + ';' : null
}

const styleAttribute = (ownedProps = []) => ({
    default: null,
    parseHTML: (element) => stripStyleProps(element.getAttribute('style'), ownedProps),
    renderHTML: (attributes) => (attributes.style ? { style: attributes.style } : {}),
})

/**
 * Keeps inline styles on ordinary elements (headings, list items, table cells, styled spans) instead of dropping them.
 */
export const PreservedStyles = Extension.create({
    name: 'preservedStyles',

    addGlobalAttributes() {
        return [
            { types: ['paragraph', 'heading'], attributes: { style: styleAttribute(['text-align']) } },
            {
                types: ['bulletList', 'orderedList', 'listItem', 'blockquote', 'table', 'tableRow', 'tableHeader', 'tableCell'],
                attributes: { style: styleAttribute() },
            },
            { types: ['textStyle'], attributes: { style: styleAttribute(['color', 'font-family']) } },
        ]
    },
})

/**
 * A styled container: lesson banner, callout box or checklist. Any <div style> or <div data-block> becomes one.
 */
export const StyledBlock = Node.create({
    name: 'styledBlock',
    group: 'block',
    content: 'block+',
    defining: true,

    addAttributes() {
        return {
            kind: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-block'),
                renderHTML: (attributes) => (attributes.kind ? { 'data-block': attributes.kind } : {}),
            },
            style: styleAttribute(),
        }
    },

    parseHTML() {
        return [{ tag: 'div[data-block]' }, { tag: 'div[style]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', HTMLAttributes, 0]
    },
})

export function boxHtml(kind, bodyHtml = '<p>Write here…</p>') {
    const box = BOXES[kind] || BOXES.key
    return `<div data-block="${kind}" style="${boxStyle(box)}"><p style="${labelStyle(box)}">${escapeHtml(box.label)}</p>${bodyHtml}</div>`
}

export function heroHtml(title = 'Lesson title') {
    return `<div data-block="hero" style="${HERO_STYLE}">`
        + '<p style="margin:0;"><span style="display:inline-block;background:#f97316;color:#ffffff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:999px;">Lesson 1</span></p>'
        + `<h1 style="color:#ffffff;margin:14px 0 8px 0;font-size:30px;line-height:1.2;">${escapeHtml(title)}</h1>`
        + '<p style="color:#dbeafe;margin:0 0 14px 0;font-size:17px;">One sentence about what learners will make or discover.</p>'
        + '<p style="color:#bfdbfe;margin:0;font-size:13px;">Course · Module · 60 minutes</p>'
        + '</div>'
}

export function checklistHtml() {
    return `<div data-block="checklist" style="${CHECKLIST_STYLE}">`
        + '<p style="margin:0 0 8px 0;font-weight:700;color:#1e3a8a;">Before you submit, check that you can:</p>'
        + '<ul><li><p>First thing to check</p></li><li><p>Second thing to check</p></li></ul>'
        + '</div>'
}

function findBlockDepth($pos) {
    for (let depth = $pos.depth; depth > 0; depth--) {
        if ($pos.node(depth).type.name === 'styledBlock') {
            return depth
        }
    }
    return null
}

/**
 * Inserts a callout box. With text selected, the selected paragraphs are moved into the box.
 */
export function insertBox(editor, kind) {
    const box = BOXES[kind] || BOXES.key
    if (editor.state.selection.empty) {
        editor.chain().focus().insertContent(boxHtml(kind)).run()
        return
    }

    editor.chain().focus()
        .wrapIn('styledBlock', { kind, style: boxStyle(box) })
        .command(({ tr, state }) => {
            const depth = findBlockDepth(tr.selection.$from)
            if (depth === null) {
                return false
            }
            const label = state.schema.nodes.paragraph.create({ style: labelStyle(box) }, state.schema.text(box.label))
            tr.insert(tr.selection.$from.start(depth), label)
            return true
        })
        .run()
}

/**
 * Removes the box around the cursor and keeps everything that was inside it.
 */
export function unwrapBox(editor) {
    const { state, view } = editor
    const depth = findBlockDepth(state.selection.$from)
    if (depth === null) {
        return false
    }
    const $from = state.selection.$from
    const node = $from.node(depth)
    const pos = $from.before(depth)
    view.dispatch(state.tr.replaceWith(pos, pos + node.nodeSize, node.content).scrollIntoView())
    editor.commands.focus()
    return true
}

export function togglePill(editor, kind) {
    const current = editor.getAttributes('textStyle').style || ''
    if (/border-radius:\s*999px/.test(current)) {
        editor.chain().focus().extendMarkRange('textStyle').setMark('textStyle', { style: null }).removeEmptyTextStyle().run()
        return
    }
    if (editor.state.selection.empty) {
        editor.chain().focus().insertContent(`<span style="${pillStyle(kind)}">block name</span>&nbsp;`).run()
        return
    }
    editor.chain().focus().setMark('textStyle', { style: pillStyle(kind) }).run()
}

/**
 * Gives every header and cell of the table at the cursor the lesson table style (navy header row).
 */
export function styleTable(editor) {
    const { state, view } = editor
    const $from = state.selection.$from
    let tableDepth = null
    for (let depth = $from.depth; depth > 0; depth--) {
        if ($from.node(depth).type.name === 'table') {
            tableDepth = depth
            break
        }
    }
    if (tableDepth === null) {
        return false
    }
    const tableStart = $from.before(tableDepth)
    const tr = state.tr
    $from.node(tableDepth).descendants((node, offset) => {
        if (node.type.name === 'tableHeader' || node.type.name === 'tableCell') {
            const style = node.type.name === 'tableHeader' ? TH_STYLE : TD_STYLE
            tr.setNodeMarkup(tableStart + 1 + offset, undefined, { ...node.attrs, style })
            return false
        }
        return true
    })
    view.dispatch(tr)
    return true
}

export function styleHeading(editor) {
    if (!editor.isActive('heading', { level: 2 })) {
        editor.chain().focus().setHeading({ level: 2 }).run()
    }
    editor.chain().focus().updateAttributes('heading', { style: H2_STYLE }).run()
}

export const INSERT_OPTIONS = [
    ['', 'Styled block…'],
    ['hero', '🎯 Lesson banner'],
    ...Object.entries(BOXES).map(([kind, box]) => [`box:${kind}`, `▌ ${box.label} box`]),
    ['checklist', '☑ Checklist box'],
    ['heading', 'Orange section heading'],
    ['table', 'Style this table'],
    ...Object.entries(PILLS).map(([kind, pill]) => [`pill:${kind}`, `● ${pill[2]} block pill`]),
    ['unwrap', '✕ Remove box (keep text)'],
]

export function runInsertOption(editor, value, lessonTitle = '') {
    if (value === 'hero') {
        editor.chain().focus().insertContentAt(0, heroHtml(lessonTitle || 'Lesson title')).run()
    } else if (value.startsWith('box:')) {
        insertBox(editor, value.slice(4))
    } else if (value === 'checklist') {
        editor.chain().focus().insertContent(checklistHtml()).run()
    } else if (value === 'heading') {
        styleHeading(editor)
    } else if (value === 'table') {
        if (!styleTable(editor)) {
            alert('Click inside a table first.')
        }
    } else if (value.startsWith('pill:')) {
        togglePill(editor, value.slice(5))
    } else if (value === 'unwrap') {
        if (!unwrapBox(editor)) {
            alert('Click inside a box first.')
        }
    }
}
