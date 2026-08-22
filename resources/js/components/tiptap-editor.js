import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Link from '@tiptap/extension-link'
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyle } from '@tiptap/extension-text-style'
import FontFamily from '@tiptap/extension-font-family'
import { common, createLowlight } from 'lowlight'

const lowlight = createLowlight(common)

// Tags the configured extensions actually understand. Anything else found in
// stored content (e.g. <nav>, <header>, <form> typed as teaching material and
// saved raw by legacy editors) would be parsed as real DOM by ProseMirror and
// silently dropped, corrupting the document. Instead we escape unknown tags so
// they survive as visible literal text.
const EDITOR_ALLOWED_TAGS = new Set([
    'p', 'br', 'hr', 'div', 'span',
    'strong', 'b', 'em', 'i', 'u', 's', 'del', 'strike',
    'ul', 'ol', 'li',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'blockquote', 'pre', 'code', 'a', 'img',
])

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
    // Validate element exists
    if (!element) {
        console.error('TipTap: No element provided for editor initialization');
        return null;
    }

    try {
        const editor = new Editor({
            element: element,
            editable: true,
            extensions: [
                StarterKit.configure({
                    codeBlock: false, // We'll use CodeBlockLowlight instead
                }),
                TextStyle,
                FontFamily,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                Image.configure({
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
            ],
            content: escapeUnknownTags(initialContent) || '<p></p>',
            editorProps: {
                attributes: {
                    class: 'prose prose-sm sm:prose lg:prose-lg dark:prose-invert max-w-none focus:outline-none min-h-[300px] px-4 py-3',
                },
            },
            onUpdate: ({ editor }) => {
                if (onUpdate) {
                    try {
                        const html = editor.getHTML();
                        onUpdate(html);
                    } catch (error) {
                        console.error('TipTap onUpdate error:', error);
                    }
                }
            },
        })

        return editor
    } catch (error) {
        console.error('TipTap initialization error:', error);
        return null;
    }
}

export function createToolbar(editor, container) {
    const existingToolbar = container.querySelector('[data-tiptap-toolbar]')
    if (existingToolbar) {
        existingToolbar.remove()
    }

    const toolbar = document.createElement('div')
    toolbar.setAttribute('data-tiptap-toolbar', 'true')
    toolbar.className = 'flex flex-wrap items-center gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800'

    const fontSelect = document.createElement('select')
    fontSelect.className = 'text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-2 py-1'
    fontSelect.innerHTML = [
        '<option value="default">Font: Default</option>',
        '<option value="Arial, sans-serif">Font: Sans</option>',
        '<option value="\"Helvetica Neue\", Arial, sans-serif">Font: Helvetica</option>',
        '<option value="\"Times New Roman\", serif">Font: Times</option>',
        '<option value="Georgia, serif">Font: Serif</option>',
        '<option value="\"Courier New\", monospace">Font: Mono</option>',
        '<option value="Verdana, sans-serif">Font: Verdana</option>',
        '<option value="Tahoma, sans-serif">Font: Tahoma</option>',
        '<option value="\"Trebuchet MS\", sans-serif">Font: Trebuchet</option>',
    ].join('')
    fontSelect.addEventListener('change', () => {
        const value = fontSelect.value
        try {
            if (value === 'default') {
                editor.chain().focus().unsetFontFamily().run()
            } else {
                editor.chain().focus().setFontFamily(value).run()
            }
        } catch (error) {
            console.error('Font family action error:', error)
        }
    })
    toolbar.appendChild(fontSelect)

    const fontSep = document.createElement('div')
    fontSep.className = 'w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1'
    toolbar.appendChild(fontSep)

    const buttons = [
        {
            icon: '<strong>B</strong>',
            title: 'Bold',
            action: () => {
                try {
                    editor.chain().focus().toggleBold().run()
                } catch (error) {
                    console.error('Bold action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('bold')
                } catch {
                    return false
                }
            },
        },
        {
            icon: '<em>I</em>',
            title: 'Italic',
            action: () => {
                try {
                    editor.chain().focus().toggleItalic().run()
                } catch (error) {
                    console.error('Italic action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('italic')
                } catch {
                    return false
                }
            },
        },
        { type: 'separator' },
        {
            icon: 'H1',
            title: 'Heading 1',
            action: () => {
                try {
                    editor.chain().focus().toggleHeading({ level: 1 }).run()
                } catch (error) {
                    console.error('H1 action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('heading', { level: 1 })
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'H2',
            title: 'Heading 2',
            action: () => {
                try {
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                } catch (error) {
                    console.error('H2 action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('heading', { level: 2 })
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'H3',
            title: 'Heading 3',
            action: () => {
                try {
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                } catch (error) {
                    console.error('H3 action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('heading', { level: 3 })
                } catch {
                    return false
                }
            },
        },
        { type: 'separator' },
        {
            icon: 'Left',
            title: 'Align Left',
            action: () => {
                try {
                    editor.chain().focus().setTextAlign('left').run()
                } catch (error) {
                    console.error('Align left action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive({ textAlign: 'left' })
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'Center',
            title: 'Align Center',
            action: () => {
                try {
                    editor.chain().focus().setTextAlign('center').run()
                } catch (error) {
                    console.error('Align center action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive({ textAlign: 'center' })
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'Right',
            title: 'Align Right',
            action: () => {
                try {
                    editor.chain().focus().setTextAlign('right').run()
                } catch (error) {
                    console.error('Align right action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive({ textAlign: 'right' })
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'Justify',
            title: 'Justify',
            action: () => {
                try {
                    editor.chain().focus().setTextAlign('justify').run()
                } catch (error) {
                    console.error('Justify action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive({ textAlign: 'justify' })
                } catch {
                    return false
                }
            },
        },
        { type: 'separator' },
        {
            icon: '• List',
            title: 'Bullet List',
            action: () => {
                try {
                    editor.chain().focus().toggleBulletList().run()
                } catch (error) {
                    console.error('Bullet list action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('bulletList')
                } catch {
                    return false
                }
            },
        },
        {
            icon: '1. List',
            title: 'Numbered List',
            action: () => {
                try {
                    editor.chain().focus().toggleOrderedList().run()
                } catch (error) {
                    console.error('Ordered list action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('orderedList')
                } catch {
                    return false
                }
            },
        },
        {
            icon: 'Indent',
            title: 'Indent (List)',
            action: () => {
                try {
                    if (editor.can().sinkListItem('listItem')) {
                        editor.chain().focus().sinkListItem('listItem').run()
                    }
                } catch (error) {
                    console.error('Indent action error:', error)
                }
            },
        },
        {
            icon: 'Outdent',
            title: 'Outdent (List)',
            action: () => {
                try {
                    if (editor.can().liftListItem('listItem')) {
                        editor.chain().focus().liftListItem('listItem').run()
                    }
                } catch (error) {
                    console.error('Outdent action error:', error)
                }
            },
        },
        { type: 'separator' },
        {
            icon: '&lt;/&gt;',
            title: 'Code Block',
            action: () => {
                try {
                    editor.chain().focus().toggleCodeBlock().run()
                } catch (error) {
                    console.error('Code block action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('codeBlock')
                } catch {
                    return false
                }
            },
        },
        {
            icon: '❝ Quote',
            title: 'Quote',
            action: () => {
                try {
                    editor.chain().focus().toggleBlockquote().run()
                } catch (error) {
                    console.error('Blockquote action error:', error)
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('blockquote')
                } catch {
                    return false
                }
            },
        },
        { type: 'separator' },
        {
            icon: '🔗 Link',
            title: 'Add Link',
            action: () => {
                try {
                    // Check if text is selected
                    const { from, to } = editor.state.selection
                    const hasSelection = from !== to
                    
                    if (!hasSelection) {
                        alert('Please select some text first to add a link.')
                        return
                    }
                    
                    const previousUrl = editor.getAttributes('link').href
                    const url = window.prompt('Enter URL:', previousUrl)
                    
                    if (url === null) {
                        return
                    }
                    
                    if (url === '') {
                        // Remove link
                        editor.chain().focus().extendMarkRange('link').unsetLink().run()
                        return
                    }
                    
                    // Add link to selected text
                    editor.chain().focus().setLink({ href: url }).run()
                } catch (error) {
                    console.error('Link action error:', error)
                    alert('Failed to add link. Please try again.')
                }
            },
            isActive: () => {
                try {
                    return editor.isActive('link')
                } catch {
                    return false
                }
            },
        },
        {
            icon: '🖼️ Image',
            title: 'Upload Image',
            action: () => {
                try {
                    // Create file input
                    const input = document.createElement('input')
                    input.type = 'file'
                    input.accept = 'image/*'
                    input.onchange = async (e) => {
                        const file = e.target.files[0]
                        if (file) {
                            // Show loading state
                            const loadingMsg = document.createElement('div')
                            loadingMsg.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
                            loadingMsg.textContent = 'Uploading image...'
                            document.body.appendChild(loadingMsg)
                            
                            try {
                                // Upload to server
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
                                        'X-CSRF-TOKEN': csrfToken.content
                                    }
                                })
                                
                                if (response.ok) {
                                    const data = await response.json()
                                    // Focus editor and insert image using insertContent (safer than setImage)
                                    editor.chain().focus().insertContent({
                                        type: 'image',
                                        attrs: {
                                            src: data.url,
                                            alt: file.name,
                                        }
                                    }).run()
                                    loadingMsg.textContent = '✓ Image uploaded!'
                                    loadingMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
                                } else {
                                    const errorData = await response.json()
                                    throw new Error(errorData.message || 'Upload failed')
                                }
                            } catch (error) {
                                console.error('Image upload error:', error)
                                loadingMsg.textContent = '✗ ' + (error.message || 'Upload failed')
                                loadingMsg.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50'
                            }
                            
                            setTimeout(() => loadingMsg.remove(), 3000)
                        }
                    }
                    input.click()
                } catch (error) {
                    console.error('Image action error:', error)
                    alert('Failed to open file picker. Please try again.')
                }
            },
        },
    ]

    let buttonElements = []

    buttons.forEach((btn, index) => {
        if (btn.type === 'separator') {
            const sep = document.createElement('div')
            sep.className = 'w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1'
            toolbar.appendChild(sep)
        } else {
            const button = document.createElement('button')
            button.type = 'button'
            button.className = 'px-2 py-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-xs font-medium text-gray-700 dark:text-gray-300'
            button.title = btn.title
            button.innerHTML = btn.icon
            
            button.addEventListener('pointerdown', (e) => {
                e.preventDefault()
                e.stopPropagation()
                btn.action()
                updateActiveStates()
            })

            buttonElements.push({ button, config: btn })
            toolbar.appendChild(button)
        }
    })

    function updateActiveStates() {
        try {
            try {
                const fontFamily = editor.getAttributes('textStyle').fontFamily || 'default'
                fontSelect.value = fontFamily
            } catch (error) {
                console.debug('Font family update error:', error)
            }

            buttonElements.forEach(({ button, config }) => {
                try {
                    if (config.isActive && config.isActive()) {
                        button.classList.add('bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400')
                        button.classList.remove('text-gray-700', 'dark:text-gray-300')
                    } else {
                        button.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'text-blue-600', 'dark:text-blue-400')
                        button.classList.add('text-gray-700', 'dark:text-gray-300')
                    }
                } catch (error) {
                    // Silently fail for individual button state updates
                    console.debug('Button state update error:', error)
                }
            })
        } catch (error) {
            console.error('updateActiveStates error:', error)
        }
    }

    // Safely attach event listeners
    try {
        editor.on('selectionUpdate', updateActiveStates)
        editor.on('update', updateActiveStates)
        
        // Initial state
        setTimeout(updateActiveStates, 100)
    } catch (error) {
        console.error('Error attaching editor listeners:', error)
    }

    container.insertBefore(toolbar, container.firstChild)
}
