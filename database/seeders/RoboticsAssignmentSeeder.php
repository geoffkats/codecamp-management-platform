<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class RoboticsAssignmentSeeder extends Seeder
{
    /**
     * Add assignment assessments (prompt question + file upload) to Robotics 1 and Robotics 2.
     *
     * Safe to re-run:
     * php artisan db:seed --class=RoboticsAssignmentSeeder
     */
    public function run(): void
    {
        $courses = [
            'robotics-for-mbot-programming' => 'mbot',
            'robotics-for-arduino-and-acebott' => 'arduino',
        ];

        $total = 0;

        foreach ($courses as $slug => $track) {
            $title = $track === 'mbot'
                ? 'Robotics for mBot Programming'
                : 'Robotics for Arduino and ACEBOTT';

            $course = Course::query()
                ->where(function ($query) use ($slug, $title) {
                    $query->where('slug', $slug)
                        ->orWhere('title', $title);
                })
                ->first();

            if (! $course) {
                $this->command->warn("Course not found for {$slug}. Run the robotics lesson seeder first.");
                continue;
            }

            $lessons = Lesson::query()
                ->where('course_id', $course->id)
                ->orderBy('order')
                ->get();

            if ($lessons->isEmpty()) {
                $this->command->warn("No lessons on {$course->title}. Seed lessons first.");
                continue;
            }

            foreach ($lessons as $lesson) {
                if ($this->seedAssignment($course, $lesson, $track)) {
                    $total++;
                    $this->command->info("Assignment ready: {$lesson->title}");
                }
            }
        }

        $this->command->info("Robotics assignments ready ({$total} assignment assessments).");
    }

    private function seedAssignment(Course $course, Lesson $lesson, string $track): bool
    {
        $brief = $this->briefForLesson($lesson->title, $track);
        $title = $brief['title'];

        $assessment = Assessment::updateOrCreate(
            [
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'assessment_type' => 'assignment',
                'title' => $title,
            ],
            [
                'description' => $brief['description'],
                'max_attempts' => 3,
                'time_limit_minutes' => null,
                'passing_score' => 60,
                'xp_reward' => $brief['xp'],
                'is_required' => true,
                'show_results_immediately' => false,
                'show_correct_answers' => false,
                'allow_review' => true,
                'is_randomized' => false,
                'shuffle_options' => false,
                'is_locked' => false,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'assignment_data' => [
                    'instructions' => $brief['instructions'],
                    'submission_format' => 'file',
                    'file_types' => $brief['file_types'],
                    'max_file_size' => 20,
                    'max_points' => 100,
                    'rubric' => [
                        'Program works as required' => 40,
                        'Code / blocks are clear' => 25,
                        'Testing notes / explanation' => 20,
                        'Creativity / polish' => 15,
                    ],
                ],
            ]
        );

        $prompt = Question::query()
            ->where('assessment_id', $assessment->id)
            ->where('question_type', 'short_answer')
            ->orderBy('order')
            ->first();

        $promptData = [
            'assessment_id' => $assessment->id,
            'question_text' => $brief['prompt_question'],
            'question_type' => 'short_answer',
            'points' => 20,
            'order' => 0,
            'explanation' => 'Teachers use this note while grading your upload.',
            'settings' => [
                'min_words' => 0,
                'max_words' => 500,
            ],
        ];

        if ($prompt) {
            $prompt->update($promptData);
        } else {
            Question::create($promptData);
        }

        $upload = Question::query()
            ->where('assessment_id', $assessment->id)
            ->where('question_type', 'file_upload')
            ->orderBy('order')
            ->first();

        $uploadData = [
            'assessment_id' => $assessment->id,
            'question_text' => $brief['upload_question'],
            'question_type' => 'file_upload',
            'points' => 80,
            'order' => 1,
            'explanation' => 'Upload your project file or a clear zip/screenshots of your program.',
            'settings' => [
                'allowed_types' => implode(',', $brief['file_types']),
                'max_size' => 20,
                'max_files' => 3,
            ],
        ];

        if ($upload) {
            $upload->update($uploadData);
        } else {
            Question::create($uploadData);
        }

        return true;
    }

    /**
     * @return array{
     *   title: string,
     *   description: string,
     *   instructions: string,
     *   prompt_question: string,
     *   upload_question: string,
     *   file_types: array<int, string>,
     *   xp: int
     * }
     */
    private function briefForLesson(string $lessonTitle, string $track): array
    {
        $mbotFiles = ['sb3', 'zip', 'png', 'jpg', 'jpeg', 'pdf', 'mp4'];
        $arduinoFiles = ['ino', 'zip', 'png', 'jpg', 'jpeg', 'pdf', 'txt', 'hex'];

        if ($track === 'mbot') {
            return $this->mbotBrief($lessonTitle, $mbotFiles);
        }

        return $this->arduinoBrief($lessonTitle, $arduinoFiles);
    }

    private function mbotBrief(string $lessonTitle, array $fileTypes): array
    {
        $defaults = [
            'title' => 'Assignment: '.$this->shortTitle($lessonTitle),
            'description' => '<p>Build and program your <strong>mBot</strong> for this lesson, then submit your mBlock project for grading.</p>',
            'instructions' => '<ol><li>Open mBlock 5 and connect your mBot.</li><li>Complete the coding challenge for this day.</li><li>Save your project (.sb3) or export a zip with screenshots.</li><li>Write a short note about how to test it, then upload your file.</li></ol>',
            'prompt_question' => 'In a few sentences: what does your mBot program do, and how should the teacher test it?',
            'upload_question' => 'Upload your mBlock project (.sb3) and/or a zip with clear screenshots of your blocks. You may also attach a short demo video.',
            'file_types' => $fileTypes,
            'xp' => 80,
        ];

        $map = [
            'Day 1' => [
                'title' => 'Assignment: Meet mBot — Assembled Robot Photo',
                'description' => '<p>Assemble your mBot safely and show you know the parts and factory modes.</p>',
                'instructions' => '<ol><li>Assemble mBot with your partner or alone.</li><li>Label or list: mCore, motors, ultrasonic, line follower, IR receiver.</li><li>Try factory modes A/B/C briefly.</li><li>Upload clear photos of the assembled robot (and optional short video of a factory mode).</li></ol>',
                'prompt_question' => 'Name three mBot parts you used today and one safety rule you followed.',
                'upload_question' => 'Upload photos (or a short video) of your assembled mBot.',
                'xp' => 50,
            ],
            'Day 2' => [
                'title' => 'Assignment: First mBlock Program',
                'description' => '<p>Connect mBot in mBlock 5 and submit your first Live/Upload program.</p>',
                'instructions' => '<ol><li>Connect mBot in Live mode.</li><li>Make the robot move or light up with blocks.</li><li>Save as .sb3 and upload.</li></ol>',
                'prompt_question' => 'Did you use Live mode, Upload mode, or both? What does your first program do?',
                'xp' => 70,
            ],
            'Day 3' => [
                'title' => 'Assignment: Motors — Drive a Shape',
                'description' => '<p>Program motors for speed, direction, and a simple path (square, triangle, or zigzag).</p>',
                'instructions' => '<ol><li>Use motor blocks for left/right wheels.</li><li>Drive a clear shape on the floor.</li><li>Upload your .sb3 (and optional video of the path).</li></ol>',
                'prompt_question' => 'Which shape did you program, and which motor speeds/times did you use?',
                'xp' => 80,
            ],
            'Day 4' => [
                'title' => 'Assignment: Lights, Buzzer, and Button',
                'description' => '<p>Combine RGB LEDs, buzzer, and the onboard button in one interactive program.</p>',
                'prompt_question' => 'What happens when someone presses the onboard button in your program?',
                'xp' => 80,
            ],
            'Day 5' => [
                'title' => 'Assignment: Dance Robot Showcase',
                'description' => '<p>Week 1 challenge — choreograph a short dance with motion, lights, and sound.</p>',
                'prompt_question' => 'Describe your dance sequence in order (steps 1–5).',
                'xp' => 100,
            ],
            'Day 6' => [
                'title' => 'Assignment: Ultrasonic Distance Display',
                'description' => '<p>Read the ultrasonic sensor and react when an object is close.</p>',
                'prompt_question' => 'At what distance (cm) does your robot change behaviour, and what does it do?',
                'xp' => 80,
            ],
            'Day 7' => [
                'title' => 'Assignment: Obstacle Avoidance Program',
                'description' => '<p>If distance is too small, stop/turn, then continue — classic mBot avoider.</p>',
                'prompt_question' => 'Explain your if/else logic for avoiding obstacles.',
                'xp' => 100,
            ],
            'Day 8' => [
                'title' => 'Assignment: Line Sensor Values 0–3',
                'description' => '<p>Show you can read line-follower values and map them to left/right/both/none.</p>',
                'prompt_question' => 'What do values 0, 1, 2, and 3 mean on your line sensor?',
                'xp' => 80,
            ],
            'Day 9' => [
                'title' => 'Assignment: Line Following Robot',
                'description' => '<p>Keep mBot on a black line track using the line-follower sensor.</p>',
                'prompt_question' => 'How does your program steer when it loses the line?',
                'xp' => 100,
            ],
            'Day 10' => [
                'title' => 'Assignment: IR Remote Control',
                'description' => '<p>Drive mBot with the IR remote and optionally combine with a sensor.</p>',
                'prompt_question' => 'Which remote buttons did you map, and to what actions?',
                'xp' => 90,
            ],
            'Day 11' => [
                'title' => 'Assignment: Light Sensor Behaviour',
                'description' => '<p>Seek light, avoid light, or night-light mode using the light sensor.</p>',
                'prompt_question' => 'Which light behaviour did you build (seek / avoid / night light), and why?',
                'xp' => 90,
            ],
            'Day 12' => [
                'title' => 'Assignment: Variables, Nested Ifs, and Loops',
                'description' => '<p>Use variables and nested decisions to make a smarter mBot program.</p>',
                'prompt_question' => 'Name one variable you created and how nested ifs improve your robot.',
                'xp' => 100,
            ],
            'Day 13' => [
                'title' => 'Assignment: Maze Challenge Program',
                'description' => '<p>Plan, test, and improve a maze solution with sensors and logic.</p>',
                'prompt_question' => 'What failed in your first maze attempt, and what did you change?',
                'xp' => 110,
            ],
            'Day 14' => [
                'title' => 'Assignment: Capstone Mission Design + Code',
                'description' => '<p>Design your final mBot mission and submit the working program draft.</p>',
                'prompt_question' => 'What is your capstone mission title, and what should judges look for?',
                'xp' => 120,
            ],
            'Day 15' => [
                'title' => 'Assignment: Demo Day Final Submission',
                'description' => '<p>Submit your final mBot project file and a short reflection after demo day.</p>',
                'prompt_question' => 'What are you most proud of, and what would you improve next?',
                'upload_question' => 'Upload your final .sb3 (or zip) plus optional demo photos/video.',
                'xp' => 150,
            ],
        ];

        $dayKey = $this->dayKey($lessonTitle);
        if ($dayKey && isset($map[$dayKey])) {
            $override = $map[$dayKey];

            return array_merge($defaults, $override, [
                'file_types' => $fileTypes,
                'upload_question' => $override['upload_question'] ?? $defaults['upload_question'],
                'instructions' => $override['instructions'] ?? $defaults['instructions'],
            ]);
        }

        return $defaults;
    }

    private function arduinoBrief(string $lessonTitle, array $fileTypes): array
    {
        $defaults = [
            'title' => 'Assignment: '.$this->shortTitle($lessonTitle),
            'description' => '<p>Complete today’s <strong>Arduino / ACEBOTT</strong> coding task and submit your sketch or ACECode/mBlock project for grading.</p>',
            'instructions' => '<ol><li>Build and test the circuit or robot behaviour for this lesson.</li><li>Save your Arduino sketch (.ino), ACECode/mBlock project, or a zip with code + photos.</li><li>Write a short test note, then upload.</li></ol>',
            'prompt_question' => 'In a few sentences: what does your program/circuit do, and how should the teacher test it?',
            'upload_question' => 'Upload your .ino / project file, or a zip with code and clear photos of the wiring or ACEBOTT demo.',
            'file_types' => $fileTypes,
            'xp' => 80,
        ];

        $map = [
            'Day 1' => [
                'title' => 'Assignment: Lab Kit Inventory Photo',
                'description' => '<p>Show you can identify Arduino boards and power safety rules before coding.</p>',
                'prompt_question' => 'Name the board you will use (Uno/Nano/ACEBOTT) and one power safety rule.',
                'upload_question' => 'Upload photos of your board + kit layout for today.',
                'xp' => 50,
            ],
            'Day 2' => [
                'title' => 'Assignment: Breadboard LED Circuit',
                'prompt_question' => 'Which resistor value did you use with the LED, and why?',
                'xp' => 70,
            ],
            'Day 3' => [
                'title' => 'Assignment: Blink — setup() and loop()',
                'prompt_question' => 'Explain what setup() runs once and what loop() repeats in your Blink sketch.',
                'xp' => 70,
            ],
            'Day 4' => [
                'title' => 'Assignment: Button + Serial Monitor',
                'prompt_question' => 'What message prints when the button is pressed in your program?',
                'xp' => 80,
            ],
            'Day 5' => [
                'title' => 'Assignment: Traffic Light Challenge',
                'prompt_question' => 'Describe your traffic light sequence and the pedestrian button behaviour.',
                'xp' => 100,
            ],
            'Day 6' => [
                'title' => 'Assignment: Analog Light + PWM',
                'prompt_question' => 'How does your sketch map light sensor values to LED brightness?',
                'xp' => 80,
            ],
            'Day 7' => [
                'title' => 'Assignment: HC-SR04 Distance Sketch',
                'prompt_question' => 'Which pins did you use for Trig and Echo, and what distance threshold did you choose?',
                'xp' => 90,
            ],
            'Day 8' => [
                'title' => 'Assignment: DHT + LCD Display',
                'prompt_question' => 'What values does your LCD show, and how often does it refresh?',
                'xp' => 90,
            ],
            'Day 9' => [
                'title' => 'Assignment: Sensor Tour Demo',
                'prompt_question' => 'Which sensor did you focus on, and what event does it detect?',
                'xp' => 80,
            ],
            'Day 10' => [
                'title' => 'Assignment: Night Security Station',
                'prompt_question' => 'When does your security station trigger, and what output turns on?',
                'xp' => 110,
            ],
            'Day 11' => [
                'title' => 'Assignment: DC Motor Power Test',
                'prompt_question' => 'How did you power the motor safely, and what did the multimeter show?',
                'xp' => 80,
            ],
            'Day 12' => [
                'title' => 'Assignment: L298N Drive Sketch',
                'prompt_question' => 'Which L298N pins control direction and PWM speed in your code?',
                'xp' => 100,
            ],
            'Day 13' => [
                'title' => 'Assignment: Servo Sweep / Pan',
                'prompt_question' => 'What angle range does your servo use, and what triggers movement?',
                'xp' => 80,
            ],
            'Day 14' => [
                'title' => 'Assignment: 4WD Chassis Assembly Check',
                'prompt_question' => 'Is your chassis ready to drive? List any wiring still incomplete.',
                'upload_question' => 'Upload photos of the assembled 4WD chassis (top and side).',
                'xp' => 70,
            ],
            'Day 15' => [
                'title' => 'Assignment: Arduino 4WD Drive Challenge',
                'prompt_question' => 'Which path did your 4WD complete (shapes / stop / avoid), and what still needs tuning?',
                'xp' => 110,
            ],
            'Day 16' => [
                'title' => 'Assignment: ACEBOTT V2 First Drive',
                'prompt_question' => 'Which control method did you use today (app / IR / ACECode), and what move did you demo?',
                'xp' => 90,
            ],
            'Day 17' => [
                'title' => 'Assignment: ACECode Blink / First Upload',
                'prompt_question' => 'Did you use Online mode or Upload mode? What did your first ACECode program do?',
                'xp' => 80,
            ],
            'Day 18' => [
                'title' => 'Assignment: ACEBOTT 4WD Motion Shapes',
                'prompt_question' => 'Which mecanum/drive moves did you program (forward, slide, spin)?',
                'xp' => 100,
            ],
            'Day 19' => [
                'title' => 'Assignment: ACEBOTT Line Tracking',
                'prompt_question' => 'How does your line program correct when the sensor leaves the line?',
                'xp' => 100,
            ],
            'Day 20' => [
                'title' => 'Assignment: Avoid + IR Override',
                'prompt_question' => 'Explain how obstacle avoid and IR override work together in your program.',
                'xp' => 110,
            ],
            'Day 21' => [
                'title' => 'Assignment: Bluetooth / App Custom Control',
                'prompt_question' => 'Which custom app buttons did you map to robot actions?',
                'xp' => 100,
            ],
            'Day 22' => [
                'title' => 'Assignment: Wi-Fi or RFID Smart Gate',
                'prompt_question' => 'Does your project use Wi-Fi control, RFID access, or both? How do you test it?',
                'xp' => 110,
            ],
            'Day 23' => [
                'title' => 'Assignment: Custom Robot Body Concept',
                'prompt_question' => 'Describe the body platform you chose (Superbot / CLB / LEGO / craft) and why.',
                'upload_question' => 'Upload photos of your custom body on the chassis plus any ACECode/Arduino file used.',
                'xp' => 90,
            ],
            'Day 24' => [
                'title' => 'Assignment: Invent a Gadget',
                'prompt_question' => 'What gadget did you invent, and which actuator/sensor makes it useful?',
                'xp' => 120,
            ],
            'Day 25' => [
                'title' => 'Assignment: Robotics 2 Capstone Final Submit',
                'description' => '<p>Submit your final Robotics 2 mission code and reflection after demo day.</p>',
                'prompt_question' => 'What is your capstone title, and what should judges see in the demo?',
                'upload_question' => 'Upload final .ino / ACECode project / zip, plus optional demo photos or video.',
                'xp' => 150,
            ],
        ];

        $dayKey = $this->dayKey($lessonTitle);
        if ($dayKey && isset($map[$dayKey])) {
            $override = $map[$dayKey];

            return array_merge($defaults, $override, [
                'file_types' => $fileTypes,
                'upload_question' => $override['upload_question'] ?? $defaults['upload_question'],
                'instructions' => $override['instructions'] ?? $defaults['instructions'],
            ]);
        }

        return $defaults;
    }

    private function dayKey(string $lessonTitle): ?string
    {
        if (preg_match('/^Day\s+(\d+)\s*:/i', $lessonTitle, $matches)) {
            return 'Day '.(int) $matches[1];
        }

        return null;
    }

    private function shortTitle(string $lessonTitle): string
    {
        return trim(preg_replace('/^Day\s+\d+:\s*/i', '', $lessonTitle) ?? $lessonTitle);
    }
}
