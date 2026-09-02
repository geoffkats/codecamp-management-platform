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

const lowlight = createLowlight(common)

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
                CodeBlockLowlight.configure({
                    lowlight,
                }),
                TableKit.configure({
                    table: { resizable: true },
                }),
                TaskList,
                TaskItem.configure({ nested: true }),
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
        button('</>', 'Code block', () => editor.chain().focus().toggleCodeBlock().run(), () => editor.isActive('codeBlock')),
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
