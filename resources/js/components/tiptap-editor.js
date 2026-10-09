import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import FontFamily from '@tiptap/extension-font-family'
import Underline from '@tiptap/extension-underline'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'
import Placeholder from '@tiptap/extension-placeholder'
import Typography from '@tiptap/extension-typography'
import Youtube from '@tiptap/extension-youtube'
import { TableKit } from '@tiptap/extension-table'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import { common, createLowlight } from 'lowlight'
import { ResizableImage } from './resizable-image'
import { INSERT_OPTIONS, PreservedStyles, StyledBlock, runInsertOption } from './lesson-blocks'
import { isScratchLine, looksLikeScratch } from './scratch-detect'

const lowlight = createLowlight(common)
// Scratch source is shown as plain text; registering it stops lowlight auto-detecting another language.
lowlight.register('scratch', () => ({ name: 'scratch', contains: [] }))

const SCRATCH_STARTER = 'when flag clicked\nmove (10) steps\nsay [Hello!] for (2) seconds'

const ScratchAwareCodeBlock = CodeBlockLowlight.extend({
    addNodeView() {
        return ({ node }) => {
            let current = node
            let timer = null

            const dom = document.createElement('div')
            const pre = document.createElement('pre')
            const code = document.createElement('code')
            pre.appendChild(code)

            const preview = document.createElement('div')
            preview.contentEditable = 'false'
            preview.className = 'cau-scratch-preview'
            const label = document.createElement('div')
            label.className = 'cau-scratch-label'
            label.textContent = 'Scratch blocks · preview (edit the text above)'
            const canvas = document.createElement('div')
            preview.append(label, canvas)
            dom.append(pre, preview)

            const paint = () => {
                const text = current.textContent
                if (!text.trim()) {
                    canvas.textContent = 'Type Scratch blocks above, one per line, e.g. "when flag clicked".'
                    return
                }
                import('./question-text')
                    .then(({ renderScratchSvg }) => renderScratchSvg(text, 0.65))
                    .then((svg) => canvas.replaceChildren(svg))
                    .catch(() => { canvas.textContent = 'Could not draw these blocks. Check the spelling of each block.' })
            }

            const sync = () => {
                const language = current.attrs.language
                const isScratch = language === 'scratch'
                code.className = language ? `language-${language}` : ''
                dom.className = isScratch ? 'cau-scratch-block' : ''
                preview.hidden = !isScratch
                clearTimeout(timer)
                if (isScratch) {
                    timer = setTimeout(paint, 300)
                }
            }

            sync()

            return {
                dom,
                contentDOM: code,
                update(updated) {
                    if (updated.type !== current.type) return false
                    const changed = updated.textContent !== current.textContent || updated.attrs.language !== current.attrs.language
                    current = updated
                    if (changed) sync()
                    return true
                },
                ignoreMutation(mutation) {
                    return preview.contains(mutation.target) || mutation.target === dom
                },
                stopEvent(event) {
                    return preview.contains(event.target)
                },
                destroy() {
                    clearTimeout(timer)
                },
            }
        }
    },
})

function scratchNode(schema, text) {
    const content = text.trim()
    return schema.nodes.codeBlock.create({ language: 'scratch' }, content ? schema.text(content) : null)
}

function toast(message, tone = 'blue') {
    const el = document.createElement('div')
    el.className = `fixed top-4 right-4 z-50 rounded-lg px-4 py-2 text-white shadow-lg ${tone === 'green' ? 'bg-green-500' : 'bg-blue-500'}`
    el.textContent = message
    document.body.appendChild(el)
    setTimeout(() => el.remove(), 3000)
}

/**
 * Toolbar action: turn the selected lines into one Scratch block, flip the current code block
 * to/from Scratch, or insert a starter script.
 */
export function toggleScratchBlock(editor) {
    const { state } = editor
    const { selection, schema } = state
    const { $from, $to, empty } = selection

    if ($from.parent.type.name === 'codeBlock') {
        const language = $from.parent.attrs.language === 'scratch' ? null : 'scratch'
        editor.chain().focus().updateAttributes('codeBlock', { language }).run()
        return
    }

    if (empty) {
        editor.chain().focus().insertContent({
            type: 'codeBlock',
            attrs: { language: 'scratch' },
            content: [{ type: 'text', text: SCRATCH_STARTER }],
        }).run()
        return
    }

    const from = $from.depth ? $from.before(1) : selection.from
    const to = $to.depth ? $to.after(1) : selection.to
    const text = state.doc.textBetween(from, to, '\n', '\n')
    editor.view.dispatch(state.tr.replaceWith(from, to, scratchNode(schema, text)).scrollIntoView())
    editor.commands.focus()
}

/**
 * Finds Scratch scripts typed or pasted as normal paragraphs (or plain code blocks) and turns them into Scratch blocks.
 */
export function detectScratchBlocks(editor) {
    const { state } = editor
    const { schema } = state
    const changes = []
    let run = []

    const flush = () => {
        const lines = run.flatMap((item) => item.lines)
        if (run.length && lines.filter((line) => line.trim() !== '').length >= 2 && looksLikeScratch(lines.join('\n'))) {
            changes.push({ from: run[0].offset, to: run[run.length - 1].end, text: lines.join('\n') })
        }
        run = []
    }

    state.doc.forEach((node, offset) => {
        const end = offset + node.nodeSize

        if (node.type.name === 'codeBlock') {
            flush()
            if (node.attrs.language !== 'scratch' && looksLikeScratch(node.textContent)) {
                changes.push({ from: offset, to: end, text: node.textContent, markupOnly: true })
            }
            return
        }

        if (node.type.name !== 'paragraph') {
            flush()
            return
        }

        const lines = node.textBetween(0, node.content.size, '\n', '\n').split('\n')
        const nonBlank = lines.filter((line) => line.trim() !== '')
        if (nonBlank.length && nonBlank.every(isScratchLine)) {
            run.push({ offset, end, lines: nonBlank })
        } else {
            flush()
        }
    })
    flush()

    if (!changes.length) {
        toast('No Scratch scripts found. Put each block on its own line, e.g. "when flag clicked".')
        return 0
    }

    const tr = state.tr
    changes.reverse().forEach((change) => {
        if (change.markupOnly) {
            tr.setNodeMarkup(change.from, undefined, { language: 'scratch' })
        } else {
            tr.replaceWith(change.from, change.to, scratchNode(schema, change.text))
        }
    })
    editor.view.dispatch(tr)
    toast(`Turned ${changes.length} ${changes.length === 1 ? 'script' : 'scripts'} into Scratch blocks.`, 'green')

    return changes.length
}

const SCRATCH_HAT = /^\s*when\b/i

/**
 * Enter at the end of a typed Scratch script ("when flag clicked", "move 10 steps", …) turns the lines above
 * into a Scratch block and keeps the cursor inside it for the next block. Scripts must start with a "when …"
 * hat block so ordinary sentences are never converted while typing.
 */
function convertTypedScratch(view) {
    const { state } = view
    const { selection, doc, schema } = state
    const { $from, empty } = selection

    if ($from.parent.type.name === 'codeBlock') {
        const code = $from.parent.textContent
        if (!$from.parent.attrs.language && SCRATCH_HAT.test(code) && looksLikeScratch(code)) {
            view.dispatch(state.tr.setNodeMarkup($from.before($from.depth), undefined, { ...$from.parent.attrs, language: 'scratch' }))
        }
        return false
    }

    if (!empty || $from.depth !== 1 || $from.parent.type.name !== 'paragraph' || $from.parentOffset !== $from.parent.content.size) {
        return false
    }

    const lines = []
    let index = $from.index(0)
    let from = $from.before(1)
    const to = $from.after(1)

    while (index >= 0) {
        const node = doc.child(index)
        const text = node.type.name === 'paragraph' ? node.textContent : ''
        if (!isScratchLine(text)) {
            break
        }
        lines.unshift(text.trim())
        if (index !== $from.index(0)) {
            from -= node.nodeSize
        }
        if (SCRATCH_HAT.test(text)) {
            break
        }
        index -= 1
    }

    if (lines.length < 2 || !SCRATCH_HAT.test(lines[0]) || !looksLikeScratch(lines.join('\n'))) {
        return false
    }

    const text = lines.join('\n') + '\n'
    const tr = state.tr.replaceWith(from, to, scratchNode(schema, lines.join('\n')))
    tr.insertText('\n', from + 1 + text.length - 1)
    tr.setSelection(selection.constructor.create(tr.doc, from + 1 + text.length)).scrollIntoView()
    view.dispatch(tr)
    toast('Scratch script detected — keep typing blocks. Press Enter three times to leave the block.', 'green')

    return true
}

const EDITOR_ALLOWED_TAGS = new Set([
    'p', 'br', 'hr', 'div', 'span',
    'strong', 'b', 'em', 'i', 'u', 's', 'del', 'strike', 'mark',
    'ul', 'ol', 'li',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'blockquote', 'pre', 'code', 'a', 'img',
    'table', 'thead', 'tbody', 'tr', 'th', 'td', 'colgroup', 'col',
    'iframe', 'figure', 'figcaption', 'video', 'source',
    'input', 'label',
])

const TEXT_COLORS = ['#111827', '#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#2563eb', '#7c3aed', '#db2777']
const HIGHLIGHT_COLORS = ['#fef08a', '#bbf7d0', '#bae6fd', '#e9d5ff', '#fecdd3', '#e5e7eb']

export function escapeUnknownTags(html) {
    if (!html || typeof html !== 'string' || html.indexOf('<') === -1) {
        return html
    }

    return html.replace(
        /<\/?([a-zA-Z][a-zA-Z0-9-]*)((?:"[^"]*"|'[^']*'|[^>"'])*)>/g,
        (match, tagName) => EDITOR_ALLOWED_TAGS.has(tagName.toLowerCase())
            ? match
            : match.replace(/</g, '&lt;').replace(/>/g, '&gt;')
    )
}

export function initTipTapEditor(element, initialContent = '', onUpdate = null) {
    if (!element) {
        console.error('TipTap: No element provided for editor initialization')
        return null
    }

    try {
        return new Editor({
            element,
            editable: true,
            extensions: [
                StarterKit.configure({
                    codeBlock: false,
                    underline: false,
                    link: false,
                }),
                TextStyle,
                Color,
                FontFamily,
                Underline,
                Highlight.configure({ multicolor: true }),
                Typography,
                Placeholder.configure({
                    placeholder: 'Write the lesson here…',
                }),
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                ResizableImage.configure({
                    inline: true,
                    allowBase64: true,
                }),
                Link.configure({
                    openOnClick: false,
                    HTMLAttributes: {
                        class: 'text-blue-600 dark:text-blue-400 underline',
                    },
                }),
                ScratchAwareCodeBlock.configure({
                    lowlight,
                }),
                TableKit.configure({
                    table: { resizable: true },
                }),
                TaskList,
                TaskItem.configure({ nested: true }),
                StyledBlock,
                PreservedStyles,
                Youtube.configure({
                    width: 640,
                    height: 360,
                    nocookie: true,
                    HTMLAttributes: {
                        class: 'rounded-lg overflow-hidden my-4',
                    },
                }),
            ],
            content: escapeUnknownTags(initialContent) || '<p></p>',
            editorProps: {
                attributes: {
                    class: 'prose prose-sm sm:prose lg:prose-lg dark:prose-invert max-w-none focus:outline-none min-h-[300px] px-4 py-3',
                },
                handleKeyDown(view, event) {
                    if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey || event.altKey || event.isComposing) {
                        return false
                    }

                    return convertTypedScratch(view)
                },
                handlePaste(view, event) {
                    const text = event.clipboardData?.getData('text/plain')
                    const { $from } = view.state.selection
                    if (!text || $from.parent.type.name === 'codeBlock' || !looksLikeScratch(text)) {
                        return false
                    }

                    view.dispatch(view.state.tr.replaceSelectionWith(scratchNode(view.state.schema, text)).scrollIntoView())
                    toast('Pasted as Scratch blocks. Use the 🧩 Scratch button to switch back to plain text.', 'green')
                    return true
                },
            },
            onUpdate: ({ editor }) => {
                if (!onUpdate) {
                    return
                }

                try {
                    onUpdate(editor.getHTML())
                } catch (error) {
                    console.error('TipTap onUpdate error:', error)
                }
            },
        })
    } catch (error) {
        console.error('TipTap initialization error:', error)
        return null
    }
}

function runSafe(label, action) {
    try {
        action()
    } catch (error) {
        console.error(`${label} action error:`, error)
    }
}

function button(icon, title, action, isActive = null) {
    return { icon, title, action, isActive }
}

function separator() {
    return { type: 'separator' }
}

function selectControl(className, html) {
    const select = document.createElement('select')
    select.className = className
    select.innerHTML = html
    return select
}

function colorSwatch(color, title, onPick) {
    const buttonEl = document.createElement('button')
    buttonEl.type = 'button'
    buttonEl.title = title
    buttonEl.className = 'h-5 w-5 rounded-full border border-gray-300 dark:border-gray-600 shadow-sm'
    buttonEl.style.backgroundColor = color
    buttonEl.addEventListener('pointerdown', (event) => {
        event.preventDefault()
        event.stopPropagation()
        onPick()
    })
    return buttonEl
}

export function createToolbar(editor, container) {
    const existingToolbar = container.querySelector('[data-tiptap-toolbar]')
    if (existingToolbar) {
        existingToolbar.remove()
    }

    const toolbar = document.createElement('div')
    toolbar.setAttribute('data-tiptap-toolbar', 'true')
    toolbar.className = 'flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'

    const controlClass = 'text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-2 py-1'

    const fontSelect = selectControl(controlClass, [
        '<option value="default">Font</option>',
        '<option value="Arial, sans-serif">Sans</option>',
        '<option value="&quot;Helvetica Neue&quot;, Arial, sans-serif">Helvetica</option>',
        '<option value="&quot;Times New Roman&quot;, serif">Times</option>',
        '<option value="Georgia, serif">Serif</option>',
        '<option value="&quot;Courier New&quot;, monospace">Mono</option>',
        '<option value="Verdana, sans-serif">Verdana</option>',
    ].join(''))
    fontSelect.addEventListener('change', () => {
        runSafe('Font family', () => {
            if (fontSelect.value === 'default') {
                editor.chain().focus().unsetFontFamily().run()
            } else {
                editor.chain().focus().setFontFamily(fontSelect.value).run()
            }
        })
    })
    toolbar.appendChild(fontSelect)

    const headingSelect = selectControl(controlClass, [
        '<option value="p">Paragraph</option>',
        '<option value="1">Heading 1</option>',
        '<option value="2">Heading 2</option>',
        '<option value="3">Heading 3</option>',
    ].join(''))
    headingSelect.addEventListener('change', () => {
        runSafe('Heading', () => {
            if (headingSelect.value === 'p') {
                editor.chain().focus().setParagraph().run()
            } else {
                editor.chain().focus().toggleHeading({ level: Number(headingSelect.value) }).run()
            }
        })
    })
    toolbar.appendChild(headingSelect)

    const blockSelect = selectControl(controlClass, INSERT_OPTIONS
        .map(([value, label]) => `<option value="${value}">${label}</option>`)
        .join(''))
    blockSelect.title = 'Banners, coloured boxes, block pills and table styles'
    blockSelect.addEventListener('change', () => {
        const value = blockSelect.value
        blockSelect.value = ''
        if (value) {
            runSafe('Styled block', () => runInsertOption(editor, value))
        }
    })
    toolbar.appendChild(blockSelect)

    const buttons = [
        button('<strong>B</strong>', 'Bold', () => editor.chain().focus().toggleBold().run(), () => editor.isActive('bold')),
        button('<em>I</em>', 'Italic', () => editor.chain().focus().toggleItalic().run(), () => editor.isActive('italic')),
        button('<u>U</u>', 'Underline', () => editor.chain().focus().toggleUnderline().run(), () => editor.isActive('underline')),
        button('<s>S</s>', 'Strike', () => editor.chain().focus().toggleStrike().run(), () => editor.isActive('strike')),
        separator(),
        button('Left', 'Align left', () => editor.chain().focus().setTextAlign('left').run(), () => editor.isActive({ textAlign: 'left' })),
        button('Center', 'Align center', () => editor.chain().focus().setTextAlign('center').run(), () => editor.isActive({ textAlign: 'center' })),
        button('Right', 'Align right', () => editor.chain().focus().setTextAlign('right').run(), () => editor.isActive({ textAlign: 'right' })),
        button('Justify', 'Justify', () => editor.chain().focus().setTextAlign('justify').run(), () => editor.isActive({ textAlign: 'justify' })),
        separator(),
        button('• List', 'Bullet list', () => editor.chain().focus().toggleBulletList().run(), () => editor.isActive('bulletList')),
        button('1. List', 'Numbered list', () => editor.chain().focus().toggleOrderedList().run(), () => editor.isActive('orderedList')),
        button('☑ Tasks', 'Task list', () => editor.chain().focus().toggleTaskList().run(), () => editor.isActive('taskList')),
        button('Indent', 'Indent', () => {
            if (editor.can().sinkListItem('listItem')) {
                editor.chain().focus().sinkListItem('listItem').run()
            } else if (editor.can().sinkListItem('taskItem')) {
                editor.chain().focus().sinkListItem('taskItem').run()
            }
        }),
        button('Outdent', 'Outdent', () => {
            if (editor.can().liftListItem('listItem')) {
                editor.chain().focus().liftListItem('listItem').run()
            } else if (editor.can().liftListItem('taskItem')) {
                editor.chain().focus().liftListItem('taskItem').run()
            }
        }),
        separator(),
        button('Table', 'Insert table', () => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(), () => editor.isActive('table')),
        button('+ Col', 'Add column', () => editor.chain().focus().addColumnAfter().run()),
        button('- Col', 'Delete column', () => editor.chain().focus().deleteColumn().run()),
        button('+ Row', 'Add row', () => editor.chain().focus().addRowAfter().run()),
        button('- Row', 'Delete row', () => editor.chain().focus().deleteRow().run()),
        separator(),
        button('</>', 'Code block', () => editor.chain().focus().toggleCodeBlock().run(), () => editor.isActive('codeBlock') && editor.getAttributes('codeBlock').language !== 'scratch'),
        button('🧩 Scratch', 'Scratch blocks: turn the selected lines into Scratch blocks, or insert a new script', () => toggleScratchBlock(editor), () => editor.isActive('codeBlock', { language: 'scratch' })),
        button('Detect Scratch', 'Find Scratch scripts written as normal text in this lesson and turn them into blocks', () => detectScratchBlocks(editor)),
        button('❝', 'Quote', () => editor.chain().focus().toggleBlockquote().run(), () => editor.isActive('blockquote')),
        button('—', 'Divider', () => editor.chain().focus().setHorizontalRule().run()),
        separator(),
        button('🔗', 'Add link', () => {
            const { from, to } = editor.state.selection
            if (from === to) {
                alert('Select some text first to add a link.')
                return
            }
            const previousUrl = editor.getAttributes('link').href
            const url = window.prompt('Enter URL:', previousUrl || 'https://')
            if (url === null) {
                return
            }
            if (url === '') {
                editor.chain().focus().extendMarkRange('link').unsetLink().run()
                return
            }
            editor.chain().focus().setLink({ href: url }).run()
        }, () => editor.isActive('link')),
        button('🖼', 'Upload image', () => uploadImage(editor)),
        button('S', 'Small image', () => editor.chain().focus().updateAttributes('image', { width: '40%' }).run()),
        button('M', 'Medium image', () => editor.chain().focus().updateAttributes('image', { width: '70%' }).run()),
        button('L', 'Large image', () => editor.chain().focus().updateAttributes('image', { width: '100%' }).run()),
        button('▶ Video', 'YouTube video', () => {
            const url = window.prompt('Paste a YouTube URL:')
            if (!url) {
                return
            }
            editor.chain().focus().setYoutubeVideo({ src: url, width: 640, height: 360 }).run()
        }),
    ]

    const buttonElements = []

    buttons.forEach((btn) => {
        if (btn.type === 'separator') {
            const sep = document.createElement('div')
            sep.className = 'w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1'
            toolbar.appendChild(sep)
            return
        }

        const buttonEl = document.createElement('button')
        buttonEl.type = 'button'
        buttonEl.className = 'px-2 py-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-xs font-medium text-gray-700 dark:text-gray-300'
        buttonEl.title = btn.title
        buttonEl.innerHTML = btn.icon
        buttonEl.addEventListener('pointerdown', (event) => {
            event.preventDefault()
            event.stopPropagation()
            runSafe(btn.title, btn.action)
            updateActiveStates()
        })
        buttonElements.push({ button: buttonEl, config: btn })
        toolbar.appendChild(buttonEl)
    })

    const colorWrap = document.createElement('div')
    colorWrap.className = 'flex items-center gap-1 ml-1'
    colorWrap.title = 'Text color'
    TEXT_COLORS.forEach((color) => {
        colorWrap.appendChild(colorSwatch(color, `Text ${color}`, () => {
            editor.chain().focus().setColor(color).run()
        }))
    })
    toolbar.appendChild(colorWrap)

    const highlightWrap = document.createElement('div')
    highlightWrap.className = 'flex items-center gap-1 ml-1'
    highlightWrap.title = 'Highlight'
    HIGHLIGHT_COLORS.forEach((color) => {
        highlightWrap.appendChild(colorSwatch(color, `Highlight ${color}`, () => {
            editor.chain().focus().toggleHighlight({ color }).run()
        }))
    })
    toolbar.appendChild(highlightWrap)

    function updateActiveStates() {
        try {
            const fontFamily = editor.getAttributes('textStyle').fontFamily || 'default'
            fontSelect.value = fontFamily

            if (editor.isActive('heading', { level: 1 })) {
                headingSelect.value = '1'
            } else if (editor.isActive('heading', { level: 2 })) {
                headingSelect.value = '2'
            } else if (editor.isActive('heading', { level: 3 })) {
                headingSelect.value = '3'
            } else {
                headingSelect.value = 'p'
            }

            buttonElements.forEach(({ button: buttonEl, config }) => {
                if (config.isActive && config.isActive()) {
                    buttonEl.classList.add('bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400')
                    buttonEl.classList.remove('text-gray-700', 'dark:text-gray-300')
                } else {
                    buttonEl.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400')
                    buttonEl.classList.add('text-gray-700', 'dark:text-gray-300')
                }
            })
        } catch (error) {
            console.debug('TipTap toolbar state error:', error)
        }
    }

    editor.on('selectionUpdate', updateActiveStates)
    editor.on('update', updateActiveStates)
    setTimeout(updateActiveStates, 100)

    container.insertBefore(toolbar, container.firstChild)
}

async function uploadImage(editor) {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.onchange = async (event) => {
        const file = event.target.files?.[0]
        if (!file) {
            return
        }

        const loadingMsg = document.createElement('div')
        loadingMsg.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
        loadingMsg.textContent = 'Uploading image...'
        document.body.appendChild(loadingMsg)

        try {
            const formData = new FormData()
            formData.append('image', file)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')
            if (!csrfToken) {
                throw new Error('CSRF token not found')
            }

            const response = await fetch('/api/upload-image', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content,
                },
            })

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}))
                throw new Error(errorData.message || 'Upload failed')
            }

            const data = await response.json()
            editor.chain().focus().insertContent({
                type: 'image',
                attrs: {
                    src: data.url,
                    alt: file.name,
                    width: '70%',
                },
            }).run()
            loadingMsg.textContent = '✓ Image uploaded!'
            loadingMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
        } catch (error) {
            console.error('Image upload error:', error)
            loadingMsg.textContent = '✗ ' + (error.message || 'Upload failed')
            loadingMsg.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
        }

        setTimeout(() => loadingMsg.remove(), 3000)
    }
    input.click()
}
