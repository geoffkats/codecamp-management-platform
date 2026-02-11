<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    protected $client;
    protected $apiKey;
    protected $models = ['gemini-2.5-flash', 'gemini-1.5-flash', 'gemini-pro', 'gemini-1.5-pro']; // Try models in order
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        // Disable SSL verification for local development
        // For production, consider using proper SSL certificates or updating CA bundle
        $this->client = new Client([
            'verify' => false,
        ]);
    }

    /**
     * Generate curriculum outline for a course
     */
    public function generateCurriculumOutline($courseTitle, $courseDescription, $targetAudience, $duration)
    {
        $prompt = "Create a detailed curriculum outline for the following course:\n\n";
        $prompt .= "Course Title: $courseTitle\n";
        $prompt .= "Description: $courseDescription\n";
        $prompt .= "Target Audience: $targetAudience\n";
        $prompt .= "Duration: $duration weeks\n\n";
        $prompt .= "Provide a structured curriculum with:\n";
        $prompt .= "1. 3-5 main modules\n";
        $prompt .= "2. 3-5 lessons per module\n";
        $prompt .= "3. Learning objectives for each lesson\n";
        $prompt .= "4. Suggested assessment types\n";
        $prompt .= "Format as clear, parseable sections.";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Generate lesson content based on topic
     */
    public function generateLessonContent($topic, $targetAudience, $learningObjectives)
    {
        $prompt = "Create comprehensive lesson content for:\n\n";
        $prompt .= "Topic: $topic\n";
        $prompt .= "Target Audience: $targetAudience\n";
        $prompt .= "Learning Objectives:\n";
        $prompt .= implode("\n", array_map(fn($obj) => "- $obj", explode(',', $learningObjectives))) . "\n\n";
        $prompt .= "Provide:\n";
        $prompt .= "1. Introduction (2-3 paragraphs)\n";
        $prompt .= "2. Key concepts (with bullet points)\n";
        $prompt .= "3. Real-world examples\n";
        $prompt .= "4. Common misconceptions to address\n";
        $prompt .= "5. Summary with takeaways";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Generate quiz/assessment questions
     */
    public function generateAssessmentQuestions($topic, $difficulty, $count = 5)
    {
        $prompt = "Generate $count multiple-choice quiz questions about: $topic\n\n";
        $prompt .= "Difficulty Level: $difficulty (easy, medium, hard)\n";
        $prompt .= "Format each question as:\n";
        $prompt .= "Q: [Question text]\n";
        $prompt .= "A) [Option A]\n";
        $prompt .= "B) [Option B]\n";
        $prompt .= "C) [Option C]\n";
        $prompt .= "D) [Option D]\n";
        $prompt .= "Answer: [Correct option letter]\n";
        $prompt .= "Explanation: [Brief explanation]\n\n";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Suggest improvements to lesson content
     */
    public function suggestImprovements($lessonContent, $topic)
    {
        $prompt = "Review and suggest improvements to this lesson content about '$topic':\n\n";
        $prompt .= "---\n";
        $prompt .= substr($lessonContent, 0, 1000) . "...\n";
        $prompt .= "---\n\n";
        $prompt .= "Provide constructive suggestions for:\n";
        $prompt .= "1. Clarity and readability\n";
        $prompt .= "2. Engagement and examples\n";
        $prompt .= "3. Learning effectiveness\n";
        $prompt .= "4. Missing topics or concepts\n";
        $prompt .= "Keep suggestions concise and actionable.";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Generate module description from lessons
     */
    public function generateModuleDescription($moduleName, $lessons)
    {
        $lessonList = implode(", ", $lessons);
        $prompt = "Create a compelling module description for:\n\n";
        $prompt .= "Module Name: $moduleName\n";
        $prompt .= "Lessons: $lessonList\n\n";
        $prompt .= "Write a 2-3 paragraph description that:\n";
        $prompt .= "1. Explains the module's purpose\n";
        $prompt .= "2. Highlights key learning outcomes\n";
        $prompt .= "3. Mentions how it fits in the broader curriculum\n";
        $prompt .= "4. Motivates learners to engage";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Generate learning objectives from a topic
     */
    public function generateLearningObjectives($topic, $count = 5)
    {
        $prompt = "Generate $count clear, measurable learning objectives for the topic: '$topic'\n\n";
        $prompt .= "Each objective should:\n";
        $prompt .= "- Start with an action verb (Understand, Apply, Analyze, etc.)\n";
        $prompt .= "- Be specific and measurable\n";
        $prompt .= "- Be achievable within a lesson timeframe\n\n";
        $prompt .= "Format as a numbered list.";

        return $this->generateContentInternal($prompt);
    }

    /**
     * Public method for generic content generation
     */
    public function generateContent($prompt)
    {
        return $this->generateContentInternal($prompt);
    }

    /**
     * Generate content using Gemini API
     */
    protected function generateContentInternal($prompt)
    {
        try {
            // Validate API key
            if (!$this->apiKey) {
                Log::error('Gemini API key not configured');
                return [
                    'success' => false,
                    'error' => 'Gemini API key not configured. Please add GEMINI_API_KEY to your .env file.',
                ];
            }

            // Check cache to avoid repeated calls for same prompts
            $cacheKey = 'gemini_' . md5($prompt);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return [
                    'success' => true,
                    'content' => $cached,
                    'cached' => true,
                ];
            }

            // Try each model until one works
            $lastError = null;
            foreach ($this->models as $model) {
                try {
                    // Call Gemini API
                    $url = "{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}";
                    
                    $response = $this->client->post($url, [
                        'json' => [
                            'contents' => [
                                [
                                    'parts' => [
                                        [
                                            'text' => $prompt,
                                        ],
                                    ],
                                ],
                            ],
                            'generationConfig' => [
                                'temperature' => 0.7,
                                'maxOutputTokens' => 2000,
                                'topP' => 0.9,
                            ],
                        ],
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'timeout' => 30,
                    ]);

                    $body = json_decode($response->getBody(), true);

                    if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
                        $content = $body['candidates'][0]['content']['parts'][0]['text'];

                        // Cache the result for 24 hours
                        Cache::put($cacheKey, $content, now()->addHours(24));

                        Log::info("Gemini API succeeded with model: $model");
                        return [
                            'success' => true,
                            'content' => $content,
                            'cached' => false,
                            'model' => $model,
                        ];
                    }
                } catch (\Exception $e) {
                    $lastError = $e;
                    Log::warning("Model $model failed: " . $e->getMessage());
                    continue; // Try next model
                }
            }

            // All models failed
            if ($lastError) {
                Log::error('All Gemini models failed', [
                    'message' => $lastError->getMessage(),
                ]);
                return [
                    'success' => false,
                    'error' => 'No available Gemini model responded. Please verify your API key has access to at least one of: ' . implode(', ', $this->models),
                ];
            }

            Log::error('Unexpected Gemini API response');
            return [
                'success' => false,
                'error' => 'Unexpected API response - no text content received',
            ];
        } catch (RequestException $e) {
            Log::error('Gemini API request failed', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody(),
            ]);

            return [
                'success' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Gemini AI Service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if API is configured
     */
    public function isConfigured()
    {
        return !empty($this->apiKey);
    }
}
