import './bootstrap';
import { initTipTapEditor, createToolbar, escapeUnknownTags } from './components/tiptap-editor';

// TipTap is bundled directly — no dynamic import needed
window.loadTipTap = () => Promise.resolve({ initTipTapEditor, createToolbar });

// Lazy load Chart.js only when needed (kept dynamic — chart.js is huge and rarely used)
window.loadChart = async () => {
    if (!window.Chart) {
        const Chart = await import('chart.js/auto');
        window.Chart = Chart.default;
    }
    return window.Chart;
};

// Alpine component for TipTap editor with Livewire integration.
// Assigned on window because Livewire's Alpine boots from a classic
// script and this file is a deferred Vite module — blade waits for this.
window.setupTipTapEditor = function (content) {
    let editor;

    const isValidStorageId = (value) => {
        if (value === null || value === undefined) return false;
        const text = String(value).trim();
        return text !== '' && text !== 'undefined' && text !== 'null';
    };

    const buildDraftKey = (courseId, lessonId) => {
        let resolvedCourseId = isValidStorageId(courseId) ? String(courseId).trim() : null;

        if (!resolvedCourseId) {
            const pathMatch = window.location.pathname.match(/\/builder\/(\d+)/);
            if (pathMatch && pathMatch[1]) {
                resolvedCourseId = pathMatch[1];
            }
        }

        if (!resolvedCourseId) {
            return null;
        }

        const resolvedLessonId = isValidStorageId(lessonId) ? String(lessonId).trim() : 'new';
        return `lesson_content_draft_${resolvedCourseId}_${resolvedLessonId}`;
    };

    const saveDraftSafely = (key, html) => {
        if (!key || !html) return;

        // Keep drafts bounded so one large paste cannot exhaust storage.
        const maxChars = 300000;
        if (html.length > maxChars) {
            return;
        }

        try {
            localStorage.setItem(key, html);
        } catch (error) {
            if (!(error && error.name === 'QuotaExceededError')) {
                return;
            }

            try {
                // Best effort cleanup: remove older lesson drafts, keep current key.
                const keysToRemove = [];
                for (let i = 0; i < localStorage.length; i += 1) {
                    const storageKey = localStorage.key(i);
                    if (storageKey && storageKey.startsWith('lesson_content_draft_') && storageKey !== key) {
                        keysToRemove.push(storageKey);
                    }
                }

                keysToRemove.forEach((storageKey) => localStorage.removeItem(storageKey));
                localStorage.setItem(key, html);
            } catch (retryError) {
                console.warn('Autosave skipped: localStorage quota exceeded.');
            }
        }
    };
    
    return {
        content: content,
        loading: true,
        error: null,
        showDraftBanner: false,
        _pendingDraft: null,
        _draftKey: null,

        restoreDraft() {
            if (this._pendingDraft) {
                editor.commands.setContent(escapeUnknownTags(this._pendingDraft));
                this.content = this._pendingDraft;
            }
            this.showDraftBanner = false;
            this._pendingDraft = null;
        },
        discardDraft() {
            if (this._draftKey) localStorage.removeItem(this._draftKey);
            this.showDraftBanner = false;
            this._pendingDraft = null;
        },

        async init(element, courseId, lessonId) {
            try {
                if (editor) {
                    this.loading = false;
                    return;
                }

                if (this.autosaveTimer) {
                    clearInterval(this.autosaveTimer);
                    this.autosaveTimer = null;
                }

                if (this.lessonSavedHandler) {
                    window.removeEventListener('lesson-saved', this.lessonSavedHandler);
                    this.lessonSavedHandler = null;
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

                if (editorElement.dataset.tiptapInitialized === 'true') {
                    this.loading = false;
                    return;
                }

                // Lazy load TipTap
                const { initTipTapEditor, createToolbar } = await window.loadTipTap();
                
                // Initialize editor
                editorElement.innerHTML = '';

                // Debounce editor -> Livewire sync. Serializing a large document
                // to HTML on every keystroke is expensive and hammers the
                // Alpine/entangle binding; flush on blur so the latest content is
                // always synced before any Livewire action (save, toggles, etc.).
                let pendingHtml = null;
                let syncTimer = null;
                const flushContent = () => {
                    if (syncTimer) {
                        clearTimeout(syncTimer);
                        syncTimer = null;
                    }
                    if (pendingHtml !== null) {
                        this.content = pendingHtml;
                        pendingHtml = null;
                    }
                };

                editor = initTipTapEditor(editorElement, this.content, (html) => {
                    // Update Alpine data (which syncs to Livewire via entangle)
                    pendingHtml = html;
                    clearTimeout(syncTimer);
                    syncTimer = setTimeout(flushContent, 300);
                });
                
                if (!editor) {
                    this.error = 'Editor failed to initialize.';
                    this.loading = false;
                    return;
                }

                editor.on('blur', flushContent);

                createToolbar(editor, editorElement.parentElement);
                editorElement.dataset.tiptapInitialized = 'true';
                this.loading = false;

                const draftKey = buildDraftKey(courseId, lessonId);
                this._draftKey = draftKey;

                // Watch for external content changes (from Livewire)
                this.$watch('content', (newContent) => {
                    if (!editor || editor.isDestroyed) return;
                    if (newContent === editor.getHTML()) return;

                    // Never replace the document while the user is typing in it.
                    // A failed/stale Livewire round-trip (e.g. a 403 on /livewire/update)
                    // can echo back old content; calling setContent mid-edit invalidates
                    // ProseMirror positions and throws "RangeError: Position -1 outside
                    // of fragment", destroying the editor session.
                    if (editor.isFocused) return;

                    try {
                        editor.commands.setContent(escapeUnknownTags(newContent) || '<p></p>');
                    } catch (error) {
                        console.warn('TipTap: skipped external content update', error);
                    }
                });

                // Autosave to localStorage every 15 seconds
                this.autosaveTimer = setInterval(() => {
                    if (editor) {
                        const html = editor.getHTML();
                        if (html && html !== '<p></p>') {
                            saveDraftSafely(draftKey, html);
                        }
                    }
                }, 15000);

                // Offer to restore draft via inline banner (not a blocking confirm dialog)
                const draft = draftKey ? localStorage.getItem(draftKey) : null;
                if (draft && draft !== '<p></p>' && (!this.content || this.content === '<p></p>')) {
                    this._pendingDraft = draft;
                    this.showDraftBanner = true;
                }
                
                // Clear draft on successful save
                this.lessonSavedHandler = () => {
                    if (draftKey) {
                        localStorage.removeItem(draftKey);
                    }
                };
                window.addEventListener('lesson-saved', this.lessonSavedHandler);
            } catch (error) {
                console.error('TipTap load error:', error);
                const message = error && error.message ? ` (${error.message})` : '';
                this.error = `Editor failed to load${message}. Using plain text editor.`;
                this.loading = false;
            }
        }
    };
};

function syncLessonContent(host, html) {
    const textarea = host.querySelector('.lesson-content-editor');
    if (textarea) {
        textarea.value = html;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    const wireEl = host.closest('[wire\\:id]');
    if (!wireEl || typeof Livewire === 'undefined' || typeof Livewire.find !== 'function') {
        return;
    }

    const component = Livewire.find(wireEl.getAttribute('wire:id'));
    if (component && typeof component.set === 'function') {
        component.set('formData.content', html, false);
    }
}

function mountLessonEditors() {
    document.querySelectorAll('.lesson-tiptap-host:not([data-tiptap-mounted])').forEach((host) => {
        const mount = host.querySelector('.lesson-tiptap-mount');
        const textarea = host.querySelector('.lesson-content-editor');
        if (!mount) {
            return;
        }

        host.dataset.tiptapMounted = 'true';

        let pendingHtml = null;
        let syncTimer = null;
        const flushContent = () => {
            if (syncTimer) {
                clearTimeout(syncTimer);
                syncTimer = null;
            }
            if (pendingHtml === null) {
                return;
            }
            syncLessonContent(host, pendingHtml);
            pendingHtml = null;
        };

        const editor = initTipTapEditor(mount, textarea ? textarea.value : '', (html) => {
            pendingHtml = html;
            clearTimeout(syncTimer);
            syncTimer = setTimeout(flushContent, 300);
        });

        if (!editor) {
            delete host.dataset.tiptapMounted;
            if (textarea) {
                textarea.classList.remove('hidden');
            }
            return;
        }

        editor.on('blur', flushContent);
        createToolbar(editor, host);
        if (textarea) {
            textarea.classList.add('hidden');
        }
    });
}

function startLessonEditorWatcher() {
    mountLessonEditors();

    if (!window.__lessonEditorObserver && document.body) {
        window.__lessonEditorObserver = new MutationObserver(() => mountLessonEditors());
        window.__lessonEditorObserver.observe(document.body, { childList: true, subtree: true });
    }
}

document.addEventListener('DOMContentLoaded', startLessonEditorWatcher);
document.addEventListener('livewire:navigated', mountLessonEditors);
document.addEventListener('livewire:init', () => {
    startLessonEditorWatcher();
    Livewire.hook('morph.added', () => queueMicrotask(mountLessonEditors));
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => queueMicrotask(mountLessonEditors));
    });
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status !== 419) {
                return;
            }
            preventDefault();
            const path = window.location.pathname || '/';
            if (path.startsWith('/login')) {
                window.location.reload();
                return;
            }
            window.location.replace('/login');
        });
    });
});

if (document.readyState !== 'loading') {
    startLessonEditorWatcher();
}

