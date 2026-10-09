// Mirrors App\Support\QuestionText::SCRATCH_LINE so the editor and assessments agree on what a Scratch line is.
const SCRATCH_LINE = /^(when (green )?flag clicked|when this sprite clicked|when (\[.*\]|\w+) key pressed|when i receive|when backdrop switches|when i start as a clone|forever$|repeat( until)?\b|if\s*<.*>\s*then$|if .* then$|else$|end$|move \(?-?\d|turn (cw|ccw|right|left|↻|↺)?\s*\(?-?\d|go to\b|glide\b|point (in|towards)\b|change (x|y|size|color|\[|\()|set (x|y|size|rotation|\[|\()|say\b|think\b|switch (costume|backdrop)\b|next (costume|backdrop)$|show$|hide$|play sound\b|start sound\b|stop (all|this script|other scripts)|wait \(?\d|wait until\b|broadcast\b|ask \[|pen (up|down)$|erase all$|stamp$|create clone\b|delete this clone$|go to (front|back)|bounce\b|add .* to \[|delete .* of \[)/i

const CONTROL_ONLY = /^\s*(end|else)\s*$/i

export function isScratchLine(line) {
    const trimmed = line.trim()
    if (trimmed === '' || !SCRATCH_LINE.test(trimmed)) return false
    if (trimmed.endsWith(':') || /\w\(/.test(trimmed)) return false

    // Sentences like "Go to the next slide." or long instructions are prose, not blocks.
    return !/[.!]$/.test(trimmed) && trimmed.split(/\s+/).length <= 10
}

/**
 * True when most non-blank lines are Scratch blocks (at least two lines, at least one real block).
 */
export function looksLikeScratch(text) {
    const lines = String(text || '').replace(/\r\n?/g, '\n').split('\n').filter((line) => line.trim() !== '')
    if (lines.length < 2) return false

    const scratch = lines.filter(isScratchLine)
    const blocks = scratch.filter((line) => !CONTROL_ONLY.test(line))

    return blocks.length >= 1 && scratch.length / lines.length >= 0.7
}

/**
 * scratchblocks only recognises numbers written as (n), so "repeat 5" or "turn 15 degrees" typed by hand
 * would render as unknown blocks. Mirrors QuestionText::scratch(), but keeps blank lines (they separate scripts).
 */
export function normalizeScratch(text) {
    const lines = String(text || '').replace(/\r\n?/g, '\n').split('\n')
    const indents = lines.filter((line) => line.trim() !== '').map((line) => line.length - line.trimStart().length)
    const min = indents.length ? Math.min(...indents) : 0

    return lines
        .map((line) => line.slice(min))
        .map((line) => line
            .replace(/(?<=^|\s)(-?\d+(?:\.\d+)?)(?=\s|$)/g, '($1)')
            .replace(/^(\s*)turn\s+(?=\()/i, '$1turn cw '))
        .join('\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim()
}
