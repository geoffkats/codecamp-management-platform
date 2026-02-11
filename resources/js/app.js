import './bootstrap';

// Lazy load Chart.js only when needed
window.loadChart = async () => {
    if (!window.Chart) {
        const Chart = await import('chart.js/auto');
        window.Chart = Chart.default;
    }
    return window.Chart;
};

// Lazy load TipTap only when needed
window.loadTipTap = async () => {
    if (!window.initTipTapEditor) {
        const { initTipTapEditor, createToolbar } = await import('./components/tiptap-editor');
        window.initTipTapEditor = initTipTapEditor;
        window.createToolbar = createToolbar;
    }
    return { initTipTapEditor: window.initTipTapEditor, createToolbar: window.createToolbar };
};

// Alpine component for TipTap editor with Livewire integration
window.setupTipTapEditor = function (content) {
    let editor;
    
    return {
        content: content,
        loading: true,
        error: null,
        
        async init(element, courseId) {
            try {
                if (editor) {
                    this.loading = false;
                    return;
                }

                const resolveElement = () => element || (this.$refs ? this.$refs.editor : null);
                let editorElement = resolveElement();

                if (!editorElement || !editorElement.isConnected) {
                    await new Promise((resolve) => requestAnimationFrame(resolve));
                    editorElement = resolveElement();
                }

                if (!editorElement || !editorElement.isConnected) {
                    this.error = 'Editor failed to initialize (missing element).';
                    this.loading = false;
                    return;
                }

                // Lazy load TipTap
                const { initTipTapEditor, createToolbar } = await window.loadTipTap();
                
                // Initialize editor
                editor = initTipTapEditor(editorElement, this.content, (html) => {
                    // Update Alpine data (which syncs to Livewire via entangle)
                    this.content = html;
                });
                
                if (!editor) {
                    this.error = 'Editor failed to initialize.';
                    this.loading = false;
                    return;
                }

                createToolbar(editor, editorElement.parentElement);
                this.loading = false;
                
                // Watch for external content changes (from Livewire)
                this.$watch('content', (newContent) => {
                    // Skip if content matches current editor content
                    if (newContent === editor.getHTML()) return;
                    
                    // Update editor content from external source (Livewire)
                    editor.commands.setContent(newContent || '<p></p>');
                });
                
                // Autosave to localStorage every 15 seconds
                setInterval(() => {
                    if (editor) {
                        const html = editor.getHTML();
                        if (html && html !== '<p></p>') {
                            localStorage.setItem(`lesson_content_draft_${courseId}`, html);
                        }
                    }
                }, 15000);
                
                // Restore from localStorage if available
                const draft = localStorage.getItem(`lesson_content_draft_${courseId}`);
                if (draft && draft !== '<p></p>' && (!this.content || this.content === '<p></p>')) {
                    if (confirm('Found an autosaved draft. Would you like to restore it?')) {
                        editor.commands.setContent(draft);
                        this.content = draft;
                    }
                }
                
                // Clear draft on successful save
                window.addEventListener('lesson-saved', () => {
                    localStorage.removeItem(`lesson_content_draft_${courseId}`);
                });
            } catch (error) {
                console.error('TipTap load error:', error);
                const message = error && error.message ? ` (${error.message})` : '';
                this.error = `Editor failed to load${message}. Using plain text editor.`;
                this.loading = false;
            }
        }
    };
};

