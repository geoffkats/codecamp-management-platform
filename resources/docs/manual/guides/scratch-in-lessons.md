---
title: Scratch blocks in lessons
summary: Type a Scratch script in the lesson editor and it turns into real coloured blocks for students.
category: Teaching
icon: puzzle-piece
order: 21
audience: [admin, trainer]
---

Lessons can show Scratch scripts as real Scratch blocks, the same way quiz questions do. Students see the coloured blocks on the lesson page, not plain text.

## Three ways to add a script

**1. Just type it.** Write one block per line, starting with a `when …` block:

```text
when flag clicked
move 10 steps
say [Hello!] for 2 seconds
```

When you press **Enter** after the second line, the lines become a Scratch block and a preview appears underneath.

![A Scratch block in the lesson editor: the script on top, the block preview below](manual:scratch-editor.png)

Keep typing; each new line is added to the same script. Press **Enter** three times to leave the block and carry on writing normal text.

Only scripts that start with a `when …` block (for example *when flag clicked*, *when this sprite clicked*, *when space key pressed*) are converted while typing, so a sentence like *"Go to the next slide"* is never changed.

**2. Paste it.** Copy a script from anywhere and paste it into the editor. It becomes a Scratch block automatically.

**3. Use the toolbar.**

![The 🧩 Scratch and Detect Scratch buttons in the editor toolbar](manual:scratch-toolbar.png)

- **🧩 Scratch**: on an empty line, inserts a starter script. With lines selected, turns them into one Scratch block. Inside a code block, switches it between Scratch and normal code.
- **Detect Scratch**: scans the whole lesson and converts every Scratch script written as normal text. Use this for older lessons.

## Writing tips

- One block per line. Use `end` to close `repeat`, `forever` and `if` blocks.
- Numbers can be typed plainly (`move 10 steps`, `turn 15 degrees`). The preview adds the round number shapes.
- Text inputs go in square brackets: `say [Hello!]`.
- Edit the text in the yellow box. The preview below updates as you type.
- Leave a blank line between two separate scripts.

The full typing rules (dropdowns, operators, `if … then`) are the same as for quiz questions. See *Code and Scratch questions*.

## What students see

On the lesson page the script shows as Scratch blocks with a small *Scratch blocks* label.

![The same script on the student's lesson page](manual:scratch-lesson-view.png)

Older lessons that already contain Scratch scripts written as plain lines are shown as blocks too.
