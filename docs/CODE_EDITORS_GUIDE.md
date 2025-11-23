# Code Editors Guide: Python, JavaScript & Web Development

## 🎯 Overview

Your platform now includes **interactive code editors** for Python, JavaScript, and Web Development lessons. Students can write, run, and test code directly in the browser!

---

## 🐍 Python Code Editor

### Features
- ✅ Syntax-highlighted editor
- ✅ Run button (server-side execution)
- ✅ Output console
- ✅ Error display
- ✅ Reset button
- ✅ Line numbers
- ✅ Security restrictions

### Usage in Lessons

```blade
<x-code-editor 
    language="python"
    code="print('Hello, World!')"
    title="Python Practice"
    :editable="true"
    :showOutput="true"
    height="400px"
/>
```

### Props
- `language` - "python" (required)
- `code` - Initial code (default: empty)
- `title` - Editor title (default: "Python Editor")
- `editable` - Allow editing (default: true)
- `showOutput` - Show output console (default: true)
- `height` - Editor height (default: "400px")

### Example Lesson Code

```php
// In your lesson model or seeder
$lesson->code_example = "# Python Variables
name = 'Student'
age = 15
print(f'Hello, {name}!')
print(f'You are {age} years old')

# Try changing the values above!";
```

### Auto-Detection
The Python editor automatically appears when:
- Lesson title contains "python"
- Lesson content contains "python"
- Lesson type is "code"
- Lesson has `code_example` field

---

## ⚡ JavaScript Code Editor

### Features
- ✅ Syntax-highlighted editor
- ✅ Run button (client-side execution)
- ✅ Console.log capture
- ✅ Error handling
- ✅ Reset functionality
- ✅ Line numbers

### Usage in Lessons

```blade
<x-code-editor 
    language="javascript"
    code="console.log('Hello!');"
    title="JavaScript Practice"
/>
```

### Props
Same as Python editor, but with `language="javascript"`

### Example Lesson Code

```php
$lesson->code_example = "// JavaScript Arrays
const numbers = [1, 2, 3, 4, 5];
const doubled = numbers.map(n => n * 2);
console.log('Original:', numbers);
console.log('Doubled:', doubled);

// Try adding more array methods!";
```

### Auto-Detection
Appears when lesson title contains "javascript" (but not "web" or "html")

---

## 🌐 Web Development Editor

### Features
- ✅ Split-screen layout (horizontal/vertical)
- ✅ Three tabs: HTML, CSS, JavaScript
- ✅ Live preview pane
- ✅ Auto-update on typing (1 second delay)
- ✅ Refresh button
- ✅ Layout toggle
- ✅ Fully responsive

### Usage in Lessons

```blade
<x-web-editor 
    html="<h1>Hello World!</h1>"
    css="h1 { color: blue; }"
    javascript="console.log('Ready!');"
    title="Web Playground"
    :editable="true"
/>
```

### Props
- `html` - Initial HTML code
- `css` - Initial CSS code
- `javascript` - Initial JavaScript code
- `title` - Editor title (default: "Web Development Editor")
- `editable` - Allow editing (default: true)

### Example Lesson Code

```php
$lesson->html_example = "<h1>My First Web Page</h1>
<p>Welcome to web development!</p>
<button id='clickMe'>Click Me</button>
<p id='output'></p>";

$lesson->css_example = "body {
  font-family: Arial, sans-serif;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
}

button {
  background: white;
  color: #667eea;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}";

$lesson->js_example = "document.getElementById('clickMe').addEventListener('click', function() {
  document.getElementById('output').textContent = 'Button clicked!';
});";
```

### Auto-Detection
Appears when lesson title contains:
- "web"
- "html"
- "css"
- "javascript" (with "web" or "html")

---

## 🔧 Backend Setup

### Requirements
- Python installed on server (for Python execution)
- Node.js installed on server (for server-side JS execution, optional)

### API Endpoints

**Python Execution:**
```
POST /api/execute/python
Body: { "code": "print('Hello')" }
Response: { "success": true, "output": "Hello\n" }
```

**JavaScript Execution:**
```
POST /api/execute/javascript
Body: { "code": "console.log('Hello')" }
Response: { "success": true, "output": "Hello\n" }
```

### Security Features

1. **Restricted Operations**
   - No file access (open, file)
   - No imports (os, sys, subprocess)
   - No eval/exec
   - No input() function

2. **Execution Limits**
   - 5 second timeout
   - 10,000 character code limit
   - Temporary file cleanup

3. **Authentication**
   - Requires authenticated user
   - Uses Sanctum middleware

### Installation

1. **Ensure Python is installed:**
   ```bash
   python --version
   ```

2. **Ensure Node.js is installed (optional):**
   ```bash
   node --version
   ```

3. **Create temp directory:**
   ```bash
   mkdir -p storage/app/temp
   chmod 755 storage/app/temp
   ```

4. **Test the API:**
   ```bash
   curl -X POST http://your-domain/api/execute/python \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{"code":"print(\"Hello\")"}'
   ```

---

## 📝 Adding Code Editors to Lessons

### Method 1: Auto-Detection (Easiest)

Just name your lesson appropriately:
- "Python Variables" → Python editor appears
- "JavaScript Arrays" → JavaScript editor appears
- "HTML Basics" → Web editor appears

### Method 2: Add Code Examples

In your lesson model or database:

```php
// Python lesson
$lesson->code_example = "print('Hello, World!')";

// Web lesson
$lesson->html_example = "<h1>Hello</h1>";
$lesson->css_example = "h1 { color: blue; }";
$lesson->js_example = "console.log('Ready!');";
```

### Method 3: Manual Component

In your lesson view or custom template:

```blade
<x-code-editor 
    language="python"
    code="{{ $lesson->code_example }}"
/>
```

---

## 🎓 Example Lessons

### Python Lesson: Variables

```php
Lesson::create([
    'title' => 'Python Variables',
    'lesson_type' => 'code',
    'code_example' => "# Variables in Python
name = 'Alice'
age = 25
height = 5.6

print(f'Name: {name}')
print(f'Age: {age}')
print(f'Height: {height} feet')

# Try creating your own variables!",
    'objectives' => "• Understand variables\n• Use different data types\n• Print variables",
]);
```

### JavaScript Lesson: Functions

```php
Lesson::create([
    'title' => 'JavaScript Functions',
    'lesson_type' => 'code',
    'code_example' => "// Functions in JavaScript
function greet(name) {
  return 'Hello, ' + name + '!';
}

console.log(greet('Student'));
console.log(greet('Teacher'));

// Try creating your own function!",
    'objectives' => "• Create functions\n• Use parameters\n• Return values",
]);
```

### Web Development Lesson: First Page

```php
Lesson::create([
    'title' => 'HTML Basics - Your First Page',
    'lesson_type' => 'code',
    'html_example' => "<h1>My First Web Page</h1>
<p>This is a paragraph.</p>
<button>Click Me</button>",
    'css_example' => "body {
  font-family: Arial;
  padding: 20px;
}

h1 {
  color: #4F46E5;
}

button {
  background: #4F46E5;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
}",
    'js_example' => "document.querySelector('button').addEventListener('click', function() {
  alert('Hello!');
});",
    'objectives' => "• Create HTML structure\n• Style with CSS\n• Add JavaScript interactivity",
]);
```

---

## 🎨 Customization

### Change Editor Height

```blade
<x-code-editor 
    language="python"
    code="print('Hello')"
    height="600px"
/>
```

### Disable Editing (Read-Only)

```blade
<x-code-editor 
    language="python"
    code="print('Example')"
    :editable="false"
/>
```

### Hide Output Console

```blade
<x-code-editor 
    language="python"
    code="print('Hello')"
    :showOutput="false"
/>
```

### Custom Title

```blade
<x-code-editor 
    language="python"
    code="print('Hello')"
    title="Exercise 1: Hello World"
/>
```

---

## 🐛 Troubleshooting

### Python Code Not Running

**Problem:** "Execution error" or timeout

**Solutions:**
1. Check Python is installed: `python --version`
2. Check temp directory exists: `storage/app/temp`
3. Check file permissions: `chmod 755 storage/app/temp`
4. Check for infinite loops in code
5. Verify API route is registered

### JavaScript Not Executing

**Problem:** Code runs but no output

**Solution:** JavaScript editor captures `console.log()`. Make sure code uses `console.log()` for output.

### Web Editor Not Updating

**Problem:** Changes don't appear in preview

**Solutions:**
1. Click "Run" button manually
2. Wait 1 second for auto-update
3. Click "Refresh" button
4. Check browser console for errors

### Security Errors

**Problem:** "Restricted operation detected"

**Solution:** This is intentional. The following are blocked for security:
- File operations (open, file)
- System imports (os, sys, subprocess)
- Dangerous functions (eval, exec)
- User input (input)

---

## 📊 Student Experience

### What Students See

1. **Python Lesson:**
   - Code editor with example code
   - "Run Code" button
   - Output console showing results
   - "Reset" button to restore original code

2. **JavaScript Lesson:**
   - Code editor with example code
   - "Run Code" button
   - Console output display
   - Instant feedback

3. **Web Development Lesson:**
   - Three tabs: HTML, CSS, JavaScript
   - Live preview pane
   - Layout toggle (horizontal/vertical)
   - Real-time updates

### Student Workflow

1. Read lesson instructions
2. See example code in editor
3. Modify the code
4. Click "Run" to test
5. See output/results
6. Iterate and learn
7. Reset if needed

---

## 🚀 Best Practices

### For Teachers

1. **Start Simple**
   - Begin with basic examples
   - Add comments explaining code
   - Gradually increase complexity

2. **Provide Clear Instructions**
   - Tell students what to modify
   - Explain expected output
   - Give hints for exercises

3. **Use Comments**
   - Add `# TODO:` comments for student tasks
   - Explain each section
   - Provide examples

4. **Test Your Code**
   - Run code before publishing
   - Check for errors
   - Verify output is clear

### Example with Comments

```python
# Python Variables Exercise
# TODO: Change the name to your own name
name = 'Student'

# TODO: Change the age to your age
age = 15

# This will print your information
print(f'Hello, {name}!')
print(f'You are {age} years old')

# TODO: Add a new variable for your favorite color
# and print it below
```

---

## 📈 Future Enhancements

Potential additions:
- [ ] Syntax highlighting (Monaco Editor integration)
- [ ] Code completion/IntelliSense
- [ ] Multiple test cases
- [ ] Auto-grading
- [ ] Code sharing
- [ ] Save student code
- [ ] Code history/versions
- [ ] Collaborative editing
- [ ] More languages (Java, C++, etc.)

---

## ✅ Summary

Your platform now supports:
- ✅ Python code execution
- ✅ JavaScript code execution
- ✅ HTML/CSS/JS web development
- ✅ Live preview
- ✅ Secure execution
- ✅ Auto-detection in lessons
- ✅ Student-friendly interface

Students can now **learn by doing** with interactive code editors in every lesson!
