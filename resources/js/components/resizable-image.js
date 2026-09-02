import Image from '@tiptap/extension-image'

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: (element) => element.getAttribute('width') || element.style.width || null,
                renderHTML: (attributes) => {
                    if (!attributes.width) {
                        return {}
                    }

                    const width = String(attributes.width)
                    const normalized = /(%|px)$/.test(width) ? width : `${width}px`

                    return {
                        width,
                        style: `width: ${normalized}; height: auto;`,
                    }
                },
            },
        }
    },
})
