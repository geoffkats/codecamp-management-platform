---
title: Writing code and Scratch questions
summary: How to type questions so code shows as a code panel and Scratch scripts show as real blocks.
category: Assessments
icon: code-bracket
order: 12
audience: [admin, trainer]
---

You don't need any special symbols. **Put the code or Scratch script on its own lines** under the question, and the system shows it properly to students:

- code becomes a dark panel with colours and the indentation kept,
- Scratch becomes real Scratch 3 blocks.

![A Python question and a Scratch question as students see them](manual:code-and-scratch.png)

## Code

Type or paste the code exactly as written, keeping its indentation:

```text
Consider the following Python code:

class A:
   def __init__(self, x):
       self.x = x

class B(A):
   def __init__(self, x, y):
       super().__init__(x)
       self.y = y

What is the relationship between the A and B classes?
```

Python, JavaScript, HTML, CSS, SQL, Java and C/C++ are recognised. Short code inside a sentence can go between backticks: `` `print()` ``.

## Scratch

Write one block per line, using the words on the block. Close `repeat`, `forever` and `if` with `end`:

```text
What shape will the sprite draw?
when green flag clicked
pen down
repeat 4
   move 100 steps
   turn 90 degrees
end
```

| You type | You get |
|---|---|
| `(10)` | a round number input |
| `[hello]` | a square text input |
| `[score v]` | a dropdown |
| `<touching [edge v] ?>` | a pointed true/false block |
| `end` | closes a C-shaped block |

Plain numbers like `repeat 5` or `move 10 steps` are fixed for you automatically.

## Always check the Student preview

While you type, a **Student preview** box appears under the question. If you see blocks or a code panel there, students will too. A **red** Scratch block means the wording doesn't match a real Scratch block. Check the exact text in Scratch.

> **If detection misses something:** wrap the script in a fence. Put ` ```scratch ` (or ` ```python `, ` ```html `…) on the line before and ` ``` ` on the line after. This is rarely needed.

## Good to know

- Blocks and code panels only appear in the **question text**. Answer options stay plain text, so for "which script is right?" questions, put the scripts in the question and use labels like *Script A* / *Script B* as answers.
- Questions *about* HTML are safe. `<p>Hello<br>World</p>` shows as text, it isn't run.
