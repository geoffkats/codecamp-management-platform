# 🤖 AI Curriculum Builder Setup Guide

**Date:** December 7, 2025  
**Status:** ✅ READY TO USE

---

## Quick Start

### 1. Get Your Free Gemini API Key

1. Go to [Google AI Studio](https://aistudio.google.com/app/apikeys)
2. Click **"Get API Key"** → **"Create API key in new project"**
3. Copy your API key
4. Add it to your `.env` file:

```bash
GEMINI_API_KEY=your_api_key_here
```

### 2. Test the Connection

Navigate to the **Curriculum Builder** and look for the ✨ **AI Assistant** button in the bottom-right corner.

---

## Features

### 📋 Curriculum Outline Generator
- **Input:** Course title, description, target audience, duration (weeks)
- **Output:** Structured curriculum with modules, lessons, objectives, and assessment types
- **Use Case:** Start building a new course structure from scratch

### 📖 Lesson Content Generator
- **Input:** Topic, target audience, learning objectives
- **Output:** Complete lesson content with introduction, concepts, examples, and summary
- **Use Case:** Generate comprehensive lesson material quickly

### ❓ Quiz Question Generator
- **Input:** Topic, difficulty level (easy/medium/hard), number of questions
- **Output:** Multiple-choice questions with answers and explanations
- **Use Case:** Create assessments aligned with your lesson content

### 🎯 Learning Objectives Generator
- **Input:** Topic
- **Output:** 5 clear, measurable learning objectives
- **Use Case:** Define what students should achieve after the lesson

---

## AI Assistant Interface

### 🎨 UI Overview

```
┌─────────────────────────────────────────────┐
│  ✨ AI Curriculum Assistant                │
│  Powered by Google Gemini AI                │
├─────────────────────────────────────────────┤
│                                             │
│  📋 Curriculum Outline  📖 Lesson Content   │
│  ❓ Quiz Questions      🎯 Learning Objs.   │
│                                             │
│  Form Fields (Dynamic based on selection)   │
│  ┌──────────────────────────────────────┐  │
│  │ Topic: [input]                       │  │
│  │ Description: [textarea]              │  │
│  │ ...                                  │  │
│  └──────────────────────────────────────┘  │
│                                             │
│  [Cancel]  [🚀 Generate]                   │
│                                             │
│  ✅ Generated Content (if available)       │
│  ┌──────────────────────────────────────┐  │
│  │ [Content here...]                    │  │
│  │ [📋 Copy]                            │  │
│  └──────────────────────────────────────┘  │
│                                             │
└─────────────────────────────────────────────┘
```

---

## How to Use

### Step 1: Open Curriculum Builder
- Navigate to **Curriculum** → **Builder**
- Select or create a course

### Step 2: Click AI Assistant Button
- Look for the ✨ **AI Assistant** button (bottom-right, sticky)
- Modal will open with feature options

### Step 3: Select Generation Type
- Choose what you want to generate:
  - 📋 Curriculum Outline
  - 📖 Lesson Content
  - ❓ Quiz Questions
  - 🎯 Learning Objectives

### Step 4: Fill in Form
- Enter required information based on your selection
- Form fields change dynamically

### Step 5: Generate Content
- Click **🚀 Generate**
- Wait for AI to process (usually 5-15 seconds)
- Content appears in the preview box

### Step 6: Copy & Use
- Click **📋 Copy** to copy to clipboard
- Paste into your curriculum builder
- Edit and refine as needed

---

## Example Workflows

### 📋 Create Full Course Structure

**Input:**
- Course Title: "Web Development with Laravel"
- Description: "Learn modern web development using Laravel framework"
- Target Audience: "Beginner developers with basic PHP knowledge"
- Duration: 8 weeks

**Output:**
```
MODULE 1: Laravel Basics
- Lesson 1: Introduction to Laravel
- Lesson 2: Project Setup & Configuration
- Lesson 3: Routing & Controllers
- Assessment: Quiz on routing concepts

MODULE 2: Database & Models
- Lesson 1: Database Design
- Lesson 2: Eloquent ORM
- Lesson 3: Relationships
- Assessment: Build a data model

...
```

### 📖 Generate Detailed Lesson

**Input:**
- Topic: "Authentication in Laravel"
- Target Audience: "Intermediate PHP developers"
- Objectives: "Implement user authentication, use middleware, understand password hashing"

**Output:**
```
INTRODUCTION
Learn how to implement secure user authentication in your Laravel applications...

KEY CONCEPTS
- Password hashing and verification
- JWT tokens vs Session-based auth
- Middleware for route protection
- Guard configuration

REAL-WORLD EXAMPLES
[Code examples provided]

COMMON MISTAKES
- Storing plain-text passwords
- Not validating input...
```

### ❓ Generate Quiz Questions

**Input:**
- Topic: "Database Relationships"
- Difficulty: "Medium"
- Number: 5 questions

**Output:**
```
Q1: What is the difference between One-to-Many and Many-to-Many relationships?
A) One-to-Many involves two related models, Many-to-Many involves multiple relationships
B) There is no significant difference
C) One-to-Many is deprecated in modern databases
D) Many-to-Many requires explicit pivot tables

Answer: A
Explanation: One-to-Many means one record can have multiple related records...
```

---

## Caching & Performance

The AI service implements smart caching:
- **Same request cache:** 24 hours
- **Reduces API calls:** No duplicate requests for identical prompts
- **Cost-saving:** Fewer API calls = less resource usage
- **Speed:** Cached responses return instantly

---

## API Limits & Quotas

**Google Gemini Free Tier:**
- 60 requests per minute
- 1,500 requests per day
- Limits reset daily

**Optimization Tips:**
- Generate in batches (don't spam requests)
- Edit generated content instead of regenerating
- Use caching by not requesting identical content twice
- Share generated content among team members

---

## Troubleshooting

### ❌ "Gemini API is not configured"

**Solution:**
1. Get your API key from [Google AI Studio](https://aistudio.google.com/app/apikeys)
2. Add to `.env`:
   ```
   GEMINI_API_KEY=your_key_here
   ```
3. Restart the application
4. Run `php artisan config:clear`

### ❌ "API request failed"

**Solutions:**
- Check internet connection
- Verify API key is correct
- Check if you've exceeded rate limits (60 req/min)
- Try again in a few seconds
- Check logs: `storage/logs/laravel.log`

### ❌ "Unexpected API response"

**Solutions:**
- Check `.env` GEMINI_API_KEY is correct
- Verify you're connected to the internet
- Try a simpler prompt first
- Check Laravel logs for detailed error

### ⚠️ Slow Response Time

**Reasons & Solutions:**
- **Network latency:** Try again
- **API busy:** Rate limiting - wait a moment
- **Complex request:** Break into smaller requests
- **AI thinking time:** Normal (5-15 seconds)

---

## Code Architecture

### Service Layer
**File:** `app/Services/GeminiAIService.php`

```php
// Generate curriculum outline
$gemini = new GeminiAIService();
$result = $gemini->generateCurriculumOutline(
    courseTitle: "Web Dev",
    courseDescription: "Learn web development",
    targetAudience: "Beginners",
    duration: 8
);

// Generate lesson content
$result = $gemini->generateLessonContent(
    topic: "Authentication",
    targetAudience: "Intermediate developers",
    learningObjectives: "Auth basics, JWT tokens, middleware"
);

// Generate quiz questions
$result = $gemini->generateAssessmentQuestions(
    topic: "Laravel Routing",
    difficulty: "medium",
    count: 5
);

// Generate learning objectives
$result = $gemini->generateLearningObjectives(
    topic: "Database Design",
    count: 5
);
```

### Livewire Component
**File:** `app/Livewire/Curriculum/AIAssistant.php`

Handles:
- Form input validation
- API communication
- Response caching
- Error handling
- User interaction

### Blade Template
**File:** `resources/views/livewire/curriculum/ai-assistant.blade.php`

Features:
- Beautiful gradient UI
- Modal interface
- Dynamic form fields
- Loading states
- Response display
- Copy-to-clipboard functionality

---

## Best Practices

### ✅ DO:
- Review generated content before using
- Edit and refine based on your course style
- Combine AI-generated with your personal expertise
- Use for inspiration and starting points
- Test generated questions before assessment

### ❌ DON'T:
- Use generated content verbatim without review
- Publish without proofreading
- Generate duplicate content (use cache)
- Share personal information in prompts
- Rely solely on AI (add human expertise)

---

## Privacy & Security

- **API Keys:** Keep GEMINI_API_KEY secret (never commit to git)
- **Data:** Requests sent to Google servers (review their privacy policy)
- **Caching:** Local server cache only
- **No storage:** Generated content not stored on Google servers permanently
- **HTTPS:** Always use HTTPS in production

---

## Future Enhancements

Potential features to add:
- 🎨 Generate course images/thumbnails
- 📝 Plagiarism checking for generated content
- 🔍 Keyword extraction for SEO
- 📊 Content difficulty analysis
- 🌍 Multi-language support
- 💬 Conversational AI assistant (multi-turn)
- 📹 Video script generation
- 🎯 Rubric generation for assessments

---

## Support

### Get Help
1. Check logs: `storage/logs/laravel.log`
2. Review this guide
3. Test API key at [Google AI Studio](https://aistudio.google.com/app/apikeys)
4. Check internet connection
5. Restart application: `php artisan serve`

### Report Issues
Create an issue with:
- Error message (from logs or UI)
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

---

**🚀 Ready to build amazing courses with AI!**

Generated content is a starting point. Combine it with your expertise for the best results.

