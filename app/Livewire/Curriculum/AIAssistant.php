<?php

namespace App\Livewire\Curriculum;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Services\GeminiAIService;
use Livewire\Component;
use Livewire\Attributes\On;

class AIAssistant extends Component
{
    public $showChat = false;
    public $courseId = null;
    public $messages = [];
    public $userMessage = '';

    public function mount($courseId = null)
    {
        $this->courseId = $courseId;
    }

    private function getGemini()
    {
        return new GeminiAIService();
    }

    public function toggleChat()
    {
        $this->showChat = !$this->showChat;
        
        // Send welcome message on first open
        if ($this->showChat && empty($this->messages)) {
            $this->addSystemMessage("👋 Hi! I'm your AI curriculum assistant. I can help you:\n\n• Generate curriculum outlines\n• Create lesson content\n• Write quiz questions\n• Suggest learning objectives\n• Improve existing content\n\nJust ask me anything!");
        }
    }

    public function closeChat()
    {
        $this->showChat = false;
    }

    private function addUserMessage($message)
    {
        $this->messages[] = [
            'type' => 'user',
            'content' => $message,
            'timestamp' => now(),
        ];
        $this->dispatch('message-added');
    }

    private function addAIMessage($message)
    {
        $this->messages[] = [
            'type' => 'ai',
            'content' => $message,
            'timestamp' => now(),
        ];
        $this->dispatch('message-added');
    }

    private function addSystemMessage($message)
    {
        $this->messages[] = [
            'type' => 'system',
            'content' => $message,
            'timestamp' => now(),
        ];
        $this->dispatch('message-added');
    }

    private function getCourseContext()
    {
        if (!$this->courseId) {
            return '';
        }

        $course = Course::with(['modules.lessons.assessments'])->find($this->courseId);
        
        if (!$course) {
            return '';
        }

        $context = "Current Course Context:\n";
        $context .= "Course: {$course->title}\n";
        $context .= "Description: {$course->description}\n";
        $context .= "Category: {$course->category}\n\n";
        
        $context .= "Modules:\n";
        foreach ($course->modules as $module) {
            $context .= "- {$module->title}\n";
            foreach ($module->lessons as $lesson) {
                $context .= "  • {$lesson->title} (Type: {$lesson->lesson_type})\n";
                if ($lesson->assessments->count() > 0) {
                    $context .= "    - Has {$lesson->assessments->count()} assessment(s)\n";
                }
            }
        }

        return $context;
    }

    public function sendMessage()
    {
        if (empty(trim($this->userMessage))) {
            return;
        }

        $message = trim($this->userMessage);
        $this->addUserMessage($message);
        $this->userMessage = '';

        try {
            $gemini = $this->getGemini();
            
            // Build prompt with course context
            $prompt = $this->getCourseContext();
            $prompt .= "\n\nUser Question: {$message}\n\n";
            $prompt .= "Provide a helpful, detailed response as a curriculum building assistant. ";
            $prompt .= "If the user asks to generate content, create it in a format ready to copy/paste. ";
            $prompt .= "Use the course context above to give relevant, specific suggestions.";

            $result = $gemini->generateContent($prompt);

            if ($result['success']) {
                $this->addAIMessage($result['content']);
            } else {
                $this->addSystemMessage("⚠️ Error: " . ($result['error'] ?? 'Failed to generate response'));
            }
        } catch (\Exception $e) {
            $this->addSystemMessage("⚠️ Error: " . $e->getMessage());
        }

        // Scroll to bottom
        $this->dispatch('scroll-to-bottom');
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->addSystemMessage("Chat cleared. How can I help you?");
    }

    public function quickAction($action)
    {
        $prompts = [
            'outline' => "Generate a detailed curriculum outline for this course with modules and lessons",
            'lesson' => "Suggest a new lesson topic that would fit well in this curriculum",
            'quiz' => "Create 5 quiz questions based on the existing lessons in this course",
            'objectives' => "Generate learning objectives for the entire course",
            'improve' => "Review the current course structure and suggest improvements",
        ];

        if (isset($prompts[$action])) {
            $this->userMessage = $prompts[$action];
            $this->sendMessage();
        }
    }

    public function render()
    {
        return view('livewire.curriculum.ai-assistant');
    }
}
