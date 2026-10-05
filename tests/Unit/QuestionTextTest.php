<?php

use App\Support\QuestionText;

function segmentTypes(string $text): array
{
    return array_map(fn ($s) => $s['type'].(isset($s['language']) && $s['language'] ? ':'.$s['language'] : ''), QuestionText::segments($text));
}

it('splits pasted python out of the question prose', function () {
    $text = "Consider the following Python code:\n\nclass A:\n   def __init__(self, x):\n       self.x = x\n\nclass B(A):\n   def __init__(self, x, y):\n       super().__init__(x)\n       self.y = y\n\nWhat is the relationship between the A and B classes?";

    $segments = QuestionText::segments($text);

    expect(segmentTypes($text))->toBe(['text', 'code:python', 'text'])
        ->and($segments[1]['content'])->toStartWith("class A:\n   def __init__")
        ->and($segments[1]['label'])->toBe('Python')
        ->and(trim($segments[2]['content']))->toBe('What is the relationship between the A and B classes?');
});

it('recognises scratch scripts', function () {
    expect(segmentTypes("when green flag clicked\nrepeat 5\n   move 10 steps\n   turn 15 degrees\nend"))->toBe(['scratch'])
        ->and(segmentTypes("What happens here?\nset [x v] to [1]\nrepeat 4\n   change [x v] by (2)\nend\nsay (x)"))->toBe(['text', 'scratch']);
});

it('writes hand-typed scratch numbers the way scratchblocks expects', function () {
    $segments = QuestionText::segments("What will happen?\n\nset [count v] to [1]\n\nrepeat 5\n   move 10 steps\n   turn 15 degrees\nend");

    expect($segments[1]['content'])->toBe("set [count v] to [1]\nrepeat (5)\n   move (10) steps\n   turn cw (15) degrees\nend");
});

it('honours fenced blocks', function () {
    expect(segmentTypes("Predict:\n```js\nlet a = 1;\n```\nThen?"))->toBe(['text', 'code:javascript', 'text'])
        ->and(segmentTypes("Run:\n```scratch\nsay [Hi]\n```"))->toBe(['text', 'scratch']);
});

it('uses a stray language line as the label', function () {
    $text = "What will be printed?\nJavaScript\nlet age = 18;\nconsole.log(age);";

    expect(segmentTypes($text))->toBe(['text', 'code:javascript'])
        ->and(trim(QuestionText::segments($text)[0]['content']))->toBe('What will be printed?');
});

it('splits a sentence glued to the first code line', function () {
    $segments = QuestionText::segments("Look at the following CSS rule:p {\n  font-family: Arial;\n}\nWhy?");

    expect($segments[0]['content'])->toBe('Look at the following CSS rule:')
        ->and($segments[1]['type'])->toBe('code')
        ->and($segments[1]['content'])->toStartWith('p {');
});

it('keeps single-line and html-talk questions as escaped text', function () {
    expect(segmentTypes('What does <p>Hello<br>World</p> display?'))->toBe(['text'])
        ->and(QuestionText::inline('What does <br> do in `<p>`?'))
        ->toContain('&lt;br&gt;')
        ->toContain('<code')
        ->not->toContain('<br>');
});

it('summarises rich questions for lists', function () {
    $preview = QuestionText::preview("Predict the output:\nset [score v] to [0]\nrepeat 3\n   change [score v] by (5)\nend\nWhat is the final score?");

    expect($preview['text'])->toBe('Predict the output: What is the final score?')
        ->and($preview['kinds'])->toBe(['Scratch blocks']);
});
