<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MbotRoboticsLessonSeeder extends Seeder
{
    /**
     * Robotics 1 — Robotics for mBot Programming
     *
     * 3 weeks × 5 days × 2 hours. Safe to re-run on production:
     * php artisan db:seed --class=MbotRoboticsLessonSeeder
     */
    public function run(): void
    {
        $instructor = $this->resolveInstructor();

        if (! $instructor) {
            $this->command->error('No users found. Create at least one teacher or admin before seeding this course.');

            return;
        }

        $course = $this->seedCourse($instructor);
        $this->command->info("Using course: {$course->title} (ID {$course->id})");

        $globalOrder = 1;

        foreach ($this->weeks() as $weekIndex => $week) {
            $module = CourseModule::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'title' => $week['title'],
                ],
                [
                    'description' => $week['description'],
                    'overview' => $week['overview'],
                    'order_index' => $weekIndex + 1,
                    'estimated_duration_hours' => 10,
                    'is_active' => true,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $course->approved_by ?: $course->instructor_id,
                ]
            );

            foreach ($week['lessons'] as $lessonIndex => $lessonData) {
                $lesson = $this->seedLesson($course, $module, $lessonIndex, $globalOrder, $lessonData);
                $this->seedQuiz($lesson, $lessonData['quiz']);
                $this->command->info("Seeded lesson {$globalOrder}: {$lesson->title}");
                $globalOrder++;
            }
        }

        $this->command->info('Robotics for mBot Programming is ready (15 lessons, 15 quizzes, 150 questions).');

        $this->call(RoboticsAssignmentSeeder::class);
    }

    private function seedCourse(User $instructor): Course
    {
        $existing = Course::query()
            ->where('slug', 'robotics-for-mbot-programming')
            ->orWhere('title', 'Robotics for mBot Programming')
            ->first();

        $payload = [
            'title' => 'Robotics for mBot Programming',
            'slug' => 'robotics-for-mbot-programming',
            'description' => '<p><strong>Robotics 1</strong> is a 3-week, 30-hour beginner course on the Makeblock mBot. Students assemble the robot, learn mBlock 5, then program motors, lights, sound, and sensors. By the end of the course they can build obstacle-avoiding and line-following robots and present a capstone project.</p><p>The course follows Makeblock\'s beginner mBot path: factory firmware modes, mCore (Arduino Uno), RJ25 ports, ultrasonic and line-follower modules, then live and upload programming in mBlock.</p>',
            'short_description' => 'Robotics 1: build and program Makeblock mBot with mBlock over 3 weeks (5 days × 2 hours).',
            'difficulty_level' => 'Beginner',
            'estimated_duration' => 30,
            'category' => 'STEM',
            'tags' => ['robotics', 'mbot', 'mblock', 'makeblock', 'stem', 'arduino', 'sensors', 'coding'],
            'requirements' => [
                'Makeblock mBot kit (one per student or pair)',
                'Laptop with mBlock 5 installed',
                '4 AA batteries or a charged 3.7V LiPo pack',
                'USB cable from the kit',
                'Black electrical tape and a few classroom obstacles',
                'No prior coding experience required',
            ],
            'what_you_learn' => [
                'Name mBot parts and assemble the robot safely',
                'Use factory modes A, B, and C with the onboard button and IR remote',
                'Program mBot in mBlock 5 (Live and Upload)',
                'Control motors, RGB LEDs, and the buzzer',
                'Read ultrasonic, line-follower, light, and IR sensors',
                'Build obstacle-avoiding and line-following behaviours',
                'Use loops, if/else, wait until, and variables',
                'Debug ports, firmware, and wiring',
                'Design, build, and demo a capstone robotics project',
            ],
            'price' => 0.00,
            'is_published' => true,
            'enrollment_type' => 'invite_only',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $instructor->id,
        ];

        if ($existing) {
            $existing->fill($payload);
            if (! $existing->instructor_id) {
                $existing->instructor_id = $instructor->id;
            }
            $existing->save();

            return $existing;
        }

        $payload['instructor_id'] = $instructor->id;

        return Course::create($payload);
    }

    private function seedLesson(Course $course, CourseModule $module, int $lessonIndex, int $globalOrder, array $lessonData): Lesson
    {
        $slug = Str::slug($lessonData['title']);

        return Lesson::updateOrCreate(
            [
                'course_id' => $course->id,
                'slug' => $slug,
            ],
            [
                'module_id' => $module->id,
                'title' => $lessonData['title'],
                'content' => $lessonData['content'],
                'summary' => $lessonData['summary'],
                'objectives' => $lessonData['objectives'],
                'implementation_guidance' => $lessonData['guidance'],
                'lesson_type' => 'text',
                'difficulty_level' => $lessonData['difficulty'] ?? 'beginner',
                'duration_minutes' => 120,
                'order' => $globalOrder,
                'order_index' => $lessonIndex + 1,
                'is_published' => true,
                'is_free_preview' => $globalOrder === 1,
                'is_locked' => false,
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $course->approved_by ?: $course->instructor_id,
            ]
        );
    }

    private function seedQuiz(Lesson $lesson, array $quizData): void
    {
        $assessment = Assessment::updateOrCreate(
            [
                'course_id' => $lesson->course_id,
                'lesson_id' => $lesson->id,
                'title' => $quizData['title'],
            ],
            [
                'assessment_type' => 'quiz',
                'description' => $quizData['description'],
                'max_attempts' => 3,
                'time_limit_minutes' => 20,
                'passing_score' => 70,
                'xp_reward' => 50,
                'is_required' => true,
                'show_results_immediately' => true,
                'show_correct_answers' => false,
                'allow_review' => true,
                'is_randomized' => false,
                'shuffle_options' => true,
                'is_locked' => false,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        Question::where('assessment_id', $assessment->id)->each(function (Question $question) {
            $question->options()->delete();
            $question->delete();
        });

        foreach (array_slice($quizData['questions'], 0, 10) as $index => $questionData) {
            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'order' => $index,
                'explanation' => $questionData['explanation'] ?? null,
            ]);

            foreach ($questionData['options'] as $optIndex => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $optIndex,
                ]);
            }
        }
    }

    private function resolveInstructor(): ?User
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['teacher', 'ict_teacher', 'admin']);
        })->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
    }

    private function mc(string $text, string $correct, array $wrongs, int $points = 10): array
    {
        $options = [['option_text' => $correct, 'is_correct' => true]];
        foreach ($wrongs as $wrong) {
            $options[] = ['option_text' => $wrong, 'is_correct' => false];
        }

        return [
            'question_text' => $text,
            'question_type' => 'multiple_choice',
            'points' => $points,
            'options' => $options,
        ];
    }

    private function tf(string $text, bool $isTrue, int $points = 10): array
    {
        return [
            'question_text' => $text,
            'question_type' => 'true_false',
            'points' => $points,
            'options' => [
                ['option_text' => 'True', 'is_correct' => $isTrue],
                ['option_text' => 'False', 'is_correct' => ! $isTrue],
            ],
        ];
    }

    private function weeks(): array
    {
        return [
            [
                'title' => 'Week 1 — Meet mBot, mBlock, and Motion',
                'description' => 'Assemble mBot, learn factory modes, install mBlock 5, then program motors, RGB LEDs, and the buzzer.',
                'overview' => 'Days 1–5: robot parts, safety, firmware, first programs, driving, lights and sound, then a dance-robot challenge.',
                'lessons' => $this->week1Lessons(),
            ],
            [
                'title' => 'Week 2 — Sensors and Smart Behaviours',
                'description' => 'Read the ultrasonic and line-follower sensors, then build obstacle avoidance, line following, and IR remote control.',
                'overview' => 'Days 6–10: distance, decisions, line values 0–3, following algorithms, and combining the IR remote with sensors.',
                'lessons' => $this->week2Lessons(),
            ],
            [
                'title' => 'Week 3 — Logic, Challenges, and Capstone',
                'description' => 'Add the light sensor, variables and nested logic, then complete a maze challenge and a capstone demo.',
                'overview' => 'Days 11–15: light-seeking, debugging, maze, capstone build, and presentation day.',
                'lessons' => $this->week3Lessons(),
            ],
        ];
    }

    private function week1Lessons(): array
    {
        return [
            [
                'title' => 'Day 1: Meet mBot — Parts, Safety, and Factory Modes',
                'summary' => 'Name every major mBot part, assemble the robot, and try factory modes A, B, and C.',
                'difficulty' => 'beginner',
                'objectives' => "Name the mCore, motors, sensors, and ports on mBot\nAssemble the chassis, wheels, and modules with the kit screwdriver\nExplain the difference between sensors, actuators, and the controller\nSwitch factory firmware modes A (manual), B (obstacle avoidance), and C (line following)\nFollow battery and cable safety rules",
                'guidance' => "Materials: one mBot kit per pair, 4 AA or charged LiPo, screwdriver, printed part map, IR remote, black tape line, a box for Mode B.\n2-hour flow:\n0:00–0:15 Welcome, what a robot is, show a working mBot in Mode B.\n0:15–0:40 Unbox and name parts. Match colour tags on RJ25 cables to ports.\n0:40–1:20 Guided assembly (chassis, motors M1/M2, caster, ultrasonic Port 3, line follower Port 2).\n1:20–1:45 Power on, onboard button cycles modes, IR remote in Mode A, tape line for Mode C.\n1:45–2:00 Quiz, pack batteries out, count screws.\nCoach: do not skip colour tags. Yellow = single digital (ultrasonic). Blue = dual digital (line follower).",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>By the end of this session you can name the parts of a Makeblock <strong>mBot</strong>, put the robot together safely, and drive it with the <strong>factory firmware</strong> — no coding yet.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> What is a robot? Demo mBot avoiding a bag.</li>
  <li><strong>0:15–0:40</strong> Unbox. Name the brain, motors, sensors, and cables.</li>
  <li><strong>0:40–1:20</strong> Build: chassis, wheels, sensors on the correct ports.</li>
  <li><strong>1:20–1:45</strong> Factory modes A, B, and C.</li>
  <li><strong>1:45–2:00</strong> 10-question quiz and pack-up.</li>
</ul>

<h2>What is mBot?</h2>
<p>mBot is Makeblock's beginner STEM robot. Children build it with a screwdriver, then program it with <strong>mBlock</strong> (block coding) or later Arduino C. The metal body is aluminium. The brain is the <strong>mCore</strong> board, which is based on the <strong>Arduino Uno</strong> and runs at <strong>16 MHz</strong>.</p>

<h3>The three parts of every robot</h3>
<ul>
  <li><strong>Sensors</strong> — collect information (distance, line, light, button, IR).</li>
  <li><strong>Controller</strong> — the mCore thinks and decides.</li>
  <li><strong>Actuators</strong> — motors, RGB LEDs, and the buzzer do something in the world.</li>
</ul>

<h2>Know your kit</h2>
<ul>
  <li><strong>mCore</strong> — 4 sensor ports, 2 motor ports (M1 left, M2 right), 2 onboard RGB LEDs, buzzer, light sensor, IR transmitter/receiver, onboard button.</li>
  <li><strong>Me Ultrasonic Sensor</strong> — distance 3–400 cm. Yellow tag = single digital. Default: <strong>Port 3</strong>.</li>
  <li><strong>Me Line Follower</strong> — two IR probes for a black line on white (or the reverse). Blue tag = dual digital. Default: <strong>Port 2</strong>.</li>
  <li><strong>Two DC geared motors</strong> — about 200 RPM. Connect to M1 and M2.</li>
  <li><strong>Power</strong> — 4 AA batteries or a 3.7 V LiPo (~1800 mAh, about 1 hour of play). Batteries are often not in the box.</li>
  <li><strong>Communication</strong> — USB, Bluetooth, or 2.4G dongle.</li>
</ul>

<h3>Colour tags (do not mix these up)</h3>
<ul>
  <li><strong>Yellow</strong> — single digital (ultrasonic).</li>
  <li><strong>Blue</strong> — dual digital (line follower).</li>
  <li>Always plug a yellow cable into a yellow port, blue into blue.</li>
</ul>

<h2>Factory firmware vs online firmware</h2>
<p>The robot ships with <strong>factory firmware</strong>: three ready-made modes you switch with the onboard button or the IR remote.</p>
<ul>
  <li><strong>Mode A — Manual</strong> — drive with the IR remote.</li>
  <li><strong>Mode B — Obstacle avoidance</strong> — ultrasonic looks ahead and the robot turns away from objects.</li>
  <li><strong>Mode C — Line following</strong> — the robot tracks a dark line on a light floor.</li>
</ul>
<p><strong>Online firmware</strong> is for live programming from mBlock. It frees memory and supports more sensors, but it does <em>not</em> include the three preset modes. After you upload your own program, factory modes are gone until you restore factory firmware.</p>

<h2>Safety</h2>
<ul>
  <li>Screwdrivers stay on the table, not in pockets.</li>
  <li>Insert batteries with the correct +/−. Switch the robot off before plugging USB if the teacher asks you to.</li>
  <li>Do not lift mBot by the wires or ultrasonic "eyes".</li>
  <li>Keep fingers away from spinning wheels.</li>
  <li>At pack-up: power off, remove batteries if the camp requires it, coil the USB cable.</li>
</ul>

<h2>Build checklist</h2>
<ol>
  <li>Motors on the chassis. Left motor cable to <strong>M1</strong>, right to <strong>M2</strong>.</li>
  <li>Caster wheel at the front, drive wheels at the back (or follow the paper manual in the box).</li>
  <li>mCore on top, USB facing a clear edge.</li>
  <li>Line follower under the front, facing the floor, <strong>Port 2</strong>.</li>
  <li>Ultrasonic facing forward, <strong>Port 3</strong>.</li>
  <li>Power on. Press the onboard button to cycle modes. Try the remote in Mode A.</li>
</ol>

<h3>Try this</h3>
<ol>
  <li>Mode A: drive a square using only the remote.</li>
  <li>Mode B: make a "city" of bags and watch the robot choose a path.</li>
  <li>Mode C: stick a black tape loop on the floor and see if both probes can see the line.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 1 — Meet mBot Quiz',
                    'description' => 'Parts, ports, firmware modes, and safety.',
                    'questions' => [
                        $this->mc('Which company makes mBot?', 'Makeblock', ['Arduino', 'LEGO', 'Raspberry Pi']),
                        $this->mc('What is the name of mBot\'s main control board?', 'mCore', ['Raspberry Pi', 'micro:bit', 'UNO clone shield']),
                        $this->mc('The mCore is based on which microcontroller platform?', 'Arduino Uno', ['BBC micro:bit', 'ESP32 only', 'PlayStation']),
                        $this->mc('Factory firmware Mode A is for:', 'Manual driving with the IR remote', ['Only line following', 'Only obstacle avoidance', 'Uploading Python']),
                        $this->mc('Factory firmware Mode B is for:', 'Obstacle avoidance', ['Playing music only', 'Charging the battery', 'Updating Windows']),
                        $this->mc('Factory firmware Mode C is for:', 'Line following', ['Turning the LEDs off forever', 'Bluetooth pairing only', 'Factory reset']),
                        $this->mc('By default the Me Line Follower should be plugged into:', 'Port 2 (blue / dual digital)', ['Port 3 (yellow)', 'Motor port M1', 'The USB port']),
                        $this->mc('By default the Me Ultrasonic Sensor should be plugged into:', 'Port 3 (yellow / single digital)', ['Port 2 (blue)', 'Motor port M2', 'The IR remote']),
                        $this->mc('Which list is the correct robot trio?', 'Sensors, controller, actuators', ['Wheels, stickers, remote only', 'USB, Wi-Fi, HDMI', 'Battery, paint, speaker only']),
                        $this->tf('Factory firmware includes three preset modes (manual, obstacle avoidance, line following).', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 2: mBlock 5 — Connect, Live Mode, and Your First Program',
                'summary' => 'Install the mBot device in mBlock 5, connect over USB, and upload a first LED program.',
                'difficulty' => 'beginner',
                'objectives' => "Add mBot as the device in mBlock 5\nExplain Live (online) mode versus Upload mode\nStart every mBot program with the when mBot(mCore) starts up hat block\nLight an onboard RGB LED and wait\nRestore or replace firmware when live control is needed",
                'guidance' => "Materials: assembled mBot, USB, laptops with mBlock 5 (Windows/macOS/Chromebook). Install Makeblock USB serial driver before class if needed.\n2-hour flow:\n0:00–0:15 Recap parts. Show mBlock 5 layout: devices, palettes, scripts, stage.\n0:15–0:40 Add device mBot, delete leftover Codey if it confuses students. USB connect.\n0:40–1:15 First program: when mBot starts up → set LED to red → wait → black. Upload.\n1:15–1:45 Live vs Upload. Traffic-light LED sequence. Troubleshooting: port, firmware, cable.\n1:45–2:00 Quiz and save the project as Day2-LED.\nCoach: hat block is required in Upload mode. If factory modes vanish after upload, that is expected.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>You will program mBot with <strong>mBlock 5</strong>: add the mBot device, connect with USB, and upload a program that lights the onboard RGB LEDs.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Recap yesterday. Open mBlock 5 together.</li>
  <li><strong>0:15–0:40</strong> Devices tab: add mBot. Connect USB.</li>
  <li><strong>0:40–1:15</strong> First script: hat block + LED + wait.</li>
  <li><strong>1:15–1:45</strong> Live mode vs Upload mode. Traffic-light challenge.</li>
  <li><strong>1:45–2:00</strong> Quiz and save.</li>
</ul>

<h2>What is mBlock?</h2>
<p>mBlock is Makeblock's free editor. For mBot you use <strong>block-based</strong> coding (like Scratch). Later you can show the Arduino C that the blocks compile to. mBlock also runs on Windows, macOS, Linux, and there are tablet apps (<strong>mBlock Blockly</strong> / Makeblock apps) if a laptop fails.</p>

<h3>Set up (every class)</h3>
<ol>
  <li>Open mBlock 5.</li>
  <li>Devices tab → <strong>+</strong> → choose <strong>mBot</strong> → OK. Remove Codey Rocky if it is in the way.</li>
  <li>Connect the USB cable. Select the correct serial port if asked.</li>
  <li>For programs that stay on the robot after unplugging, switch to <strong>Upload mode</strong>.</li>
</ol>

<h2>Live mode vs Upload mode</h2>
<ul>
  <li><strong>Live / online</strong> — the computer talks to mBot in real time. Useful for testing. Often needs <strong>online firmware</strong>. Factory modes A/B/C are not included in online firmware.</li>
  <li><strong>Upload</strong> — your script is compiled and stored on mCore. Unplug USB and the robot still runs it when you power on. This overwrites the previous uploaded program (and factory modes until you restore them).</li>
</ul>
<p>Official getting-started guides say: when you write an upload program, always start with the hat block <strong>when mBot(mCore) starts up</strong>.</p>

<pre>when mBot(mCore) starts up
set LED 1 to (red)
wait (1) seconds
set LED 1 to (black / off)
</pre>

<h3>Block palettes you will use this week</h3>
<ul>
  <li><strong>Events</strong> — hat blocks (start up, button pressed).</li>
  <li><strong>Control</strong> — wait, forever, if/else, repeat.</li>
  <li><strong>Looks / LED</strong> — onboard RGB LEDs.</li>
  <li><strong>Motors</strong> — move, turn, stop, speed 0–255.</li>
  <li><strong>Sound</strong> — buzzer / play note.</li>
  <li><strong>Sensing</strong> — ultrasonic, line follower, light, button, IR, timer.</li>
</ul>

<h2>Connection options</h2>
<ul>
  <li><strong>USB</strong> — most reliable in camp. Use this first.</li>
  <li><strong>Bluetooth</strong> or <strong>2.4G</strong> — wireless after the class has USB working.</li>
</ul>

<h2>Traffic-light challenge</h2>
<ol>
  <li>LED 1 red for 2 seconds, yellow 1 second, green 2 seconds, then off.</li>
  <li>Repeat the sequence 3 times, then stop.</li>
  <li>Stretch: LED 1 and LED 2 alternate colours.</li>
</ol>

<h3>If it does not connect</h3>
<ul>
  <li>Wrong device selected (Codey instead of mBot).</li>
  <li>USB driver missing on Windows.</li>
  <li>Cable is charge-only (try the kit cable).</li>
  <li>Need to update / flash firmware from mBlock's connect panel.</li>
  <li>Forgot the hat block in Upload mode — nothing runs.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 2 — mBlock 5 Quiz',
                    'description' => 'Devices, modes, hat blocks, and first LED programs.',
                    'questions' => [
                        $this->mc('Which app do we use to program mBot with blocks on a computer?', 'mBlock 5', ['Minecraft', 'Excel', 'Photoshop']),
                        $this->mc('In the Devices tab you should add:', 'mBot', ['Only Codey Rocky', 'A printer', 'A drone']),
                        $this->mc('Every uploaded mBot script should start with:', 'when mBot(mCore) starts up', ['when green flag clicked only (Scratch sprite)', 'void Python main', 'print Hello']),
                        $this->mc('Upload mode means:', 'The program is stored on mCore and can run after USB is unplugged', ['The robot only works while you hold the cable', 'Blocks are emailed to Makeblock', 'The battery charges faster']),
                        $this->mc('Live / online mode is best for:', 'Testing blocks in real time from the computer', ['Storing a program for a competition with no laptop', 'Replacing the motors', 'Printing stickers']),
                        $this->mc('Online firmware is different from factory firmware because it:', 'Drops the three preset A/B/C modes to free memory for live coding', ['Adds three extra factory games', 'Removes the USB port', 'Changes the robot into mBot2 automatically']),
                        $this->mc('How many onboard RGB LEDs does mCore typically have?', '2', ['0', '8', '16']),
                        $this->mc('The most reliable first connection in class is:', 'USB cable', ['Shouting at the robot', 'HDMI', 'Ethernet to the ultrasonic sensor']),
                        $this->mc('mBlock block coding is most similar to:', 'Scratch (drag-and-drop blocks)', ['Only raw machine code', 'SQL databases', 'Photoshop layers']),
                        $this->tf('After you upload your own program, the original factory modes A, B, and C stay on the robot automatically.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 3: Motors — Speed, Direction, and Driving Shapes',
                'summary' => 'Drive mBot forward, back, and turn using motor blocks and wait times.',
                'difficulty' => 'beginner',
                'objectives' => "Identify M1 as the left motor and M2 as the right motor\nSet speed from 0 to 255 and stop the motors\nUse wait blocks to control how long a movement lasts\nProgram forward, backward, left, and right turns\nDrive a square and a triangle by timing",
                'guidance' => "Materials: open floor, tape start line, cones. Mark a 40 cm square on the floor.\n2-hour flow:\n0:00–0:15 Recap LEDs. Demo one working drive program.\n0:15–0:40 Motor ports, speed, stop. If it drives backward, swap M1/M2 or invert direction in blocks.\n0:40–1:20 Guided: forward 1 s, stop; then square.\n1:20–1:45 Challenges: triangle, slalom, stop on button.\n1:45–2:00 Quiz. Remind students speed 255 is fast — start at 100.\nCoach: wait time is not exact distance; batteries and floor change results. Celebrate tuning.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Make mBot <strong>move with code</strong>: forward, back, turns, then a square. Motors are actuators. You already know the controller (mCore). Today the wheels do the work.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Recap hat block. Demo a slow forward crawl.</li>
  <li><strong>0:15–0:40</strong> M1 / M2, speed 0–255, stop.</li>
  <li><strong>0:40–1:20</strong> Guided square.</li>
  <li><strong>1:20–1:45</strong> Shape and slalom challenges.</li>
  <li><strong>1:45–2:00</strong> Quiz and pack-up.</li>
</ul>

<h2>Motor facts</h2>
<ul>
  <li>Left motor → <strong>M1</strong>. Right motor → <strong>M2</strong>.</li>
  <li>Typical kit speed is about <strong>200 RPM</strong> at the gearbox.</li>
  <li>In mBlock, movement blocks use speed <strong>0–255</strong> (0 is stop, 255 is full).</li>
  <li>Use a <strong>wait</strong> block after "run forward" so the robot has time to travel. Then <strong>stop</strong>.</li>
</ul>

<pre>when mBot(mCore) starts up
run forward at speed (100)
wait (1) seconds
stop
</pre>

<h3>Turns</h3>
<ul>
  <li><strong>Swing turn</strong> — one wheel moves, the other stops. Gentle, larger arc.</li>
  <li><strong>Pivot / spin</strong> — wheels in opposite directions. Tight turn, good for squares.</li>
  <li>mBlock also has helper blocks such as "turn left" / "turn right" for a set time or heading, depending on version.</li>
</ul>

<pre>when mBot(mCore) starts up
repeat (4)
  run forward at speed (120)
  wait (1) seconds
  turn right at speed (120)
  wait (0.4) seconds
stop
</pre>
<p>Tune the wait times until your square looks square on <em>this</em> floor with <em>these</em> batteries.</p>

<h2>If it drives the wrong way</h2>
<ul>
  <li>M1 and M2 cables swapped.</li>
  <li>A motor mounted facing the wrong direction — invert that motor in software or remount.</li>
  <li>Speed so high it skids — drop to 80–120 for precision.</li>
</ul>

<h2>Practice (starter → stretch)</h2>
<ol>
  <li><strong>Starter:</strong> forward 1 second, stop. Then backward 1 second, stop.</li>
  <li><strong>Core:</strong> drive a square that returns to the tape start line.</li>
  <li><strong>Stretch:</strong> equilateral triangle, or a slalom around 3 cones without touching.</li>
  <li><strong>Bonus:</strong> onboard button starts the run so you can place the robot first.</li>
</ol>

<h3>Teacher checkpoint</h3>
<p>Every pair should have a saved project <strong>Day3-Square</strong> that stops at the end (motors must not run forever).</p>
HTML,
                'quiz' => [
                    'title' => 'Day 3 — Motors Quiz',
                    'description' => 'Ports, speed, turning, and timed driving.',
                    'questions' => [
                        $this->mc('On a standard mBot, the left motor is connected to:', 'M1', ['Port 2', 'Port 3', 'The buzzer']),
                        $this->mc('On a standard mBot, the right motor is connected to:', 'M2', ['Port 1 only', 'The USB port', 'The IR receiver']),
                        $this->mc('Typical mBlock motor speed values are:', '0 to 255', ['0 to 10 only', '1 to 1000 volts', 'Negative kilometres only']),
                        $this->mc('A speed of 0 usually means:', 'The motor stops', ['Maximum turbo', 'Turn on the ultrasonic', 'Factory Mode C']),
                        $this->mc('Why do we use a wait block after "run forward"?', 'To control how long the motors run before the next command', ['To charge the LiPo', 'To change Port 2 to yellow', 'To delete firmware']),
                        $this->mc('A pivot turn usually means:', 'Wheels move in opposite directions so the robot spins in place', ['Both wheels always stop', 'Only the caster wheel is powered', 'The ultrasonic is unplugged']),
                        $this->mc('If mBot drives backward when you use "run forward", a likely cause is:', 'M1/M2 wires swapped or a motor mounted reversed', ['The line follower is on Port 3', 'mBlock is in Fahrenheit', 'The hat block is named wrong']),
                        $this->mc('For accurate squares in class you should usually start with:', 'A medium speed such as 80–120 and then tune wait times', ['Always 255 and hope', 'Speed 0 forever', 'Unplugging one motor']),
                        $this->mc('Motors are an example of:', 'Actuators', ['Sensors only', 'The mCore CPU', 'The USB driver']),
                        $this->tf('A wait time that draws a perfect square on one floor will always be perfect on every floor and battery level.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 4: Lights, Buzzer, and the Onboard Button',
                'summary' => 'Combine RGB LEDs, the buzzer, and the onboard button with motion.',
                'difficulty' => 'beginner',
                'objectives' => "Set onboard RGB LED colour and turn LEDs off\nPlay tones on the mCore buzzer\nUse the onboard button as an input (sensor)\nCombine outputs: light + sound + a short move\nExplain input–process–output with a real mBot script",
                'guidance' => "Materials: quiet-ish room for buzzers, or keep volume short notes only.\n2-hour flow:\n0:00–0:15 Recap square. IPO diagram on the board: button → mCore → LED/buzzer/motors.\n0:15–0:40 LED colours, off, both LEDs.\n0:40–1:15 Buzzer notes / play tone. Button wait until.\n1:15–1:45 Mini show: press button, lights, beep, nod forward, stop.\n1:45–2:00 Quiz. Save Day4-ButtonShow.\nCoach: forever loops need a way to stop (button, or finite repeat) so packing up is safe.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>mCore already includes <strong>two RGB LEDs</strong>, a <strong>buzzer</strong>, a <strong>light sensor</strong>, and an <strong>onboard button</strong>. Today you use lights, sound, and the button — still without the ultrasonic or line follower.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Input–Process–Output. Button is an input.</li>
  <li><strong>0:15–0:40</strong> RGB LED colours and off.</li>
  <li><strong>0:40–1:15</strong> Buzzer. Wait until button pressed.</li>
  <li><strong>1:15–1:45</strong> Combine with a tiny move ("nod").</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Input–process–output on mBot</h2>
<ul>
  <li><strong>Input:</strong> onboard button (pressed or not).</li>
  <li><strong>Process:</strong> your blocks on mCore.</li>
  <li><strong>Output:</strong> LED colour, buzzer note, motor motion.</li>
</ul>

<pre>when mBot(mCore) starts up
wait until (onboard button pressed)
set LED 1 to (green)
play tone (C4) for (0.5) beats
run forward at speed (80)
wait (0.3) seconds
stop
set LED 1 to (black)
</pre>

<h3>RGB LEDs</h3>
<p>Each onboard LED can be red, green, blue, or mixed colours (yellow, purple, white) depending on the block. Set colour to <strong>black</strong> or "off" when you finish so the robot does not stay bright in the bag.</p>

<h3>Buzzer</h3>
<p>The buzzer is an onboard actuator. Short notes are enough. A forever loop of loud tones is not kind to the next table.</p>

<h3>Onboard button</h3>
<p>Treat the button like a sensor that reports pressed / not pressed. Useful patterns:</p>
<ul>
  <li><strong>wait until button pressed</strong> — place the robot, then start.</li>
  <li><strong>if button pressed then stop</strong> — emergency stop in a forever drive.</li>
</ul>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> button press turns LED 1 blue for 2 seconds.</li>
  <li><strong>Core:</strong> button starts a 3-note tune and a green blink.</li>
  <li><strong>Stretch:</strong> button starts a "nod" (tiny forward/back) with lights; second press stops a forever blink.</li>
</ol>

<h2>Common mistakes</h2>
<ul>
  <li>No hat block — nothing runs after upload.</li>
  <li>LED never turned off.</li>
  <li>Forever + motors and no stop condition.</li>
  <li>Using the IR remote buttons today — save that for Week 2. Today is the <em>onboard</em> button.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 4 — Lights, Sound, Button Quiz',
                    'description' => 'LEDs, buzzer, button, and IPO.',
                    'questions' => [
                        $this->mc('The onboard button is best described as a:', 'Sensor (input)', ['Motor', 'Battery pack', 'Ultrasonic speaker']),
                        $this->mc('The buzzer is best described as an:', 'Actuator (output)', ['Line-follower probe', 'RJ25 yellow tag', 'USB driver']),
                        $this->mc('RGB LEDs can typically show:', 'Red, green, blue, and mixed colours', ['Only black and white photos', 'Only Morse code via USB', 'Only Port 2 values']),
                        $this->mc('A good way to start a run after you place the robot is:', 'wait until onboard button pressed', ['Unplug M1 forever', 'Remove the caster wheel', 'Set speed to 0 then 0 again']),
                        $this->mc('In input–process–output, mCore is the:', 'Process / controller', ['Only a wheel', 'Only the tape on the floor', 'The IR remote battery']),
                        $this->mc('Why set the LED to black/off at the end of a show?', 'So the lights do not stay on in the bag or distract the next test', ['To erase mBlock', 'To switch to Mode C', 'To charge the ultrasonic']),
                        $this->mc('Which palette is most likely to contain wait, forever, and if?', 'Control', ['Motors only', 'Sensing only', 'Devices tab printers']),
                        $this->mc('A forever loop that drives motors should include:', 'A way to stop (button, sensor, or finite design)', ['No stop ever, as a rule', 'A second mCore', 'Port 3 unplugged']),
                        $this->mc('mCore includes which onboard extras besides motors?', 'RGB LEDs, buzzer, light sensor, IR, and a button', ['A GPS satellite', 'A touchscreen', 'Four servo horns only']),
                        $this->tf('The onboard button and the IR remote are the same sensor.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 5: Week 1 Challenge — Dance Robot Showcase',
                'summary' => 'Combine movement, lights, and sound into a 20-second choreographed routine, then review Week 1.',
                'difficulty' => 'beginner',
                'objectives' => "Plan a short routine with a beginning, middle, and end\nReuse Week 1 blocks: motors, wait, LED, buzzer, button\nTest, tune wait times, and stop safely\nPresent a 20-second demo to another pair\nRecall parts, ports, modes, and mBlock setup",
                'guidance' => "Materials: performance lane, speaker optional (robot buzzer is enough), judging card: starts on button, 20 seconds, ends stopped, lights used.\n2-hour flow:\n0:00–0:15 Week 1 recap quiz-style oral: ports, modes, hat block.\n0:15–0:30 Design on paper: 6–8 steps.\n0:30–1:20 Build and tune. Teacher circulates for swapped motors and missing stop.\n1:20–1:45 Pair demos (20 seconds each).\n1:45–2:00 Written quiz. Restore robots to a known program or factory firmware if you need modes next week.\nStretch: two robots dance in sync (same waits).",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Week 1 ends with a <strong>dance robot</strong>: a 20-second show that starts on the onboard button and ends with motors stopped. You may only use skills from Days 1–4 (no ultrasonic or line follower yet).</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Recap map: parts, ports, Live vs Upload, speed 0–255.</li>
  <li><strong>0:15–0:30</strong> Plan the routine on paper.</li>
  <li><strong>0:30–1:20</strong> Code, test, tune.</li>
  <li><strong>1:20–1:45</strong> Showcase.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Design rules</h2>
<ol>
  <li>Hat block: when mBot starts up.</li>
  <li>Wait for onboard button before moving.</li>
  <li>At least two different movements (for example forward and a turn).</li>
  <li>At least two LED colour changes.</li>
  <li>At least one buzzer sound.</li>
  <li>Final block: <strong>stop</strong> motors and LEDs off.</li>
</ol>

<h3>Example skeleton</h3>
<pre>when mBot(mCore) starts up
wait until (onboard button pressed)
set LED 1 to (red)
play tone (C4) for (0.3) beats
run forward at speed (100)
wait (0.5) seconds
turn left at speed (100)
wait (0.4) seconds
set LED 1 to (blue)
play tone (G4) for (0.3) beats
run backward at speed (100)
wait (0.4) seconds
stop
set LED 1 to (black)
</pre>

<h2>Tuning tips</h2>
<ul>
  <li>Write the dance as a list of 6–8 lines first. Code second.</li>
  <li>If it runs long, shorten waits, not speed first.</li>
  <li>If it is boring, add a spin (pivot) and a colour flash together.</li>
  <li>Save as <strong>Day5-Dance</strong> before you "improve" and break it.</li>
</ul>

<h2>Week 1 you should now know</h2>
<ul>
  <li>mBot = Makeblock, brain = mCore (Arduino Uno).</li>
  <li>Port 2 line follower (blue), Port 3 ultrasonic (yellow), M1/M2 motors.</li>
  <li>Factory A/B/C vs your uploaded program.</li>
  <li>mBlock device + hat block + Upload.</li>
  <li>IPO: button in, LEDs/sound/motors out.</li>
</ul>

<h3>Next week</h3>
<p>Sensors that see the world: ultrasonic distance and the line follower. Bring tape and a box.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 5 — Week 1 Review Quiz',
                    'description' => 'Mixed review of parts, mBlock, motors, and outputs.',
                    'questions' => [
                        $this->mc('mBot\'s main board (mCore) runs at about:', '16 MHz', ['16 GHz only', '1 Hz', 'The same as a school Wi-Fi router always']),
                        $this->mc('Which statement about factory Mode C is true?', 'It follows a line using the line-follower module', ['It only plays the buzzer', 'It disables Port 3 forever', 'It requires Python']),
                        $this->mc('Yellow RJ25 tags are for:', 'Single digital modules such as the ultrasonic sensor', ['Dual digital line followers only', 'Motor ports M1/M2', 'HDMI']),
                        $this->mc('Blue RJ25 tags are for:', 'Dual digital modules such as the line follower', ['The USB cable colour', 'The IR remote batteries', 'LiPo only']),
                        $this->mc('A dance program should end with:', 'Motors stopped (and ideally LEDs off)', ['Forever full speed 255', 'Unplugging Port 2 mid-run', 'Deleting the hat block']),
                        $this->mc('Why start the dance on the onboard button?', 'So you can place the robot, then start the show on purpose', ['Because USB cannot upload without it', 'Because M1 will not spin otherwise', 'Because mBlock forbids wait blocks']),
                        $this->mc('Block-based mBot code can later be viewed as:', 'Arduino C (generated from blocks)', ['Only Morse code', 'Only Excel formulas', 'Only Mode A firmware']),
                        $this->mc('Communication options for mBot include:', 'USB, Bluetooth, and 2.4G', ['Only carrier pigeon', 'Only HDMI', 'Only Port 2 analogue sound']),
                        $this->mc('Power options listed for mBot kits include:', '4 AA batteries or a 3.7 V LiPo pack', ['Mains 240 V into Port 3', 'Solar only', 'Nine-volt into the ultrasonic eyes']),
                        $this->tf('Sensors collect information; actuators such as motors and LEDs act on the world.', true),
                    ],
                ],
            ],
        ];
    }

    private function week2Lessons(): array
    {
        return [
            [
                'title' => 'Day 6: Ultrasonic Sensor — Seeing Distance',
                'summary' => 'Read distance in centimetres from the Me Ultrasonic Sensor on Port 3.',
                'difficulty' => 'beginner',
                'objectives' => "Explain that the ultrasonic module measures distance from about 3 to 400 cm\nConfirm the yellow tag and Port 3 connection\nUse the ultrasonic sensing block in mBlock\nShow distance with LED colour bands\nChoose a safe threshold for \"object is close\"",
                'guidance' => "Materials: metre stick, boxes at 10, 30, 60 cm. One person holds a notebook as a target.\n2-hour flow:\n0:00–0:15 Recap Port 3 yellow. Show the two transducers (trig/echo style eyes).\n0:15–0:45 Live readouts or LED bands: green far, yellow mid, red close.\n0:45–1:20 Threshold if distance &lt; 15 then red else green.\n1:20–1:45 Calibration challenge: detect a hand at ~20 cm only.\n1:45–2:00 Quiz.\nCoach: 0 or huge values often mean unplugged, wrong port, or aimed at the ceiling.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>The <strong>Me Ultrasonic Sensor</strong> measures how far an object is. Makeblock's range is about <strong>3–400 cm</strong>. The yellow tag means a <strong>single digital</strong> interface — plug it into a yellow port, default <strong>Port 3</strong>.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> How echo works in simple language.</li>
  <li><strong>0:15–0:45</strong> Read distance. LED colour = near / far.</li>
  <li><strong>0:45–1:20</strong> if distance &lt; threshold.</li>
  <li><strong>1:20–1:45</strong> Calibration challenge.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>How it works (beginner model)</h2>
<p>The module sends a burst of ultrasound and listens for the echo. Closer objects echo sooner, so the block reports a smaller number of <strong>centimetres</strong>. It is not a camera. Soft fabric, thin chair legs, and angled surfaces can confuse it.</p>

<h3>mBlock pattern</h3>
<pre>when mBot(mCore) starts up
forever
  if (ultrasonic sensor on Port 3) &lt; (15) then
    set LED 1 to (red)
  else
    set LED 1 to (green)
</pre>

<h2>Pick a threshold</h2>
<ul>
  <li>Too small (for example 3 cm) — the robot may hit the object before it "sees" it.</li>
  <li>Too large (for example 80 cm) — it panics at empty space and classmates walking by.</li>
  <li>Classroom starting point: <strong>10–20 cm</strong> for a slow robot, then tune.</li>
</ul>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> red if closer than 20 cm, green otherwise. No motors yet.</li>
  <li><strong>Core:</strong> three bands — green &gt; 40, yellow 15–40, red &lt; 15.</li>
  <li><strong>Stretch:</strong> beep when red. Still no driving (that is tomorrow).</li>
</ol>

<h2>Troubleshooting</h2>
<ul>
  <li>Cable in Port 2 by mistake (line follower port).</li>
  <li>Module aimed up or covered by a cable.</li>
  <li>Values stuck at 0 or 400 — reseat RJ25 until it clicks.</li>
  <li>Wrong device / firmware — reconnect mBot in mBlock.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 6 — Ultrasonic Sensor Quiz',
                    'description' => 'Ports, range, thresholds, and how distance sensing works.',
                    'questions' => [
                        $this->mc('The Me Ultrasonic Sensor is designed for:', 'Distance detection', ['Following a black tape line only', 'Playing MP3 files', 'Charging AA batteries']),
                        $this->mc('Makeblock lists the ultrasonic detection range as about:', '3 to 400 cm', ['3 to 4 mm only', '1 to 10 km', 'Always exactly 15 cm']),
                        $this->mc('The yellow tag on the ultrasonic module means it is a:', 'Single digital interface', ['Dual digital line probe', 'Motor encoder', 'LiPo balancer']),
                        $this->mc('Default port for the ultrasonic module on mBot is:', 'Port 3', ['M1', 'Port 2', 'The buzzer socket']),
                        $this->mc('A smaller ultrasonic reading usually means the object is:', 'Closer', ['Farther', 'Hotter', 'Playing a higher note']),
                        $this->mc('A threshold in an if block is:', 'The distance you choose to count as "too close"', ['The USB driver version', 'The mCore clock in GHz', 'The colour of Port 2']),
                        $this->mc('Why might fabric or a thin chair leg give a bad reading?', 'The sound may not bounce back clearly to the sensor', ['mBlock forbids numbers', 'Yellow cables cannot carry data', 'Motors block all ultrasound']),
                        $this->mc('A good first classroom threshold for "close" on a slow mBot is often around:', '10–20 cm (then tune)', ['400 m', '0.001 cm', 'Always 255 cm']),
                        $this->mc('If readings are stuck at 0, you should first check:', 'The RJ25 cable, port number, and that the sensor faces the target', ['Whether Mode C tape is black', 'The IR remote batteries only', 'Python indentation']),
                        $this->tf('The ultrasonic sensor is an input (sensor), not a motor.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 7: Obstacle Avoidance — If Distance Then Turn',
                'summary' => 'Use ultrasonic readings to stop or turn so mBot does not crash.',
                'difficulty' => 'intermediate',
                'objectives' => "Write if/else that drives forward only when the path is clear\nTurn or reverse when distance is below the threshold\nKeep speed slow enough to react\nCompare a stop-and-turn strategy with a gentle veer\nTest in a mini arena of bags and chairs",
                'guidance' => "Materials: arena walls of bags/boxes, one exit gap. No racing yet.\n2-hour flow:\n0:00–0:15 Day 6 recap. Demo a working avoider.\n0:15–0:40 Design on paper: forever → if close then turn else forward.\n0:40–1:20 Code together, then pairs tune threshold and turn time.\n1:20–1:45 Arena: survive 30 seconds without a hard hit. Stretch: prefer left turns only (maze prep).\n1:45–2:00 Quiz.\nCoach: both motors at 255 will hit before the turn. Start ~80–120. Watch forever loops at pack-up.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Yesterday the LED showed distance. Today the <strong>motors</strong> react. This is the same idea as factory <strong>Mode B</strong>, but it is <em>your</em> program, so you can change the threshold and the turn.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Demo. Safety: slow speed.</li>
  <li><strong>0:15–0:40</strong> Flowchart: forever / if close / else go.</li>
  <li><strong>0:40–1:20</strong> Build and tune.</li>
  <li><strong>1:20–1:45</strong> Arena challenge.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Core algorithm</h2>
<pre>when mBot(mCore) starts up
forever
  if (ultrasonic Port 3) &lt; (15) then
    stop
    turn right at speed (100)
    wait (0.5) seconds
  else
    run forward at speed (90)
</pre>

<h3>Design choices</h3>
<ul>
  <li><strong>Stop then turn</strong> — safer, easier to debug, a bit jumpy.</li>
  <li><strong>Reverse then turn</strong> — better if the sensor only sees the object when very close.</li>
  <li><strong>Always-turn-left</strong> — useful later in mazes so the robot is consistent.</li>
  <li><strong>Random left or right</strong> — fun, harder to test.</li>
</ul>

<h2>Arena challenge</h2>
<ol>
  <li><strong>Starter:</strong> never hit the wall in 20 seconds (stop-and-turn is OK).</li>
  <li><strong>Core:</strong> 45 seconds in a cluttered arena; speed at least 80.</li>
  <li><strong>Stretch:</strong> reach a green paper "exit" on the far side without a teacher rescue.</li>
</ol>

<h2>Why it still hits things</h2>
<ul>
  <li>Speed too high for the wait/turn time.</li>
  <li>Threshold too small.</li>
  <li>Sensor not facing the direction of travel.</li>
  <li>Objects beside the robot (ultrasonic looks ahead, not sideways).</li>
  <li>Forgot forever — the if ran once and stopped thinking.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 7 — Obstacle Avoidance Quiz',
                    'description' => 'Decisions, loops, thresholds, and testing.',
                    'questions' => [
                        $this->mc('Obstacle avoidance should run inside which kind of block so it keeps checking?', 'forever (or a long loop)', ['A single wait 60 seconds then halt with no loop', 'Delete the hat block', 'Only Mode C firmware']),
                        $this->mc('If distance is less than the threshold, a simple safe action is:', 'Stop and turn (or reverse, then turn)', ['Set speed to 255 toward the object', 'Unplug Port 3', 'Turn off the mCore clock']),
                        $this->mc('Factory firmware Mode B is most similar to today\'s project because it:', 'Uses the ultrasonic sensor to avoid obstacles', ['Only follows tape', 'Only blinks LED 2', 'Only charges USB']),
                        $this->mc('A common reason mBot still crashes is:', 'Speed is too high for the chosen threshold', ['The buzzer is too quiet', 'mBlock uses centimetres', 'Aluminium chassis is too light']),
                        $this->mc('The ultrasonic sensor mainly looks:', 'Forward (the direction the module faces)', ['Through the floor only', '360 degrees like radar always', 'Only at the IR remote']),
                        $this->mc('An always-turn-left policy is useful because:', 'The robot behaves consistently, which helps in mazes', ['It disables Port 3', 'It charges the LiPo', 'It paints the line black']),
                        $this->mc('if / else in this lesson chooses between:', 'Avoid action vs keep driving forward', ['M1 vs USB', 'Yellow vs blue cables on motors', 'Live vs Windows update']),
                        $this->mc('Units for the ultrasonic block are typically:', 'Centimetres', ['Kilograms', 'Decibels of Wi-Fi', 'Pixels on the stage sprite only']),
                        $this->mc('Why test in an arena of bags before a race?', 'To tune threshold and turn time without breaking the robot or hitting people', ['To delete firmware', 'To warm the AA batteries only', 'Because Mode A requires bags']),
                        $this->tf('If the if-close action runs only once (no forever), mBot will not keep watching for new obstacles.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 8: Line Follower Sensor — Values 0 to 3',
                'summary' => 'Read both IR probes and map the four line-follower values 0, 1, 2, and 3.',
                'difficulty' => 'intermediate',
                'objectives' => "State that the line follower is dual digital (blue tag) on Port 2\nExplain that two probes each see black or white\nMap values 0–3 to both-black, left-black, right-black, both-white\nUse LED or sound to show which value is active\nPrepare a high-contrast black tape line on a light floor",
                'guidance' => "Materials: black electrical tape on light floor or large white paper. Avoid shiny tape glare if possible.\n2-hour flow:\n0:00–0:15 Show the two probes. Hold black/white paper under each side.\n0:15–0:50 Display value: 4 LED/buzzer codes for 0,1,2,3.\n0:50–1:20 Students fill a table: value vs what they see.\n1:20–1:45 Move the robot by hand across a tape line and call out values.\n1:45–2:00 Quiz. No full line-follow drive until Day 9.\nCoach: some groups reverse black/white logic if the floor is dark. Teach \"line vs background\" not only \"black\".",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>The <strong>Me Line Follower</strong> has <strong>two</strong> sensors. Each has an IR LED and a detector. The blue tag means <strong>dual digital</strong> — default <strong>Port 2</strong>. It can follow a <strong>black line on white</strong> or a <strong>white line on black</strong>.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Two eyes, not one. Port 2 blue.</li>
  <li><strong>0:15–0:50</strong> Read the 0–3 value. Encode it with lights.</li>
  <li><strong>0:50–1:20</strong> Build the value table as a class.</li>
  <li><strong>1:20–1:45</strong> Slide the robot over tape. Call the number.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>The four values (standard mBlock mBot)</h2>
<p>The line-follower block reports a number from <strong>0 to 3</strong>:</p>
<ul>
  <li><strong>0</strong> — both probes on black (or both on the line, depending on contrast).</li>
  <li><strong>1</strong> — left on black, right on white.</li>
  <li><strong>2</strong> — left on white, right on black.</li>
  <li><strong>3</strong> — both probes on white (or both off the line).</li>
</ul>
<p>Confirm this table with <em>your</em> tape and floor today. If your numbers feel swapped, you may be on a dark floor with light tape — write your camp's table on the board and use that tomorrow.</p>

<pre>when mBot(mCore) starts up
forever
  set lineVal to (line follower sensor Port 2)
  if lineVal = 3 then set LED 1 to (green)
  if lineVal = 0 then set LED 1 to (red)
  if lineVal = 1 then set LED 1 to (yellow)
  if lineVal = 2 then set LED 1 to (blue)
</pre>

<h2>Good track design</h2>
<ul>
  <li>Tape at least as wide as the gap between the two probes.</li>
  <li>Strong contrast. Dusty shiny floors confuse IR.</li>
  <li>Start with a straight line, then a gentle curve. Hairpin turns wait until the robot can follow.</li>
</ul>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> LED colour for each of 0–3 while you hold paper under the probes.</li>
  <li><strong>Core:</strong> complete the four-row table with a partner.</li>
  <li><strong>Stretch:</strong> beep only when the value is 1 or 2 (exactly one probe on the line).</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 8 — Line Follower Values Quiz',
                    'description' => 'Ports, two probes, values 0–3, and track setup.',
                    'questions' => [
                        $this->mc('The Me Line Follower default port on mBot is:', 'Port 2', ['Port 3', 'M2', 'The IR remote']),
                        $this->mc('The blue tag means the module is:', 'Dual digital (two probes)', ['Single digital ultrasonic', 'A motor', 'USB only']),
                        $this->mc('How many sensing probes are on the Me Line Follower?', 'Two', ['One', 'Four', 'Eight']),
                        $this->mc('The line-follower block typically reports:', 'A value from 0 to 3', ['Only yes/no Wi-Fi', 'Distance in kilometres', 'Motor RPM']),
                        $this->mc('Value 3 commonly means:', 'Both probes see white / both off a black line (confirm on your floor)', ['Both motors unplugged', 'Ultrasonic is 3 cm', 'Mode A only']),
                        $this->mc('Value 1 commonly means:', 'Left probe on black, right on white (confirm on your floor)', ['Both probes on white only', 'Battery empty', 'Port 3 unplugged']),
                        $this->mc('mBot can be set up to follow:', 'A black line on white or a white line on black', ['Only rainbow LED strips', 'Only Wi-Fi SSIDs', 'Only ultrasonic echoes']),
                        $this->mc('A line that is much thinner than the gap between probes is:', 'Harder to detect reliably', ['Always better', 'Required by mCore 16 MHz', 'The only legal Mode C track']),
                        $this->mc('Each probe uses:', 'An IR transmitting LED and an IR detector', ['A GPS chip', 'A tiny camera with Zoom', 'A temperature thermistor only']),
                        $this->tf('You should confirm what 0, 1, 2, and 3 mean on your actual tape and floor before writing a follower.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 9: Line Following — Stay on the Track',
                'summary' => 'Turn the 0–3 readings into a robot that follows black tape.',
                'difficulty' => 'intermediate',
                'objectives' => "Write if/else rules for left-off and right-off the line\nDrive forward when both probes see the line or the chosen on-line value\nRecover when both probes lose the line\nTune speed so curves are possible\nComplete a simple loop of tape",
                'guidance' => "Materials: oval or figure-eight of black tape. Start/finish mark.\n2-hour flow:\n0:00–0:15 Recap yesterday's table. Demo a slow follower.\n0:15–0:40 Algorithm on board: 1 → turn left, 2 → turn right, on-line → forward, lost → search.\n0:40–1:25 Pair coding. Slow speed ~70–100.\n1:25–1:45 Three laps or a figure-eight.\n1:45–2:00 Quiz.\nCoach: if it oscillates wildly, slow down and shorten turn bursts. If it leaves at curves, the line may be too sharp or speed too high.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Turn sensor numbers into <strong>steering</strong>. This is factory <strong>Mode C</strong> — but you will understand every if.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Demo on the class oval.</li>
  <li><strong>0:15–0:40</strong> Rules from yesterday's table.</li>
  <li><strong>0:40–1:25</strong> Code and tune.</li>
  <li><strong>1:25–1:45</strong> Three-lap challenge.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>A simple follower (black line on white floor)</h2>
<p>Use your Day 8 table. A common starting point:</p>
<pre>when mBot(mCore) starts up
forever
  set v to (line follower Port 2)
  if v = 1 then
    // left is on black, right is on white → ease left
    turn left at speed (80)
  else if v = 2 then
    turn right at speed (80)
  else if v = 0 then
    run forward at speed (90)
  else
    // v = 3 both white — lost: slow search turn
    turn left at speed (60)
</pre>
<p>If your tape/floor mapping differs, change the numbers, not the idea: <strong>steer toward the line</strong>.</p>

<h3>Tuning</h3>
<ul>
  <li>Fast + sharp turn = leaving the track.</li>
  <li>Lost (both white): a slow spin often finds the line again.</li>
  <li>Both black (0) can mean "centred on a wide line" — forward is usually correct.</li>
</ul>

<h2>Challenges</h2>
<ol>
  <li><strong>Starter:</strong> stay on a 1 m straight for the full length.</li>
  <li><strong>Core:</strong> complete one oval without a hand touch.</li>
  <li><strong>Stretch:</strong> figure-eight or a 90° corner. LED green on line, red when lost.</li>
</ol>

<h2>Debug checklist</h2>
<ul>
  <li>Module height and pointing at the floor, not tilted.</li>
  <li>Port 2, blue cable fully clicked.</li>
  <li>Motors not swapped (left/right steering inverted).</li>
  <li>Speed below hero speed until the oval works.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 9 — Line Following Quiz',
                    'description' => 'Steering rules, tuning, and recovery when lost.',
                    'questions' => [
                        $this->mc('If the left probe is on the line and the right probe is off it, you should generally:', 'Steer left (back toward the line)', ['Steer right away from the line', 'Set ultrasonic to 400', 'Unplug M1']),
                        $this->mc('Factory Mode C is most similar to today because it:', 'Follows a line using the line-follower module', ['Only avoids walls with ultrasound', 'Only plays Frère Jacques', 'Only uses the IR remote']),
                        $this->mc('A good first speed for line following in class is often:', 'Slow to medium (about 70–100), then increase', ['Always 255', 'Always 0', 'Random 0–255 each millisecond']),
                        $this->mc('When both probes lose the line, a useful recovery is:', 'A slow search turn until a probe sees the line again', ['Full speed forward forever', 'Delete Port 2', 'Switch to HDMI']),
                        $this->mc('Wild left-right shaking on a straight usually means:', 'Turn corrections are too strong or speed is too high', ['The mCore is 16 MHz', 'AA batteries are alkaline', 'mBlock is in Upload mode']),
                        $this->mc('If left and right steering feel backwards, check:', 'Whether M1/M2 are swapped', ['The colour of the ultrasonic tag only', 'Windows wallpaper', 'The buzzer note C4']),
                        $this->mc('Line following must keep checking the sensor using:', 'A forever loop (or equivalent)', ['A single if at start-up only', 'The Devices tab + button', 'Mode A remote only']),
                        $this->mc('Wide black tape is often easier at first because:', 'Both probes can sit on the line more easily', ['It disables value 2', 'It charges the robot', 'It turns Port 3 blue']),
                        $this->mc('The main idea of the if/else chain is:', 'Steer toward the line using the two probes', ['Measure 3–400 cm', 'Play four notes', 'Update factory firmware twice']),
                        $this->tf('You can keep Day 8\'s value table and only change motor actions to build a follower.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 10: IR Remote Control and Combining Inputs',
                'summary' => 'Map IR remote buttons to motion, then mix remote drive with a sensor safety stop.',
                'difficulty' => 'intermediate',
                'objectives' => "Identify the IR transmitter and receiver on mCore\nMap remote buttons to forward, back, left, right, and stop\nKeep a forever loop that reads the remote\nAdd an ultrasonic safety: ignore forward if an object is too close\nCompare remote driving with fully autonomous Mode B/C",
                'guidance' => "Materials: kit IR remotes, spare button batteries. Label remotes so two groups do not fight the same robot.\n2-hour flow:\n0:00–0:20 How IR is a sensor. Demo one working remote program (factory Mode A is the inspiration).\n0:20–0:50 Map 5 buttons. Dead-man's stop if no key.\n0:50–1:25 Add ultrasonic: cannot drive forward if &lt; 12 cm.\n1:25–1:45 Mini game: remote slalom, safety stop must work.\n1:45–2:00 Quiz. Week 2 recap oral.\nCoach: multiple remotes interfere. Stagger testing. Point remote at mCore IR window.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>mCore has an <strong>IR receiver</strong> (and transmitter). The plastic remote in the kit talks to the robot with infrared light — the same idea as a TV remote. Factory <strong>Mode A</strong> is manual IR drive. You will rebuild that idea in mBlock, then add a <strong>safety stop</strong> using the ultrasonic sensor.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> IR as a sensor. Aim at the robot, not the floor.</li>
  <li><strong>0:20–0:50</strong> Five buttons: F/B/L/R/Stop.</li>
  <li><strong>0:50–1:25</strong> Combine: no forward if distance &lt; 12 cm.</li>
  <li><strong>1:25–1:45</strong> Slalom game.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Remote skeleton</h2>
<pre>when mBot(mCore) starts up
forever
  if (remote button up pressed) then
    run forward at speed (100)
  else if (remote button down pressed) then
    run backward at speed (100)
  else if (remote button left pressed) then
    turn left at speed (100)
  else if (remote button right pressed) then
    turn right at speed (100)
  else
    stop
</pre>
<p>Exact block names vary slightly by mBlock version. Use the <strong>Sensing</strong> / IR remote blocks for mBot.</p>

<h2>Combining inputs (the important new skill)</h2>
<p>Two sensors can disagree. Rule example: <strong>ultrasonic wins for safety</strong>.</p>
<pre>if (remote up) and (ultrasonic Port 3 &gt; 12) then
  run forward
else if (remote up) and (ultrasonic Port 3 ≤ 12) then
  stop
  set LED 1 to (red)
</pre>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> four directions + stop when no button.</li>
  <li><strong>Core:</strong> safety: cannot ram a box on purpose with the up button.</li>
  <li><strong>Stretch:</strong> number buttons set speed 80 / 120 / 160. LED shows gear.</li>
</ol>

<h2>Interference and aiming</h2>
<ul>
  <li>Sunlight and classroom IR can glitch readings — test away from bright windows if needed.</li>
  <li>Two remotes on one robot: chaos. One remote per robot.</li>
  <li>Hold the remote so its LED faces the mCore IR window.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 10 — IR Remote Quiz',
                    'description' => 'IR control, combining sensors, and safety.',
                    'questions' => [
                        $this->mc('The kit remote talks to mBot using:', 'Infrared (IR) light', ['The ultrasonic 3–400 cm beam only', 'The line-follower blue tag', 'HDMI']),
                        $this->mc('Factory firmware Mode A is mainly:', 'Manual driving with the IR remote', ['Line following only', 'Python notebooks', 'Charging via Port 2']),
                        $this->mc('A remote program should keep reading buttons using:', 'A forever loop', ['One if at power-on only', 'Unplugging USB every second', 'Mode C tape']),
                        $this->mc('If no remote button is pressed, a safe default is:', 'Stop the motors', ['Speed 255 forward', 'Spin forever', 'Disable the ultrasonic']),
                        $this->mc('Combining remote + ultrasonic so forward is blocked when close is an example of:', 'Using more than one sensor in one decision', ['Removing the controller', 'Factory firmware only', 'Yellow cables on M1']),
                        $this->mc('Two groups pointing remotes at the same robot will often:', 'Interfere with each other', ['Charge both LiPos faster', 'Upgrade mCore to 32 MHz', 'Turn Port 3 into Port 2']),
                        $this->mc('IR remote blocks for mBot live mainly in which palette?', 'Sensing (IR / remote)', ['Only Motors', 'Only Looks sprites on the stage', 'Excel']),
                        $this->mc('A red LED when forward is denied is useful because:', 'It gives feedback that the safety rule fired', ['It increases ultrasonic range to 400 m', 'It is required to use M2', 'It restores factory Mode C']),
                        $this->mc('mCore includes which IR hardware?', 'An IR transmitter and receiver', ['Only a Bluetooth chip and no IR', 'Only a camera', 'Only Port 2 analogue IR thermometers']),
                        $this->tf('Autonomous line following and human IR driving are the same program.', false),
                    ],
                ],
            ],
        ];
    }

    private function week3Lessons(): array
    {
        return [
            [
                'title' => 'Day 11: Light Sensor — Seek, Avoid, and Night Light',
                'summary' => 'Use the onboard light sensor for light-seeking, light-avoiding, and LED night-light behaviours.',
                'difficulty' => 'intermediate',
                'objectives' => "Find the onboard light sensor on mCore\nRead a light value and pick a threshold by experiment\nProgram a night light (LED on when dark)\nProgram light-seeking or light-avoiding motion\nCompare light sensing with ultrasonic and line following",
                'guidance' => "Materials: torch/phone flashlight, cardboard tunnel, a dark corner. Do not shine lasers.\n2-hour flow:\n0:00–0:15 Locate the light sensor. Cover with a finger vs shine a torch.\n0:15–0:45 Night light: if light &lt; T then LED white else off. Students choose T.\n0:45–1:25 Light-seek: turn toward brighter side using a simple scan, or forward when bright.\n1:25–1:45 Avoider: hide from the torch (cockroach robot).\n1:45–2:00 Quiz.\nCoach: classroom ceiling lights make thresholds fragile. Calibrate in the same spot they will demo.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>mCore includes an <strong>onboard light sensor</strong>. It does not use Port 2 or Port 3. You will calibrate a number that means "dark" in this room, then build a night light and a seek-or-hide robot.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Find the sensor. Finger vs torch.</li>
  <li><strong>0:15–0:45</strong> Night-light LED.</li>
  <li><strong>0:45–1:25</strong> Seek or hide with motors.</li>
  <li><strong>1:25–1:45</strong> Demo in a cardboard tunnel.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Calibrate first</h2>
<p>Read the light value in three situations and write them down:</p>
<ol>
  <li>Normal classroom.</li>
  <li>Finger covering the sensor.</li>
  <li>Torch from 20 cm.</li>
</ol>
<p>Your <strong>threshold</strong> should sit between dark and light for <em>this</em> room. Do not copy another table's number blindly.</p>

<pre>when mBot(mCore) starts up
forever
  if (light sensor) &lt; (yourDarkNumber) then
    set LED 1 to (white)
  else
    set LED 1 to (black)
</pre>

<h3>Light-seeking (moth)</h3>
<p>Simple version: if it is bright ahead, drive forward slowly; if dark, spin to search. More advanced: compare readings while turning.</p>

<h3>Light-avoiding (cockroach)</h3>
<p>If a torch hits the sensor, reverse and turn, then continue. This is the same <strong>if close then avoid</strong> idea as ultrasonics, with a different sensor.</p>

<h2>Compare the three "eyes"</h2>
<ul>
  <li><strong>Ultrasonic</strong> — distance to an object (Port 3).</li>
  <li><strong>Line follower</strong> — black/white under the chassis (Port 2).</li>
  <li><strong>Light sensor</strong> — brightness at the board (onboard).</li>
</ul>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> night light that you can demo by covering the sensor.</li>
  <li><strong>Core:</strong> moth or cockroach (pick one) that a teacher can trigger with a torch.</li>
  <li><strong>Stretch:</strong> night light + will not drive into a wall (ultrasonic still active).</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 11 — Light Sensor Quiz',
                    'description' => 'Onboard light, thresholds, and comparing sensors.',
                    'questions' => [
                        $this->mc('The mBot light sensor used in this lesson is:', 'Onboard on the mCore', ['Always the Port 2 line module', 'Always the Port 3 ultrasonic', 'Inside the AA battery']),
                        $this->mc('Before you pick a darkness threshold you should:', 'Measure light values in this room (cover vs torch vs normal)', ['Always use 15 because of ultrasonics', 'Use 255 because of motors', 'Skip sensing and use Mode C']),
                        $this->mc('A night-light program turns LEDs on when:', 'The light reading is below your dark threshold', ['The IR remote is missing', 'M1 is unplugged', 'Factory Mode A is running']),
                        $this->mc('A moth-style robot:', 'Moves toward brighter light', ['Always follows black tape', 'Measures 3–400 cm only', 'Disables the buzzer permanently']),
                        $this->mc('A cockroach-style robot:', 'Moves away from a sudden bright light', ['Charges the LiPo with light', 'Uses HDMI', 'Turns Port 2 yellow']),
                        $this->mc('Light sensing and ultrasonic sensing are different because:', 'One measures brightness and the other measures distance', ['Both always use Port 2', 'Both always use M1', 'Neither is a sensor']),
                        $this->mc('Why might a threshold that worked at 9:00 fail at 11:00?', 'Classroom daylight and ceiling lights changed', ['mCore clock dropped from 16 MHz', 'Yellow tags melted', 'mBlock deleted Control blocks']),
                        $this->mc('Combining night light + ultrasonic safety is an example of:', 'Using two different sensors in one behaviour', ['Removing the controller', 'Factory firmware A only', 'Dual digital motors']),
                        $this->mc('The light sensor is:', 'An input', ['An actuator like a motor', 'A type of wheel', 'Online firmware itself']),
                        $this->tf('You should copy another team\'s light threshold without testing in your own spot.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 12: Variables, Nested Ifs, Loops, and Debugging',
                'summary' => 'Store sensor values in variables, nest decisions, and use a simple debug routine.',
                'difficulty' => 'intermediate',
                'objectives' => "Create a variable to store distance or line value\nNest ifs (for example line follow only if nothing is close)\nUse repeat and wait until with a purpose\nDebug by showing state on LEDs\nName three common mBot failure causes (port, firmware, logic)",
                'guidance' => "Materials: combined course — a line that ends at a wall. Students must follow then stop.\n2-hour flow:\n0:00–0:20 Why variables: read once, use twice. Demo nested: if far then follow else stop.\n0:20–1:10 Build follow-until-wall. LED debug codes.\n1:10–1:40 Debug clinic: 8-minute rotations, teacher has a fault sheet (wrong port, no forever, swapped motors).\n1:40–2:00 Quiz.\nCoach: nested logic is the capstone prerequisite. Do not skip the LED debug codes.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Real robots combine rules. Today you store readings in <strong>variables</strong>, <strong>nest</strong> ifs, and <strong>debug</strong> with LED codes instead of guessing.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> Variables and nested ifs on the board.</li>
  <li><strong>0:20–1:10</strong> Project: follow a line until a wall, then stop and turn red.</li>
  <li><strong>1:10–1:40</strong> Debug clinic.</li>
  <li><strong>1:40–2:00</strong> Quiz.</li>
</ul>

<h2>Variables</h2>
<p>Read the sensor once per loop into a variable. Then all ifs use the same snapshot. That is easier to reason about than calling the sensor three times (values can change between calls).</p>
<pre>when mBot(mCore) starts up
forever
  set distance to (ultrasonic Port 3)
  set lineVal to (line follower Port 2)
  if distance &lt; 12 then
    stop
    set LED 1 to (red)
  else
    // nested: only follow the line when the path is clear
    if lineVal = 1 then turn left...
    else if lineVal = 2 then turn right...
    else run forward...
    set LED 1 to (green)
</pre>

<h3>wait until vs forever</h3>
<ul>
  <li><strong>wait until</strong> — pause the script until a condition becomes true (button, distance, etc.).</li>
  <li><strong>forever</strong> — keep doing the body. Use for driving behaviours.</li>
  <li><strong>repeat 10</strong> — finite. Good for a scan: turn a little, 10 times.</li>
</ul>

<h2>LED debug codes (use these in camp)</h2>
<ul>
  <li>Red — emergency stop / wall.</li>
  <li>Green — following / OK.</li>
  <li>Yellow — lost line / searching.</li>
  <li>Blue — remote or special mode.</li>
</ul>

<h2>Failure shortlist</h2>
<ol>
  <li><strong>Hardware:</strong> wrong port, loose RJ25, swapped motors.</li>
  <li><strong>Firmware / connect:</strong> wrong device, Live vs Upload confusion, no hat block.</li>
  <li><strong>Logic:</strong> missing forever, inverted if, threshold copied from another robot.</li>
</ol>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> variable <code>distance</code> shown as red/green only.</li>
  <li><strong>Core:</strong> nested follow-until-wall.</li>
  <li><strong>Stretch:</strong> after the wall, reverse 0.4 s, turn 90°, then wait for button to continue.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 12 — Logic and Debugging Quiz',
                    'description' => 'Variables, nested decisions, loops, and fault-finding.',
                    'questions' => [
                        $this->mc('A good reason to store ultrasonic distance in a variable each loop is:', 'All decisions in that loop use the same reading', ['It charges Port 3', 'It turns the tag from yellow to blue', 'It replaces the hat block']),
                        $this->mc('Nested ifs in the follow-until-wall project mean:', 'Line following runs only when the wall is not too close', ['Motors unplug themselves', 'Factory Mode A is required', 'The light sensor becomes Port 2']),
                        $this->mc('wait until is best described as:', 'Pause until a condition becomes true', ['A motor speed of 255', 'Online firmware', 'The IR transmitter']),
                        $this->mc('Driving behaviours that must keep sensing the world need:', 'forever (or an equivalent loop)', ['A single if and then the program ends', 'Deleting Sensing blocks', 'HDMI debug']),
                        $this->mc('An LED that is red for wall and green for following is mainly for:', 'Debugging / showing state to humans', ['Increasing RPM to 200 always', 'Changing RJ25 colour', 'Replacing the line follower']),
                        $this->mc('Loose RJ25 cables are a:', 'Hardware fault', ['mBlock variable type error only', 'Nested if syntax only', 'LiPo chemistry problem only']),
                        $this->mc('Forgot the when mBot starts up hat block in Upload mode. Result:', 'The uploaded program may never run', ['Mode C gets faster', 'Port 2 becomes Port 3', 'Batteries charge over IR']),
                        $this->mc('repeat 10 that turns a little each time is useful to:', 'Scan or search in a controlled way', ['Delete variables', 'Force factory firmware', 'Set M1 to USB']),
                        $this->mc('If left/right line steering is inverted, check:', 'Motor wiring (M1/M2) and your value table', ['Whether aluminium is magnetic', 'The 16 MHz crystal brand', 'The quiz passing score']),
                        $this->tf('Calling the ultrasonic sensor three times in one loop can give three different numbers if the robot or target moved.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 13: Maze Challenge — Plan, Test, Improve',
                'summary' => 'Apply Week 2–3 skills to a classroom maze with a time limit and a design log.',
                'difficulty' => 'intermediate',
                'objectives' => "Walk the maze first and choose a strategy (left-wall, timed turns, or mixed)\nImplement, test, and change only one thing at a time\nLog failures (stuck corner, missed opening, too fast)\nFinish or improve by a time checkpoint\nExplain your strategy in two sentences",
                'guidance' => "Materials: tape maze or box walls, start/finish flags, stopwatch. Maze should be solvable by a slow left-wall follower plus ultrasonic.\n2-hour flow:\n0:00–0:15 Rules: no hands after start, 3 official timed runs, best counts.\n0:15–0:35 Strategy on paper. Approve before they code a mess.\n0:35–1:25 Build/test. Mid-point: freeze and one teacher tip per pair.\n1:25–1:50 Official runs.\n1:50–2:00 Quiz (short) then note what they will reuse tomorrow.\nCoach: left-wall + slow speed beats fancy code that was never tested.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>maze</strong> is not a new sensor. It is a test of strategy: ultrasonic for walls, maybe the line follower if the maze has a taped path, plus nested logic from yesterday.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Rules and walk the maze without the robot.</li>
  <li><strong>0:15–0:35</strong> Choose a strategy and write it down.</li>
  <li><strong>0:35–1:25</strong> Implement and test.</li>
  <li><strong>1:25–1:50</strong> Three timed runs.</li>
  <li><strong>1:50–2:00</strong> Quiz.</li>
</ul>

<h2>Strategy menu</h2>
<ul>
  <li><strong>Left-wall follower:</strong> if front is clear, go forward; if front blocked, turn left (or right — pick one and stick to it). Works in many simple mazes.</li>
  <li><strong>Line maze:</strong> if the teacher put tape in the corridors, reuse Day 9.</li>
  <li><strong>Waypoint / timed turns:</strong> only if the maze is a known rectangle and you measured waits — brittle when batteries drop.</li>
</ul>

<h3>Left-wall sketch</h3>
<pre>forever
  if front distance &lt; 12 then
    turn left 90° (tune the wait!)
  else
    go forward slowly
</pre>
<p>Add a little pause after turns so the ultrasonic is not reading the wall you just faced.</p>

<h2>Improve like an engineer</h2>
<ol>
  <li>Change <strong>one</strong> thing (threshold, or turn time, or speed) per test.</li>
  <li>Write what happened: "stuck in corner" / "passed opening" / "too fast".</li>
  <li>Do not rewrite the whole program with 4 minutes left.</li>
</ol>

<h2>Scoring (example)</h2>
<ul>
  <li>+5 finish without a hand touch.</li>
  <li>+2 no collisions.</li>
  <li>+1 explained strategy in two sentences.</li>
  <li>Best of 3 runs.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 13 — Maze Challenge Quiz',
                    'description' => 'Strategy, testing, and maze logic.',
                    'questions' => [
                        $this->mc('A left-wall (or right-wall) maze robot needs a sensor that can see:', 'A wall in front (usually ultrasonic)', ['Only the IR remote', 'Only the buzzer pitch', 'HDMI walls']),
                        $this->mc('The most important habit when the maze fails is:', 'Change one setting, test again, and log the result', ['Rewrite every block at once', 'Set speed to 255 immediately', 'Unplug Port 2 and Port 3']),
                        $this->mc('Timed-turn mazes are brittle because:', 'Wait times change with battery and floor', ['mCore cannot turn', 'Ultrasonic cannot be used in rooms', 'Yellow tags forbid turning']),
                        $this->mc('After a 90° turn you may need a short wait so that:', 'The ultrasonic is not still seeing the old wall', ['Factory Mode C starts', 'AA batteries reverse polarity', 'mBlock saves automatically']),
                        $this->mc('If the maze also has black tape in the corridors, you may:', 'Combine line following with wall checks (nested logic)', ['Never use Port 2 in a maze', 'Delete the ultrasonic', 'Use only Live mode forever']),
                        $this->mc('Walking the maze without the robot first helps you:', 'Choose a strategy before coding', ['Charge the LiPo through the floor', 'Change mCore to 8 MHz', 'Skip the hat block']),
                        $this->mc('A hand rescue in a timed run usually means:', 'That attempt should not count as a clean finish', ['The ultrasonic range became 400 m', 'Mode A is complete', 'Variables are illegal']),
                        $this->mc('Slow speed in a maze is often better because:', 'The robot has time to sense and turn before a collision', ['Motors cannot run below 200', 'mBlock forbids speed 90', 'IR remotes require 255']),
                        $this->mc('Sticking to always-left (or always-right) turns helps because:', 'The behaviour is consistent and easier to debug', ['It disables nested ifs', 'It paints the walls black', 'It restores factory firmware']),
                        $this->tf('A maze always requires a new electronic module that is not in the standard mBot kit.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 14: Capstone Build — Design Your mBot Mission',
                'summary' => 'Choose and build a capstone: patrol, delivery, night guard, or maze-and-park.',
                'difficulty' => 'intermediate',
                'objectives' => "Pick a mission with a clear success test\nList inputs, process, and outputs for that mission\nBuild a first version that works, then add one stretch feature\nPrepare a 60-second demo script for Day 15\nKeep a known-good saved copy before last-minute edits",
                'guidance' => "Materials: optional extras (paper payload tray, printed \"package\", guard route tape). Mission cards printed.\nMissions: (1) Security patrol — follow a loop, stop and red+beep if something is close. (2) Delivery — remote or line to a zone, stop, green LED. (3) Night guard — dark → lights, then patrol. (4) Park assist — maze then stop in a bay.\n2-hour flow:\n0:00–0:15 Pick mission, write IPO and success test.\n0:15–1:30 Build. Checkpoint at 0:50: something must already move.\n1:30–1:50 Rehearse 60-second demo. Save Day14-Capstone.\n1:50–2:00 Quiz.\nCoach: freeze new features 15 minutes before the end. Working simple beats broken fancy.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Choose one <strong>capstone mission</strong> and make a version that you can demo tomorrow. Tomorrow is presentation and polish — today is the working robot.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Pick a mission. Write the success test.</li>
  <li><strong>0:15–1:30</strong> Build. First movement by 50 minutes in.</li>
  <li><strong>1:30–1:50</strong> Rehearse. Save a known-good copy.</li>
  <li><strong>1:50–2:00</strong> Quiz.</li>
</ul>

<h2>Mission cards</h2>
<h3>1. Security patrol</h3>
<p>Follow a taped loop (line follower). If ultrasonic &lt; threshold, stop, red LED, short beep, then resume or wait for button.</p>
<h3>2. Delivery robot</h3>
<p>Carry a paper "parcel". Drive on a line or by remote to a marked bay. Stop, green LED. Optional: cannot crush a box (ultrasonic).</p>
<h3>3. Night guard</h3>
<p>If dark, turn LEDs on and begin a slow patrol. If bright, stop and lights off (or daytime idle).</p>
<h3>4. Park assist</h3>
<p>Navigate a small maze or corridor, then stop centred in a tape bay.</p>

<h2>IPO template (copy this)</h2>
<ul>
  <li><strong>Inputs:</strong> which sensors?</li>
  <li><strong>Process:</strong> main if/else in one sentence.</li>
  <li><strong>Outputs:</strong> motors, LEDs, buzzer.</li>
  <li><strong>Success:</strong> a visitor can see it work in 60 seconds.</li>
</ul>

<h2>Build order</h2>
<ol>
  <li>Minimum: hat block, button start, one sensor, stop at the end.</li>
  <li>Core: the mission actually happens.</li>
  <li>Stretch: one extra (debug LED codes, speed gears, second sensor).</li>
  <li>Save <strong>Day14-Capstone</strong>. Then a backup name if you experiment.</li>
</ol>

<h3>Demo script (write it today)</h3>
<ol>
  <li>What problem does this robot solve?</li>
  <li>Which sensors?</li>
  <li>Watch this (point at the success test).</li>
  <li>What we would add with more time.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 14 — Capstone Design Quiz',
                    'description' => 'Missions, IPO, and shipping a demoable robot.',
                    'questions' => [
                        $this->mc('A good capstone success test is:', 'Something a visitor can see in about 60 seconds', ['A secret that never runs', 'Deleting the hat block', 'Only a written essay with no robot']),
                        $this->mc('IPO in your mission card stands for:', 'Input, process, output', ['Infrared, port, online', 'In-place overwrite', 'Internal program object']),
                        $this->mc('A security patrol that follows tape and stops for an obstacle needs at least:', 'Line follower and ultrasonic', ['Only the buzzer', 'Only Mode A remote with no sensors', 'HDMI and a mouse']),
                        $this->mc('The recommended build order is:', 'Minimum working version, then one stretch feature', ['All stretch features first', 'Never save the project', 'Unplug sensors until Day 15']),
                        $this->mc('You save a known-good copy before last-minute edits because:', 'A last change can break a working demo', ['mBlock charges money per save', 'Factory firmware deletes USB', 'Variables expire at midnight']),
                        $this->mc('A delivery robot stopping in a bay is mainly showing:', 'Control of motors plus a clear end condition', ['That ultrasonics measure kilograms', 'That Port 2 is yellow', 'That mCore is a camera']),
                        $this->mc('Night guard uses which onboard sensor in an essential way?', 'Light sensor', ['Only M2 encoder', 'Only the caster wheel', 'The screwdriver']),
                        $this->mc('If time is almost gone, you should:', 'Freeze new features and rehearse the working demo', ['Rewrite using Arduino C from scratch', 'Add four new missions', 'Remove the stop block for drama']),
                        $this->mc('Your 60-second script should include:', 'Problem, sensors, live success test, next step', ['Only the kit price', 'Only the 16 MHz clock', 'Only Windows device manager']),
                        $this->tf('A simple robot that clearly completes one mission is better for demo day than a complex robot that fails.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 15: Demo Day — Present, Reflect, and Next Steps',
                'summary' => 'Present the capstone, peer-review with a rubric, restore firmware if needed, and recap the 3-week course.',
                'difficulty' => 'intermediate',
                'objectives' => "Give a 60-second demo using the script from Day 14\nPeer-review another team with a simple rubric\nName what you would improve next\nRestore factory firmware or leave a labelled student program as the teacher directs\nRecall the full mBot system: parts, mBlock, sensors, and logic",
                'guidance' => "Materials: rubric slips (works / sensors named / stop safely / clear talk), camera optional, certificates optional.\n2-hour flow:\n0:00–0:10 Order of demos, applause rules, timekeeper.\n0:10–1:10 Demos + peer rubric. Teacher scores separately.\n1:10–1:30 Restore factory firmware OR archive programs. Inventory kits.\n1:30–1:50 Course recap map on the board. Tease mBot add-on packs / mBot2 / Arduino C — no new grading.\n1:50–2:00 Final quiz. Batteries out, screws counted.\nCoach: every robot must stop on command. Celebrate process, not only the flashiest dance.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>This is <strong>demo day</strong>. You present the mission from Day 14, learn from another team, pack the kits, and take the final quiz. No brand-new features after the first 10 minutes except bug fixes the teacher approves.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:10</strong> Demo rules. Freeze code.</li>
  <li><strong>0:10–1:10</strong> Presentations and peer rubric.</li>
  <li><strong>1:10–1:30</strong> Firmware / files / kit inventory.</li>
  <li><strong>1:30–1:50</strong> What you learned in 3 weeks. What is next.</li>
  <li><strong>1:50–2:00</strong> Final quiz.</li>
</ul>

<h2>Peer rubric (1–3 each)</h2>
<ul>
  <li>The robot completes the stated mission (or clearly shows the main behaviour).</li>
  <li>The team names the sensors and ports correctly.</li>
  <li>The robot stops safely (no runaway motors).</li>
  <li>The talk is clear (problem → sensors → watch this).</li>
</ul>

<h2>Kit close-down</h2>
<ol>
  <li>Power off. Remove batteries if that is camp policy.</li>
  <li>USB coiled. Remote in the box.</li>
  <li>Teacher: restore <strong>factory firmware</strong> if the next class needs Modes A/B/C, or keep student firmware labelled on a class laptop.</li>
  <li>Count screws and modules (ultrasonic, line follower).</li>
</ol>

<h2>What Robotics 1 covered</h2>
<ul>
  <li><strong>Week 1:</strong> parts, factory modes, mBlock, motors, lights, sound, button, dance.</li>
  <li><strong>Week 2:</strong> ultrasonic, avoidance, line values, following, IR remote + safety.</li>
  <li><strong>Week 3:</strong> light sensor, nested logic, maze, capstone, demo.</li>
</ul>

<h3>Optional next steps (not assessed today)</h3>
<ul>
  <li>Show Arduino C generated from your blocks.</li>
  <li>mBot add-on packs (light &amp; sound, six-leg, servo cat) if the camp has them.</li>
  <li>mBot2 / Python if you move on to Robotics 2.</li>
</ul>

<p>You started by switching factory modes with a button. You finish by <em>designing</em> the behaviour yourself. That is robotics.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 15 — Robotics 1 Final Quiz',
                    'description' => 'Full-course recap of mBot, mBlock, sensors, and safe demo habits.',
                    'questions' => [
                        $this->mc('mBot is made by Makeblock and its brain is the:', 'mCore (Arduino Uno based)', ['micro:bit only', 'A Raspberry Pi 5 required', 'An iPad GPU']),
                        $this->mc('Default ports for line follower and ultrasonic are:', 'Port 2 (line) and Port 3 (ultrasonic)', ['Port 3 (line) and Port 2 (ultrasonic)', 'M1 and M2', 'USB and HDMI']),
                        $this->mc('Factory modes A, B, and C are:', 'Manual IR, obstacle avoidance, line following', ['Python, Java, Scratch sprites only', 'Red, green, blue motors', 'Live, Upload, and Excel']),
                        $this->mc('Upload mode programs should start with:', 'when mBot(mCore) starts up', ['when green flag clicked on a random sprite only', 'print(\'mBot\') in a browser', 'Mode C tape']),
                        $this->mc('Ultrasonic distance is typically reported in:', 'Centimetres (about 3–400 cm range)', ['Only line values 0–3', 'Only IR remote key codes', 'RPM of the caster']),
                        $this->mc('Line follower values 0–3 come from:', 'Two IR probes seeing line vs background', ['Four ultrasonic eyes', 'The buzzer frequency', 'The 16 MHz crystal colour']),
                        $this->mc('Nested ifs let you:', 'Run one behaviour only when another condition is also true', ['Charge two LiPos at once', 'Turn yellow tags blue', 'Skip the mCore']),
                        $this->mc('A safe demo-day robot always:', 'Can stop (button, end of program, or sensor rule) so it does not run away', ['Runs at 255 until the battery dies', 'Has no hat block', 'Has Port 2 unplugged']),
                        $this->mc('Online firmware is used mainly so that:', 'Live programming has more memory and sensor support, without the three factory games', ['The aluminium chassis becomes plastic', 'M1 and M2 swap names nightly', 'The IR remote charges the mCore']),
                        $this->tf('Sensors feed the controller; the controller commands actuators such as motors, LEDs, and the buzzer.', true),
                    ],
                ],
            ],
        ];
    }
}
