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

class ArduinoAcebottRoboticsLessonSeeder extends Seeder
{
    /**
     * Robotics 2 — Arduino, 4WD kits, ACEBOTT V2, ACECode / mBlock / Arduino IDE.
     *
     * 5 weeks × 5 days × 2 hours. Safe to re-run:
     * php artisan db:seed --class=ArduinoAcebottRoboticsLessonSeeder
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

        $this->command->info('Robotics 2 is ready (25 lessons, 25 quizzes, 250 questions).');

        $this->call(RoboticsAssignmentSeeder::class);
    }

    private function seedCourse(User $instructor): Course
    {
        $existing = Course::query()
            ->where('slug', 'robotics-for-arduino-and-acebott')
            ->orWhere('title', 'Robotics for Arduino and ACEBOTT')
            ->orWhere('title', 'Robotics 2')
            ->first();

        $payload = [
            'title' => 'Robotics for Arduino and ACEBOTT',
            'slug' => 'robotics-for-arduino-and-acebott',
            'description' => '<p><strong>Robotics 2</strong> is a 5-week, 50-hour intermediate course. Students who finished Robotics 1 (mBot) move to open electronics: Arduino Uno/Nano, breadboards, sensors, L298N motor drivers, and 4WD chassis. They then assemble and program the <strong>ACEBOTT QD001 Smart Car V2</strong> in ACECode (blocks), Arduino C, and the ACEBOTT mobile app. Superbot, CLB BOT, LEGO, cardboard, and craft materials are used to invent bodies and missions.</p><p>Programming paths: Arduino IDE for lab circuits, ACECode or mBlock for robots, with ACECode showing generated Arduino/Python in Upload mode.</p>',
            'short_description' => 'Robotics 2: Arduino, 4WD kits, ACEBOTT V2, ACECode/mBlock, sensors, and invention (5 weeks × 2 hours).',
            'difficulty_level' => 'Intermediate',
            'estimated_duration' => 50,
            'category' => 'STEM',
            'tags' => ['robotics', 'arduino', 'acebott', 'acecode', '4wd', 'sensors', 'esp32', 'mblock', 'stem'],
            'requirements' => [
                'Completed Robotics 1 (mBot) or equivalent block-coding experience',
                'Arduino Uno or Nano, USB cable, breadboard, jumper wires, resistors, LEDs',
                '4WD chassis or ACEBOTT QD001 V2 kit where available',
                'Laptop with Arduino IDE and/or ACECode (and mBlock as a backup)',
                'Lithium packs only with holders and teacher supervision',
                'Soldering only in the designated lesson with eye protection',
            ],
            'what_you_learn' => [
                'Wire Arduino Uno/Nano circuits on a breadboard',
                'Write setup() and loop() programs in Arduino C and in ACECode/mBlock',
                'Use digital and analog sensors (light, ultrasonic, DHT, PIR, MQ, water, color)',
                'Drive DC motors with an L298N H-bridge and control servos and steppers',
                'Assemble and program an ACEBOTT V2 4WD smart car (IR, app, Wi-Fi)',
                'Add Bluetooth, Wi-Fi, RFID, relays, and LCD displays',
                'Invent robot bodies with LEGO, cardboard, ice-cream sticks, and bamboo',
                'Work safely with lithium batteries, glue guns, and soldering',
                'Design and demo an original Robotics 2 capstone',
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
                'difficulty_level' => $lessonData['difficulty'] ?? 'intermediate',
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
                'title' => 'Week 1 — Arduino Lab: Boards, Circuits, and First Code',
                'description' => 'Move from mBot to Arduino Uno/Nano. Breadboard LEDs, buttons, Arduino IDE or mBlock, and serial.',
                'overview' => 'Days 1–5: safety, Uno vs Nano, breadboard, blink, buttons, traffic-light challenge.',
                'lessons' => $this->week1Lessons(),
            ],
            [
                'title' => 'Week 2 — Sensors and Displays',
                'description' => 'Digital and analog sensing: light, ultrasonic, DHT, PIR, MQ gas, water, color, and 16x2 LCD.',
                'overview' => 'Days 6–10: analogRead, HC-SR04, climate display, extra sensors, night-security mini project.',
                'lessons' => $this->week2Lessons(),
            ],
            [
                'title' => 'Week 3 — Motors, H-Bridge, and 4WD Chassis',
                'description' => 'Drive DC motors with L298N, add servos and steppers, then assemble a 4WD Arduino car.',
                'overview' => 'Days 11–15: batteries, L298N PWM, servo pan, stepper, chassis build and drive.',
                'lessons' => $this->week3Lessons(),
            ],
            [
                'title' => 'Week 4 — ACEBOTT QD001 V2 Smart Car',
                'description' => 'Assemble ACEBOTT V2, use factory firmware and the app, then program in ACECode (and mBlock).',
                'overview' => 'Days 16–20: V2 firmware and app, ACECode online/upload, 4 independent motors, line tracking, obstacle avoid.',
                'lessons' => $this->week4Lessons(),
            ],
            [
                'title' => 'Week 5 — Wireless, Invention Kits, and Capstone',
                'description' => 'Bluetooth, Wi-Fi, RFID, Superbot/CLB BOT, craft bodies, relays, soldering safety, and demo day.',
                'overview' => 'Days 21–25: app/Bluetooth, Wi-Fi and RFID, kit bodies, invent a gadget, capstone presentations.',
                'lessons' => $this->week5Lessons(),
            ],
        ];
    }

    private function week1Lessons(): array
    {
        return [
            [
                'title' => 'Day 1: From mBot to Arduino — Boards, Power, and Lab Safety',
                'summary' => 'Compare mCore with Arduino Uno/Nano, tour the lab kits, and learn lithium, glue-gun, and wire safety.',
                'objectives' => "Explain how Arduino Uno/Nano differ from mBot mCore\nIdentify USB cables, breadboards, jumper wires, and battery holders\nState lithium-pack and glue-gun rules\nName 5V vs 3.3V and why motors need a separate pack\nSet up a clean station (multimeter, tape, cable ties)",
                'guidance' => "Materials: Uno and Nano samples, USB cables, breadboards, lithium holders (no loose cells), glue gun demo only.\n2-hour flow:\n0:00–0:20 Robotics 1 recap: sensors / controller / actuators. Show Uno next to mBot.\n0:20–0:50 Kit inventory walk: cardboard, sticks, LEGO, sensors drawer, ACEBOTT box (do not assemble yet).\n0:50–1:20 Power: AA vs lithium holder vs USB. Never short a pack. Glue gun = teacher station.\n1:20–1:45 Pin maps: digital 0–13, analog A0–A5 (Uno). Nano extras.\n1:45–2:00 Quiz. Codeless motor drones: 5-minute contrast — no code, no decisions.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Robotics 1 used a ready-made robot (mBot). Robotics 2 uses <strong>open kits</strong>: you choose the board, the wires, and the body. The main brains this week are <strong>Arduino Uno</strong> and <strong>Arduino Nano</strong>. Later we add the <strong>ACEBOTT QD001 V2</strong> (ESP32) 4WD car.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> Same robot trio: sensors, controller, actuators.</li>
  <li><strong>0:20–0:50</strong> Lab tour and kit names.</li>
  <li><strong>0:50–1:20</strong> Power and safety.</li>
  <li><strong>1:20–1:45</strong> Pin maps. USB cables (Uno vs Nano).</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Uno vs Nano vs mBot mCore</h2>
<ul>
  <li><strong>mCore</strong> — Arduino-Uno compatible, but ports are RJ25 colour-coded. Great for beginners.</li>
  <li><strong>Uno</strong> — standard classroom board. USB-B (classic) or USB-C on clones. 14 digital pins, 6 analog (A0–A5), 5 V logic.</li>
  <li><strong>Nano</strong> — same 5 V family, smaller, often USB mini/micro, extra analog pins on many clones. Easy to tape onto a craft robot.</li>
  <li><strong>ACEBOTT V2 brain</strong> — ESP32 (Wi-Fi + Bluetooth on the chip). Program with ACECode, Arduino, or Python — Week 4.</li>
</ul>

<h2>Lab materials you will actually use</h2>
<p>Arduino Uno/Nano and cables, breadboards, resistors, jumper wires, LEDs, switch buttons, capacitors, ultrasonic, PIR, DHT, MQ gas, light, color, water-level, LCD 16x2 + header pins, L298N, servos, steppers, DC fans, relays, Bluetooth, Wi-Fi, RFID, lithium holders (2-way and 4-way), chassis, wheels/tracks, LEGO / Superbot / CLB BOT, cardboard, ice-cream sticks, bamboo, cable ties, electrical tape, glue gun (teacher), soldering kit (later), multimeters.</p>

<h2>Safety (non-negotiable)</h2>
<ul>
  <li><strong>Lithium packs</strong> stay in a holder. No loose cells in a bag. No shorts with live wires.</li>
  <li><strong>Motors and relays</strong> often need their own battery. Do not power big motors from the Uno 5 V pin.</li>
  <li><strong>Glue gun</strong> — teacher station, rest on a stand, no touching the tip.</li>
  <li><strong>Soldering</strong> waits until Day 24. Today: look only.</li>
  <li>Codeless motor drones are toys for contrast: they spin without a program. Our robots <em>decide</em>.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 1 — Arduino Lab Safety Quiz',
                    'description' => 'Boards, power, and lab rules.',
                    'questions' => [
                        $this->mc('Arduino Uno logic is typically:', '5 V', ['12 V only', '48 V mains', 'The same as a wall socket']),
                        $this->mc('Compared with mBot mCore, a bare Arduino Uno uses:', 'Header pins and jumper wires (not RJ25 colour ports)', ['Only LEGO studs', 'HDMI for motors', 'No USB ever']),
                        $this->mc('Arduino Nano is mainly chosen because it is:', 'Smaller and easy to mount on a craft chassis', ['A 240 V power supply', 'A type of ultrasonic sensor', 'The ACEBOTT mobile app']),
                        $this->mc('ACEBOTT QD001 V2 is built around which controller family?', 'ESP32', ['Only a BBC micro:bit with no extras', 'A PlayStation', 'A 16x2 LCD']),
                        $this->mc('Large DC motors should usually be powered from:', 'A separate battery pack through a driver (e.g. L298N)', ['The Uno 5 V pin only', 'The USB data lines', 'The LCD contrast pot']),
                        $this->mc('Lithium cells in this lab must:', 'Stay in a proper holder under teacher rules', ['Be carried loose in a pocket', 'Be shorted to test them', 'Be soldered without permission']),
                        $this->mc('A glue gun belongs:', 'At the teacher station on a stand', ['In a student backpack while hot', 'Plugged into analog A0', 'As a motor driver']),
                        $this->mc('A codeless motor drone is useful in class to show:', 'Motion without sensing or a program', ['How ESP32 Wi-Fi works', 'How to flash ACECode', 'How L298N PWM works']),
                        $this->mc('Uno analog inputs are labelled like:', 'A0, A1, A2…', ['M1 and M2 only', 'Port 2 blue / Port 3 yellow only', 'HDMI 1 and HDMI 2']),
                        $this->tf('mBot mCore is Arduino-Uno compatible, but Robotics 2 expects you to wire your own circuits.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 2: Breadboard, Resistors, and Your First LED',
                'summary' => 'Build a series LED circuit on a breadboard with the correct resistor and jumper colour habits.',
                'objectives' => "Explain rows vs rails on a solderless breadboard\nChoose a current-limiting resistor for an LED\nWire 5 V, GND, and a digital pin without a short\nUse red for power and black/blue for ground\nMeasure continuity or voltage with a multimeter (teacher demo + pair try)",
                'guidance' => "Materials: breadboard, 220 Ω or 330 Ω resistors, LEDs, jumper wires, Uno, USB.\n0:00–0:15 Why LEDs die without resistors.\n0:15–0:45 Breadboard map: power rails, 5-hole rows.\n0:45–1:20 Wire LED + resistor to pin 13 and GND. No code yet — then power USB.\n1:20–1:45 Multimeter: diode/continuity. Fix backwards LEDs.\n1:45–2:00 Quiz.\nCoach: pin 13 often has an onboard LED — celebrate both.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>breadboard</strong> lets you prototype without soldering. Today you light an LED with a <strong>resistor</strong> so the LED survives.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:15</strong> Current, LED polarity (long leg = anode +).</li>
  <li><strong>0:15–0:45</strong> Breadboard anatomy.</li>
  <li><strong>0:45–1:20</strong> Build: pin 13 → resistor → LED → GND.</li>
  <li><strong>1:20–1:45</strong> Multimeter check.</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>Breadboard rules</h2>
<ul>
  <li>The long side rails are usually <strong>+</strong> and <strong>−</strong> (power and ground). Bridge them to Uno 5 V and GND.</li>
  <li>Each short row of 5 holes is connected inside. The gap in the middle splits the row.</li>
  <li>Colour habit: <strong>red</strong> = 5 V, <strong>black</strong> = GND, other colours = signals.</li>
</ul>

<h2>Why the resistor?</h2>
<p>An LED wants only a small current (often ~10–20 mA). A <strong>220 Ω or 330 Ω</strong> resistor in series with a 5 V pin is the classroom default. Skipping it can burn the LED or stress the pin.</p>

<h3>Circuit</h3>
<pre>Uno pin 13  --&gt;  220 Ω  --&gt;  LED anode  --&gt;  LED cathode  --&gt;  GND
</pre>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> one LED on pin 13 (onboard + external).</li>
  <li><strong>Core:</strong> two LEDs on pins 12 and 13, each with its own resistor.</li>
  <li><strong>Stretch:</strong> a third LED on pin 11. Draw the circuit in your notebook before wiring.</li>
</ol>

<h2>If it stays dark</h2>
<ul>
  <li>LED backwards.</li>
  <li>Resistor in the wrong row (not in series).</li>
  <li>GND not actually connected to the blue rail.</li>
  <li>USB not supplying the board (power LED on the Uno off).</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 2 — Breadboard and LED Quiz',
                    'description' => 'Wiring, resistors, and polarity.',
                    'questions' => [
                        $this->mc('On a typical LED the long leg is the:', 'Anode (+) that faces toward the 5 V / pin side of the series circuit', ['Cathode that must go to 12 V mains', 'Antenna for Wi-Fi', 'Motor encoder']),
                        $this->mc('A 220 Ω or 330 Ω resistor in an LED circuit is there to:', 'Limit current so the LED and pin are not damaged', ['Increase USB voltage to 24 V', 'Replace the Arduino', 'Act as an ultrasonic sensor']),
                        $this->mc('Breadboard power rails are usually used for:', '5 V and GND distributed along the board', ['HDMI video', 'Storing lithium cells', 'Programming ACECode']),
                        $this->mc('A good classroom colour habit is:', 'Red for 5 V, black for GND', ['Red for GND only', 'Rainbow random every wire', 'No colours ever']),
                        $this->mc('The gap down the middle of a breadboard:', 'Splits the rows so ICs can sit across it', ['Is a decoration only', 'Is the USB port', 'Supplies 12 V automatically']),
                        $this->mc('If the Uno power LED is off, first check:', 'USB cable and port', ['Whether the LCD has 16 characters', 'The ACEBOTT app password', 'Glue gun temperature']),
                        $this->mc('Two LEDs on two pins should each have:', 'Their own series resistor', ['One shared resistor for the whole lab', 'No GND', 'A stepper driver']),
                        $this->mc('A multimeter continuity beep helps you:', 'Confirm a wire or row is actually connected', ['Upload Python', 'Charge lithium through the probe', 'Flash ESP32 firmware']),
                        $this->mc('Pin 13 on many Unos is special because:', 'It often has an onboard LED as well', ['It is the only analog pin', 'It outputs 240 V', 'It is the Bluetooth antenna']),
                        $this->tf('You can skip the resistor if the LED looks bright enough.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 3: Arduino IDE and mBlock — setup(), loop(), and Blink',
                'summary' => 'Install or confirm Arduino IDE, upload Blink, and see the same idea in mBlock for Arduino.',
                'objectives' => "Select the correct board and COM port\nExplain setup() runs once and loop() repeats\nUse pinMode, digitalWrite, and delay\nUpload Blink to the breadboard LED\nCompare the sketch with mBlock Arduino blocks",
                'guidance' => "Install Arduino IDE + CH340/CP2102 drivers before class if clones are used. mBlock Arduino mode as backup.\n0:00–0:20 Drivers, Tools → Board → Uno, Port.\n0:20–0:50 File → Examples → 01.Basics → Blink. Upload.\n0:50–1:20 Change delays; add pin 12.\n1:20–1:45 Optional: same Blink in mBlock Arduino toolkit.\n1:45–2:00 Quiz.\nCoach: 'Board not in sync' → press reset, correct port, not ESP32 board selected by mistake.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Hardware from yesterday needs a <strong>program</strong>. Arduino C uses two functions: <strong>setup()</strong> (once) and <strong>loop()</strong> (forever). mBlock can talk to Arduino too — useful if ACECode is busy on another kit.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> Board + port. Drivers for clones.</li>
  <li><strong>0:20–0:50</strong> Upload Blink.</li>
  <li><strong>0:50–1:20</strong> Edit delays; second LED.</li>
  <li><strong>1:20–1:45</strong> mBlock Arduino path (optional parallel).</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>The smallest useful sketch</h2>
<pre>void setup() {
  pinMode(13, OUTPUT);
}

void loop() {
  digitalWrite(13, HIGH); // on
  delay(1000);            // 1000 ms = 1 s
  digitalWrite(13, LOW);  // off
  delay(1000);
}
</pre>

<h3>Upload checklist</h3>
<ol>
  <li>Tools → Board → Arduino Uno (or Nano + the right processor if asked).</li>
  <li>Tools → Port → the new COM port that appears when you plug USB.</li>
  <li>Verify (tick) then Upload (arrow).</li>
  <li>TX/RX LEDs flicker. Then your LED blinks.</li>
</ol>

<h2>mBlock / ACECode later</h2>
<p>Block apps still compile to similar ideas: set pin output, wait, repeat. ACECode Upload mode even <strong>shows the generated Arduino C and Python</strong> — we will use that in Week 4 so blocks are not a dead end.</p>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> Blink at 2 Hz (delay 250).</li>
  <li><strong>Core:</strong> pin 13 and pin 12 alternate (traffic start).</li>
  <li><strong>Stretch:</strong> three LEDs in sequence (red–yellow–green timing).</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 3 — Arduino Program Structure Quiz',
                    'description' => 'setup, loop, upload, and pins.',
                    'questions' => [
                        $this->mc('setup() is meant to run:', 'Once when the board starts or resets', ['Once per millivolt', 'Only when Wi-Fi is on', 'Never']),
                        $this->mc('loop() is meant to run:', 'Over and over after setup()', ['Only in ACEBOTT factory firmware', 'Once then halt forever', 'Only on Nano, not Uno']),
                        $this->mc('pinMode(13, OUTPUT) tells the chip:', 'Pin 13 will be used as an output', ['Pin 13 is an ultrasonic echo', 'To delete the bootloader', 'To enable HDMI']),
                        $this->mc('digitalWrite(13, HIGH) typically means:', 'Set the pin to 5 V (LED on if wired that way)', ['Set the pin to 0 V always', 'Read the DHT sensor', 'Start the glue gun']),
                        $this->mc('delay(1000) pauses about:', '1000 milliseconds (1 second)', ['1000 minutes', '1 microcentury', '16 MHz exactly one cycle']),
                        $this->mc('Before upload you must select:', 'The correct board type and COM port', ['A random printer', 'ESP32 even for a classic Uno', 'Mode C tape']),
                        $this->mc('mBlock can still be used in Robotics 2 to:', 'Program Arduino with blocks as a parallel path', ['Replace the USB cable with HDMI', 'Charge lithium packs', 'Act as an L298N']),
                        $this->mc('ACECode Upload mode is useful later because it:', 'Can show generated Arduino C and Python', ['Removes all USB ports', 'Disables blink', 'Replaces resistors with motors']),
                        $this->mc('If upload fails with the wrong port selected, you should:', 'Unplug/replug and pick the COM port that appears', ['Set pinMode to INPUT forever', 'Short 5 V to GND', 'Skip GND']),
                        $this->tf('An Arduino program that never calls loop() still repeats automatically without any loop function in the file.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 4: Buttons, Pull-ups, and the Serial Monitor',
                'summary' => 'Read a switch, print values to Serial, and control an LED with a button.',
                'objectives' => "Wire a push-button with a pull-down or use INPUT_PULLUP\nUse digitalRead in loop()\nOpen the Serial Monitor at 9600 baud\nDebounce in a simple way (short delay)\nBuild button-on / button-off LED control",
                'guidance' => "Materials: tactile switches, 10 kΩ pull-downs (or teach INPUT_PULLUP to save parts).\n0:00–0:20 Floating pins: why a pull resistor exists.\n0:20–1:00 Button + LED. Serial.println the state.\n1:00–1:40 Challenge: hold to blink, or toggle on press.\n1:40–2:00 Quiz.\nCoach: INPUT_PULLUP means pressed = LOW if the switch goes to GND.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>switch button</strong> is a digital sensor. The Arduino must not read a "floating" pin. Use a <strong>pull-down</strong> resistor to GND, or <strong>INPUT_PULLUP</strong> and a switch to GND.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> Floating vs pulled pins.</li>
  <li><strong>0:20–1:00</strong> Button controls LED + Serial.</li>
  <li><strong>1:00–1:40</strong> Toggle or hold-to-blink challenge.</li>
  <li><strong>1:40–2:00</strong> Quiz.</li>
</ul>

<h2>INPUT_PULLUP pattern (few extra parts)</h2>
<pre>void setup() {
  pinMode(2, INPUT_PULLUP); // button from pin 2 to GND
  pinMode(13, OUTPUT);
  Serial.begin(9600);
}

void loop() {
  int pressed = digitalRead(2) == LOW;
  Serial.println(pressed);
  digitalWrite(13, pressed ? HIGH : LOW);
  delay(20); // crude debounce
}
</pre>

<h3>Serial Monitor</h3>
<p>Tools → Serial Monitor. Baud must match <code>Serial.begin(9600)</code>. This is how we debug sensors next week without guessing.</p>

<h2>Practice</h2>
<ol>
  <li><strong>Starter:</strong> LED on while button is held.</li>
  <li><strong>Core:</strong> print 0/1 to Serial so the teacher can see it.</li>
  <li><strong>Stretch:</strong> toggle: each press flips the LED (need to detect the moment of press, not the level).</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 4 — Buttons and Serial Quiz',
                    'description' => 'Inputs, pull-ups, and debugging.',
                    'questions' => [
                        $this->mc('A floating digital input is a problem because:', 'It can randomly read HIGH or LOW', ['It charges the lithium pack', 'It turns the board into ESP32', 'It disables USB']),
                        $this->mc('INPUT_PULLUP with a switch to GND usually means pressed reads as:', 'LOW', ['Always 1023', '12 V', 'A DHT humidity percent']),
                        $this->mc('digitalRead() is used to:', 'See whether a digital pin is HIGH or LOW', ['Set motor PWM to 255 automatically', 'Draw on the LCD contrast', 'Upload ACECode']),
                        $this->mc('Serial.begin(9600) should match:', 'The Serial Monitor baud rate', ['The USB cable colour', 'The number of breadboard rows', 'The L298N peak current']),
                        $this->mc('A short delay after reading a button is a simple way to:', 'Reduce bounce (false extra presses)', ['Increase 5 V to 12 V', 'Replace the resistor on an LED', 'Flash factory ACEBOTT firmware']),
                        $this->mc('Serial.println() is mainly for:', 'Printing debug values to the computer', ['Driving a servo', 'Heating the glue gun', 'Pairing Bluetooth']),
                        $this->mc('A pull-down resistor holds an unpressed button pin at:', 'GND / LOW until the button connects 5 V (if wired that way)', ['HDMI', 'Wi-Fi channel 11', 'Stepper coil 4']),
                        $this->mc('Toggle-on-press is harder than hold-to-light because you must detect:', 'The change from not pressed to pressed', ['Only analog A0 weather', 'Only Mode C tape', 'The ESP32 camera']),
                        $this->mc('Pin 2 in the example is configured as:', 'An input', ['An L298N enable pin always', 'A 16x2 LCD RS pin always', 'A USB D+ line']),
                        $this->tf('You should still use Serial while learning sensors even if the robot has no screen.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 5: Week 1 Challenge — Traffic Light with Pedestrian Button',
                'summary' => 'Combine LEDs, delays, and a button into a timed traffic-light sequence.',
                'objectives' => "Plan a sequence on paper before coding\nDrive three LEDs as red, yellow, green\nInsert a pedestrian request that extends red\nUse Serial to announce states\nPresent a 30-second demo",
                'guidance' => "Materials: 3 LEDs + resistors, 1 button, cardboard traffic housing optional (ice-cream sticks).\n0:00–0:15 Recap pinMode/digitalWrite/digitalRead.\n0:15–0:35 State list on paper: green, yellow, red, walk.\n0:35–1:25 Build. Checkpoint: sequence works without button, then add button.\n1:25–1:45 Pair demos.\n1:45–2:00 Quiz.\nStretch: LCD later in Week 2 can show WALK / DON'T WALK.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Week 1 ends with a <strong>traffic light</strong>: three LEDs plus a pedestrian button. This is the same IPO idea as mBot, with wires you made yourself.</p>

<h3>Design rules</h3>
<ol>
  <li>Green (long), yellow (short), red (long), repeat.</li>
  <li>If the button was pressed during green, the next red lasts longer and a "walk" LED (or Serial message) appears.</li>
  <li>Never light green and red together.</li>
  <li>Start the cycle after a button or automatically — your choice, but document it.</li>
</ol>

<pre>// Sketch of states — fill pins to match your breadboard
const int RED = 11, YELLOW = 12, GREEN = 13, BTN = 2;

void lights(int r, int y, int g) {
  digitalWrite(RED, r);
  digitalWrite(YELLOW, y);
  digitalWrite(GREEN, g);
}
</pre>

<h2>Build order</h2>
<ol>
  <li>Sequence only (ignore button).</li>
  <li>Read button into a <code>bool walkRequested</code>.</li>
  <li>If requested, extra red time + Serial "WALK".</li>
  <li>Optional craft box from cardboard or sticks — electronics still must be visible for marking.</li>
</ol>

<p>Save as <strong>Day5-Traffic</strong>. Next week the "world" starts talking back through sensors.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 5 — Week 1 Traffic Challenge Quiz',
                    'description' => 'Sequences, buttons, and safe light states.',
                    'questions' => [
                        $this->mc('A traffic-light program is mainly a:', 'Timed sequence of outputs, optionally changed by an input', ['Wi-Fi web server', 'Stepper microstep table', 'ACEBOTT factory Mode C']),
                        $this->mc('Green and red together on a real signal would be:', 'Unsafe / incorrect — your code must not do that', ['Required by Arduino', 'How INPUT_PULLUP works', 'A way to charge batteries']),
                        $this->mc('Storing walkRequested as a bool is useful so that:', 'A press during green can affect the next red', ['USB becomes 12 V', 'The Nano becomes an Uno', 'L298N is bypassed']),
                        $this->mc('delay() during a long green makes it harder to:', 'Notice a button press unless you also poll often or use a shorter chunked wait', ['Upload Blink', 'See the onboard LED', 'Open Serial']),
                        $this->mc('Serial messages like WALK help the teacher:', 'See the state without extra hardware', ['Drive motors', 'Replace resistors', 'Pair RFID']),
                        $this->mc('Each LED still needs:', 'Its own series resistor', ['A shared 0 Ω jumper only', 'A servo horn', 'An ESP32']),
                        $this->mc('Planning states on paper first reduces:', 'Wiring and logic mistakes', ['The number of USB ports', 'Breadboard row count legally', 'Baud rate']),
                        $this->mc('This project uses the button as a:', 'Sensor / input', ['H-bridge', 'Lithium charger', 'Mecanum wheel']),
                        $this->mc('A cardboard housing is optional because:', 'The graded work is the circuit and the program', ['Arduino forbids craft', 'LEDs cannot work in boxes', 'Serial needs darkness']),
                        $this->tf('Week 1 is complete when you can light LEDs, read a button, and upload a loop that the teacher can watch.', true),
                    ],
                ],
            ],
        ];
    }

    private function week2Lessons(): array
    {
        return [
            [
                'title' => 'Day 6: Analog Light Sensor and PWM Brightness',
                'summary' => 'Read an LDR with analogRead and dim an LED with analogWrite.',
                'objectives' => "Contrast digital 0/1 with analog 0–1023 on Uno\nWire an LDR voltage divider\nPrint analog values and pick a night threshold\nUse PWM (analogWrite) on a PWM-capable pin\nBuild a night light (LED brighter when dark)",
                'guidance' => "Materials: LDR, 10 kΩ, LED on pin 9/10/11 (PWM). Avoid pin 13 for PWM demo.\n0:00–0:20 analogRead range. Cover vs torch.\n0:20–1:00 Divider wiring. Serial plot values.\n1:00–1:40 Night light mapping.\n1:40–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>mBot hid analog details. On Arduino, <strong>analogRead(A0)</strong> returns <strong>0–1023</strong> (10-bit). <strong>analogWrite(pin, 0–255)</strong> is PWM on pins marked ~ (Uno: 3, 5, 6, 9, 10, 11).</p>

<h3>LDR divider</h3>
<pre>5 V -- LDR -- A0 -- 10 kΩ -- GND
</pre>
<p>Calibrate in this room. Do not copy another table's threshold.</p>

<pre>int raw = analogRead(A0);
int pwm = map(raw, darkLow, darkHigh, 255, 0); // invert: dark → bright LED
pwm = constrain(pwm, 0, 255);
analogWrite(9, pwm);
</pre>

<h2>Practice</h2>
<ol>
  <li>Serial-print raw values: cover, room, torch.</li>
  <li>Night light on PWM pin 9.</li>
  <li>Stretch: yellow LED if medium, red if very dark.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 6 — Analog and PWM Quiz',
                    'description' => 'LDR, analogRead, analogWrite.',
                    'questions' => [
                        $this->mc('On a standard Uno, analogRead typically returns:', '0 to 1023', ['Only 0 or 1', '0 to 16 million', 'A Wi-Fi SSID']),
                        $this->mc('analogWrite on Uno PWM pins uses values:', '0 to 255', ['0 to 1023 only', 'Exactly 9600', '−255 to 12 V']),
                        $this->mc('An LDR is usually read with:', 'A voltage divider into an analog pin', ['A USB-C display port', 'The L298N OUT1 only', 'I2C address 0x00 only']),
                        $this->mc('PWM means the pin:', 'Switches rapidly so the LED looks dimmer or brighter', ['Outputs true analog 3.333 V only', 'Becomes an input automatically', 'Charges lithium']),
                        $this->mc('You should pick a darkness threshold by:', 'Measuring in this classroom', ['Always using 15 from mBot ultrasonic', 'Using 1023 always', 'Using the glue-gun temp']),
                        $this->mc('Uno PWM-capable pins include examples like:', '3, 5, 6, 9, 10, 11', ['All analog-only A0–A5 exclusively', 'USB D+ and D−', 'The crystal pins only']),
                        $this->mc('map() in Arduino is often used to:', 'Scale a sensor range into PWM 0–255', ['Upload to ESP32 automatically', 'Cut cardboard', 'Pair RFID']),
                        $this->mc('constrain() helps you:', 'Keep a value inside a min/max after mapping', ['Delete setup()', 'Disable Serial', 'Short 5 V to GND safely']),
                        $this->mc('A light sensor is:', 'An input', ['An H-bridge', 'A chassis beam', 'Factory firmware']),
                        $this->tf('analogWrite is true analog voltage like a laboratory power supply, not a pulse-width trick.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 7: Ultrasonic HC-SR04 — Trig, Echo, and Centimetres',
                'summary' => 'Measure distance with HC-SR04 on Arduino and compare it with mBot\'s Port 3 module.',
                'objectives' => "Name TRIG and ECHO pins\nExplain pulseIn and the speed-of-sound estimate\nPrint distance in cm\nSet a close-object LED\nCompare RJ25 mBot ultrasonic with jumper-wired HC-SR04",
                'guidance' => "5 V HC-SR04. If using 3.3 V boards later, use a divider on ECHO.\n0:00–0:20 mBot recap vs open wiring.\n0:20–1:10 Working sketch + LED threshold.\n1:10–1:40 Measure a metre stick; list bad surfaces (fabric, angle).\n1:40–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Makeblock's ultrasonic was a yellow RJ25 module. Here you wire <strong>VCC, TRIG, ECHO, GND</strong> yourself. Range is still on the order of a few centimetres to a few metres, with the same echo physics.</p>

<pre>const int TRIG = 9, ECHO = 10;

long cm() {
  digitalWrite(TRIG, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG, LOW);
  long us = pulseIn(ECHO, HIGH, 30000);
  if (us == 0) return -1; // timeout
  return us / 58;         // ~ us/58 ≈ centimetres
}
</pre>
<p>Many kits use <code>duration * 0.034 / 2</code> instead of <code>/ 58</code> — same idea: time of flight, divide by 2 for the round trip.</p>

<h2>Practice</h2>
<ol>
  <li>Serial-print cm.</li>
  <li>LED red if &lt; 15 cm.</li>
  <li>Stretch: beep a buzzer (if you have one) on red — keep it short.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 7 — HC-SR04 Ultrasonic Quiz',
                    'description' => 'Trig, echo, distance math, timeouts.',
                    'questions' => [
                        $this->mc('HC-SR04 needs at least which extra pins besides power?', 'TRIG and ECHO', ['Only SDA and SCL always', 'Only USB D+', 'M1 and M2']),
                        $this->mc('pulseIn measures:', 'How long a pulse stays HIGH (here, the echo)', ['Lithium capacity in Ah', 'Wi-Fi RSSI only', 'Stepper steps per revolution only']),
                        $this->mc('We divide by 2 in the speed-of-sound formula because:', 'The sound goes to the object and back', ['There are two Unos', 'PWM is 2-bit', 'Breadboards have two rails only']),
                        $this->mc('A timeout (pulseIn returns 0) often means:', 'No echo heard in time (too far, bad angle, or unplugged)', ['Perfect 15.000 cm always', 'The LED resistor is 220 Ω', 'Serial baud is 9600']),
                        $this->mc('Compared with mBot Port 3, this lesson is different because you:', 'Wire VCC/GND/TRIG/ECHO yourself', ['Cannot measure centimetres', 'Must use Mode C tape', 'Must use ACECode only']),
                        $this->mc('Soft fabric is a bad target because:', 'The echo may be weak or missing', ['It increases 5 V', 'It flashes ESP32', 'It is an H-bridge']),
                        $this->mc('A classroom "too close" LED threshold might start near:', '10–20 cm, then tune', ['400 m', '0.001 mm', '1023 metres']),
                        $this->mc('HC-SR04 VCC on Uno projects is usually:', '5 V', ['12 V from the wall', 'The glue-gun barrel', 'USB data']),
                        $this->mc('This sensor is still:', 'An input used to make decisions', ['A motor driver', 'A chassis tyre', 'A capacitor kit name']),
                        $this->tf('Ultrasonic distance on Arduino uses the same echo idea as mBot, with more wiring responsibility.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 8: DHT Climate Sensor and 16x2 LCD',
                'summary' => 'Read temperature and humidity and show them on a 16x2 LCD.',
                'objectives' => "Identify DHT data pin and library use at a beginner level\nWire a 16x2 LCD (parallel or I2C backpack if available)\nDisplay two lines of text\nRefresh slowly (sensors dislike flood reads)\nExplain header pin strips for LCD modules",
                'guidance' => "Prefer I2C LCD backpacks if the camp has them (fewer wires). Otherwise 4-bit parallel with contrast pot.\n0:00–0:20 What DHT11/DHT22 report.\n0:20–1:00 LCD hello world.\n1:00–1:40 Combine DHT + LCD.\n1:40–2:00 Quiz.\nInstall DHT and LiquidCrystal_I2C libraries on teacher laptops first.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p><strong>Temperature and humidity sensors</strong> (DHT11 / DHT22) give climate numbers. A <strong>16x2 LCD</strong> shows them without the Serial Monitor — important on a robot that leaves the laptop.</p>

<h3>LCD notes</h3>
<ul>
  <li>Many camp LCDs need <strong>header pin strips</strong> soldered — if not soldered, use a pre-soldered backpack or wait for Day 24.</li>
  <li>I2C backpack: SDA → A4, SCL → A5 on Uno (plus 5 V and GND).</li>
  <li>Contrast: a small pot on the backpack or a 10 kΩ pot on V0.</li>
</ul>

<pre>lcd.setCursor(0, 0);
lcd.print("Temp: ");
lcd.print(t);
lcd.setCursor(0, 1);
lcd.print("Hum:  ");
lcd.print(h);
delay(2000); // DHTs need time between reads
</pre>

<h2>Practice</h2>
<ol>
  <li>LCD shows your names on two lines.</li>
  <li>LCD shows T and H.</li>
  <li>Stretch: if H &gt; a threshold, turn on an LED "too humid".</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 8 — DHT and LCD Quiz',
                    'description' => 'Climate sensing and 16x2 displays.',
                    'questions' => [
                        $this->mc('A 16x2 LCD can show:', '2 lines of 16 characters each', ['Only analog 0–1023 as pictures', '4K video', 'Wi-Fi heat maps only']),
                        $this->mc('DHT sensors commonly report:', 'Temperature and humidity', ['Exact GPS latitude only', 'Motor RPM only', 'RFID UIDs only']),
                        $this->mc('Reading a DHT every 20 ms is a bad idea because:', 'The sensor needs time between readings', ['USB will reverse polarity', 'The Uno becomes a Nano', 'PWM stops legally']),
                        $this->mc('On Uno, hardware I2C is typically:', 'A4 (SDA) and A5 (SCL)', ['Pin 13 only', 'USB D+ / D−', 'L298N OUT3/OUT4']),
                        $this->mc('Header pin strips on an LCD are used to:', 'Plug the glass into a backpack or breadboard', ['Cut bamboo', 'Hold lithium cells', 'Replace jumper colours']),
                        $this->mc('If the LCD is blank, first try:', 'Contrast pot and power/GND', ['Setting baud to 1', 'Unplugging all resistors', 'Selecting board ESP32 for a 5 V Uno sketch without checking']),
                        $this->mc('lcd.setCursor(0, 1) moves to:', 'The first character of the second line', ['Analog A1', 'Servo 0 degrees', 'Wi-Fi channel 1']),
                        $this->mc('Showing data on an LCD instead of Serial helps when:', 'The robot is untethered from the laptop', ['You want to skip GND', 'You need 240 V', 'You are charging via ECHO']),
                        $this->mc('Libraries (DHT, LiquidCrystal) are:', 'Code others wrote so you can talk to the module', ['Types of chassis tyres', 'Glue sticks', 'MQ gas bottles']),
                        $this->tf('A climate display is a useful subsystem to reuse inside a smart-home or weather-station robot.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 9: PIR, MQ Gas, Water Level, and Color Sensors',
                'summary' => 'Tour the remaining analog/digital sensors with strict MQ-gas safety.',
                'objectives' => "Use a PIR as a digital motion flag\nTreat MQ gas sensors as lab instruments with ventilation rules\nRead a water-level analog probe without dunking electronics\nDescribe what a color sensor reports (RGB / frequency)\nLog which sensor you will use in the Day 10 station",
                'guidance' => "MQ: no spraying solvents. Optional: teacher demo with a safe alcohol wipe at a distance — or skip live gas and use Serial mock if policy requires.\nStations of 20 minutes: PIR, water, color, MQ (demo).\n1:40–2:00 Quiz + pick tomorrow's combination.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Rotate through <strong>basic sensors</strong> so you can invent next week, not just copy one wiring.</p>

<h3>PIR motion</h3>
<p>Digital pin: HIGH when a warm body moves in the cone. Great for alarms. Give it a few seconds to settle after power-up.</p>

<h3>MQ gas (safety first)</h3>
<ul>
  <li>Needs 5 V and time to heat. Do <strong>not</strong> sniff concentrated fumes or spray aerosols at your face.</li>
  <li>Treat readings as relative (higher number = more of whatever the sensor is tuned for), not a calibrated alarm.</li>
  <li>Ventilate. Teacher stops the station if anyone feels unwell.</li>
</ul>

<h3>Water level</h3>
<p>Analog strip. Only the sensor traces touch water — not the Uno. Dry the module after. Combine later with a pump/relay only under teacher rules.</p>

<h3>Color sensor</h3>
<p>Often TCS3200-style: LEDs + photodiode, reports a colour channel. Use coloured paper cards. Keep ambient light consistent.</p>

<h2>Exit ticket</h2>
<p>Write one sentence: which two sensors you will combine tomorrow and what the robot should do.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 9 — Sensor Tour Quiz',
                    'description' => 'PIR, MQ, water, color, safety.',
                    'questions' => [
                        $this->mc('A PIR sensor is typically used to detect:', 'Motion of a warm body in its cone', ['Exact pH of water', 'Wi-Fi passwords', 'Stepper coil current']),
                        $this->mc('After power-up a PIR often needs:', 'A short settle time before you trust the pin', ['To be connected to HDMI', '12 V into the Uno 5 V pin', 'ACECode factory Mode C']),
                        $this->mc('MQ gas sensors in this camp are:', 'Relative indicators used with ventilation and teacher rules', ['Toys to spray deodorant into', 'Replacements for lithium holders', 'LCD contrast pots']),
                        $this->mc('A water-level probe should:', 'Keep the Arduino itself out of the water', ['Be fully submerged with the Uno', 'Be powered from ECHO', 'Replace GND']),
                        $this->mc('A color sensor is most convincing when you:', 'Test with known coloured cards under similar lighting', ['Wave it at the sun only', 'Connect it to L298N OUT1', 'Use it as a motor']),
                        $this->mc('Capacitors in kits are often used to:', 'Smooth power or time analog circuits (not today\'s main actor)', ['Cut ice-cream sticks', 'Act as wheels', 'Replace USB cables']),
                        $this->mc('If anyone feels unwell at the MQ station you should:', 'Stop, ventilate, tell the teacher', ['Turn the sensor to 12 V', 'Cover the PIR with tape and continue spraying', 'Ignore it']),
                        $this->mc('These modules are still:', 'Inputs that feed if/else later', ['H-bridges', 'Mecanum rollers', 'Soldering guns']),
                        $this->mc('Combining PIR + LED tomorrow would be a simple:', 'Alarm or welcome light', ['Wi-Fi router', '4WD gearbox', 'Glue-gun PID loop']),
                        $this->tf('MQ readings in class are a full certified fire-alarm replacement.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 10: Mini Project — Night Security Station',
                'summary' => 'Build a two-sensor security gadget with LED/LCD/buzzer feedback.',
                'objectives' => "Combine at least two sensors with one or more outputs\nWrite nested ifs (both conditions vs either)\nShow state on LED and Serial or LCD\nTest false alarms and fix thresholds\nDemo in 45 seconds",
                'guidance' => "Suggested combos: PIR+LDR (motion only in the dark); ultrasonic+PIR; water+LED leak alarm.\n0:00–0:20 Choose combo, draw IPO.\n0:20–1:25 Build.\n1:25–1:45 Demos.\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Invent a <strong>night security station</strong> using Week 2 parts. Example: if it is dark <strong>and</strong> PIR sees motion, flash red and print ALARM. If light, stay idle.</p>

<h3>Nested logic reminder (from mBot Week 3, now in C)</h3>
<pre>int dark = analogRead(A0) &lt; threshold;
int motion = digitalRead(PIR) == HIGH;
if (dark &amp;&amp; motion) {
  digitalWrite(RED, HIGH);
  Serial.println("ALARM");
} else {
  digitalWrite(RED, LOW);
}
</pre>

<h2>Success test</h2>
<ul>
  <li>A visitor can trigger it on purpose.</li>
  <li>A visitor can see it is off in a non-alarm state.</li>
  <li>You can explain both sensors in one sentence each.</li>
</ul>
<p>Save <strong>Day10-Security</strong>. Week 3 adds motors so the station can become a patrol car.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 10 — Security Station Quiz',
                    'description' => 'Combining sensors and nested conditions.',
                    'questions' => [
                        $this->mc('dark &amp;&amp; motion means:', 'Both conditions must be true', ['Either condition is enough', 'Neither sensor is wired', 'PWM is 255']),
                        $this->mc('dark || motion would mean:', 'Either darkness or motion can trigger (depending on your design)', ['Both must be true always', 'The LCD is 20x4', 'USB is unplugged']),
                        $this->mc('A false alarm is fixed mainly by:', 'Thresholds, placement, and settle time — not by hoping', ['Removing GND', 'Always analogWrite 255', 'Skipping Serial']),
                        $this->mc('This mini project is graded on:', 'A visible success test plus a clear IPO explanation', ['Who has the longest jumper', 'Who uses the glue gun most', 'Who skips resistors']),
                        $this->mc('Reusing Serial or LCD for ALARM is:', 'Feedback so humans know the state', ['A motor driver technique', 'A lithium charging method', 'ESP32-only']),
                        $this->mc('PIR in the dark-only design is the:', 'Motion input', ['4WD tyre', 'H-bridge enable', 'Header strip']),
                        $this->mc('LDR in that design is the:', 'Light/dark input', ['Stepper coil', 'RFID tag', 'Mecanum roller']),
                        $this->mc('Saving a known-good copy matters because:', 'Last-minute edits can break a working demo', ['Arduino deletes USB at midnight', 'Breadboards expire hourly', 'Baud must be 1']),
                        $this->mc('Next week motors will let this idea become:', 'A moving patrol, not only a box on the table', ['A glue stick', 'A capacitor colour code', 'A drone without a controller']),
                        $this->tf('Nested ifs let you run an alarm only when more than one condition is true.', true),
                    ],
                ],
            ],
        ];
    }

    private function week3Lessons(): array
    {
        return [
            [
                'title' => 'Day 11: DC Motors, Battery Holders, and the Multimeter',
                'summary' => 'Spin a DC motor safely from a pack, measure voltage, and never back-feed the Uno.',
                'objectives' => "Identify motor polarity by swapping leads\nUse 2-way and 4-way battery holders correctly\nMeasure pack voltage with a multimeter\nExplain why a motor needs a driver, not a GPIO pin\nCable-tie strain relief",
                'guidance' => "Small DC motors + holders. No L298N yet — feel stall current risk conceptually.\n0:00–0:20 GPIO cannot drive motors.\n0:20–1:00 Pack → motor (no Arduino in the loop). Direction reverse.\n1:00–1:40 Multimeter voltage. Weak packs.\n1:40–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>DC motor</strong> is an actuator. Arduino pins can only source a tiny current. Classroom rule: <strong>motors get a battery pack + a driver chip</strong> (tomorrow: L298N). Today you feel the pack and the motor alone.</p>

<h3>Holders</h3>
<ul>
  <li>2-way and 4-way holders: match AA count to motor voltage (often 6 V from 4×AA).</li>
  <li>Lithium: only in a proper holder, correct polarity, switch off when wiring.</li>
</ul>

<h3>Multimeter</h3>
<p>DC voltage across the pack. If it is far below labelled voltage under load, replace cells. Continuity for broken jumper wires.</p>

<h2>Do not</h2>
<ul>
  <li>Connect a motor directly to pin 13.</li>
  <li>Share a collapsing motor ground poorly — we will star-ground at the driver tomorrow.</li>
  <li>Leave spinning wheels hanging off the table toward faces.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 11 — Motors and Power Quiz',
                    'description' => 'Packs, meters, and why GPIO is not a motor driver.',
                    'questions' => [
                        $this->mc('An Arduino GPIO pin is a bad motor supply because:', 'It cannot safely provide motor current', ['It is 240 V', 'It is analog-only', 'It is an LCD']),
                        $this->mc('Swapping a DC motor\'s two leads usually:', 'Reverses rotation', ['Deletes firmware', 'Changes USB baud', 'Turns it into a servo']),
                        $this->mc('A 4-way AA holder is typically about:', '6 V if cells are ~1.5 V each (alkaline)', ['48 V', '5 V USB data', '3.3 V always exactly']),
                        $this->mc('A multimeter on DC volts across a pack tells you:', 'Whether the pack still has useful voltage', ['The Wi-Fi password', 'Stepper microsteps', 'ACECode version']),
                        $this->mc('Lithium packs must:', 'Sit in a rated holder with correct polarity', ['Be loose in a pencil case', 'Feed the ECHO pin', 'Replace GND']),
                        $this->mc('Cable ties on motor wires are for:', 'Strain relief so solder/crimps do not rip', ['Increasing PWM frequency', 'I2C addressing', 'RFID UIDs']),
                        $this->mc('Wheels should be:', 'Held so they cannot run into people while testing', ['Aimed at faces for fun', 'Connected to 5 V pin 13', 'Covered in water-level traces']),
                        $this->mc('Tomorrow\'s L298N is:', 'An H-bridge motor driver', ['A temperature sensor', 'A 16x2 LCD', 'A glue stick']),
                        $this->mc('Chassis tyres and small chassis kits will be used to:', 'Build a mobile 4WD platform', ['Store resistors', 'Heat the soldering iron', 'Replace breadboards']),
                        $this->tf('It is safe to power a stalling drive motor from the Uno 5 V pin.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 12: L298N H-Bridge — PWM Speed and Turns',
                'summary' => 'Drive two DC motors with L298N: direction pins, enable PWM, and a first 4WD-style turn.',
                'objectives' => "Name IN1–IN4 and ENA/ENB\nSet a truth table for forward, reverse, stop\nUse analogWrite on enable pins for speed\nShare grounds between pack and Uno\nDrive a two-motor swing or pivot turn",
                'guidance' => "Classic L298N module with 5 V jumper caution: many modules can feed 5 V out — do not also USB-power blindly. Prefer separate logic 5 V from USB and motor pack to VM, common GND.\n0:00–0:25 H-bridge idea: current direction.\n0:25–1:20 Wire one motor, then two.\n1:20–1:45 Square on the floor (slow).\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>An <strong>H-bridge</strong> (L298N in this lab) lets firmware reverse a motor by switching which terminal is + and −. ACEBOTT cars hide a similar driver (often L293D-class). You will see the pins.</p>

<h3>Typical truth (one motor on IN1/IN2)</h3>
<ul>
  <li>IN1 HIGH, IN2 LOW → forward</li>
  <li>IN1 LOW, IN2 HIGH → reverse</li>
  <li>Both LOW (or both HIGH on some chips) → stop/brake — check your module</li>
  <li>ENA PWM → speed</li>
</ul>

<pre>analogWrite(ENA, 180);
digitalWrite(IN1, HIGH);
digitalWrite(IN2, LOW);
</pre>

<h3>Turns</h3>
<p>Left motor forward, right reverse = pivot. One motor stopped = swing. 4WD ACEBOTT V2 can command <strong>four motors independently</strong> — same idea, more wheels.</p>

<h2>Ground</h2>
<p>Uno GND must meet the motor-pack GND at the driver. Otherwise "it only works on USB" mysteries appear.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 12 — L298N H-Bridge Quiz',
                    'description' => 'Direction pins, PWM, common ground.',
                    'questions' => [
                        $this->mc('L298N is used as a:', 'Dual H-bridge motor driver', ['Humidity sensor', 'OLED camera', 'RFID reader']),
                        $this->mc('PWM on ENA/ENB mainly controls:', 'Speed', ['I2C address', 'USB protocol', 'LCD columns']),
                        $this->mc('Reversing IN1/IN2 relative to each other:', 'Reverses that motor', ['Changes baud from 9600 to 115200 automatically', 'Flashes ACEBOTT firmware', 'Cuts bamboo']),
                        $this->mc('Uno GND and motor pack GND should:', 'Be connected together (common ground)', ['Never meet', 'Meet only at the glue gun', 'Meet on the ECHO pin']),
                        $this->mc('A pivot turn typically means:', 'Motors on left and right run opposite directions', ['Both motors unplugged', 'Only the caster is powered', 'Wi-Fi off']),
                        $this->mc('Driving motors at 255 on a slick floor often:', 'Skids and is hard to control — start slower', ['Increases analogRead max to 2047', 'Charges the DHT', 'Disables L298N heat']),
                        $this->mc('ACEBOTT 4WD kits also use a motor driver chip so that:', 'The controller is not asked to pass stall current', ['The app can run without firmware', 'Mecanum wheels become LEDs', 'USB supplies 12 V motors directly']),
                        $this->mc('If the motor pack is on but logic has no GND reference, behaviour is often:', 'Random or dead inputs', ['Perfect squares', 'Automatic line follow', 'RFID dumps UIDs']),
                        $this->mc('Stop should be an explicit state in your code so that:', 'The robot can halt for people and for the end of a demo', ['PWM becomes analogRead', 'The Nano melts', 'Serial closes']),
                        $this->tf('You can treat L298N IN pins like digitalWrite outputs from the Arduino.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 13: Servo Motors — Sweep and Sensor Pan',
                'summary' => 'Control a servo with the Servo library and mount an ultrasonic on a pan bracket.',
                'objectives' => "Explain a servo as a positioned actuator (0–180° typical)\nUse Servo.h attach and write\nGive servos a solid 5 V supply\nSweep, then point at three headings and read HC-SR04\nCable-manage so the head does not rip wires",
                'guidance' => "Hobby 9g servos. External 5 V if jittering.\n0:00–0:20 Servo vs continuous DC motor.\n0:20–1:00 Sweep sketch.\n1:00–1:40 Pan + ultrasonic distances at 45/90/135.\n1:40–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>servo motor</strong> goes to an angle. That is how robot arms, steering, and ultrasonic "look left/right" work. Stepper motors (tomorrow) move in counted steps instead.</p>

<pre>#include &lt;Servo.h&gt;
Servo s;
void setup() { s.attach(6); }
void loop() {
  s.write(0); delay(500);
  s.write(90); delay(500);
  s.write(180); delay(500);
}
</pre>

<h3>Look-around pattern</h3>
<p>Write 45°, read ultrasonic, write 90°, read, write 135°, read. Choose the clearest heading. This is the seed of ACEBOTT "avoid obstacles in multiple directions".</p>

<h2>Power</h2>
<p>Servos spike current. If the Uno resets when the servo moves, use a separate 5 V pack and common GND.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 13 — Servo Quiz',
                    'description' => 'Angles, supply, and sensor pan.',
                    'questions' => [
                        $this->mc('A typical hobby servo command is an angle around:', '0 to 180 degrees', ['0 to 1023 metres', 'Only on/off like a relay', '9600 baud']),
                        $this->mc('Servo.h attach() names:', 'Which Arduino pin drives the servo signal', ['The lithium chemistry', 'The Wi-Fi SSID', 'The LCD I2C address only']),
                        $this->mc('If the board resets when the servo twitches, suspect:', 'Power sag — give the servo its own 5 V and common GND', ['Too much Serial text', 'Wrong breadboard colour', 'Missing Mode C tape']),
                        $this->mc('Panning ultrasonic on a servo helps you:', 'Sample distance in more than one direction', ['Charge MQ sensors', 'Replace L298N', 'Cut header pins']),
                        $this->mc('A servo is an:', 'Actuator', ['LDR', 'PIR crystal', 'CH340 chip']),
                        $this->mc('Continuous-rotation "servos" behave more like:', 'Speed-controlled motors, not angle servos — know which type you have', ['16x2 LCDs', 'RFID tags', 'Glue guns']),
                        $this->mc('Cable management on a pan head prevents:', 'Wires ripping out as the horn turns', ['PWM from existing', 'analogRead from working on A0', 'USB enumerating']),
                        $this->mc('ACEBOTT multi-direction avoid is the kit version of:', 'Looking or sensing more than straight ahead', ['Soldering only', 'Codeless drones', 'Breadboard power rails']),
                        $this->mc('s.write(90) on a standard servo is often:', 'Centre', ['USB bootloader', 'Full reverse on L298N IN1 only', 'LCD line 90']),
                        $this->tf('Servos and DC drive motors are interchangeable in every circuit without changing code or wiring.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 14: Stepper Motors and 4WD Chassis Assembly',
                'summary' => 'Drive a stepper for precise motion, then assemble wheels, tyres, and a 4WD or small chassis.',
                'objectives' => "Contrast stepper (steps) with DC (spin) and servo (angle)\nWire a ULN2003 or similar driver if that is the camp stepper\nCount steps for a quarter turn\nAssemble chassis, tyres, screws, cable ties\nLeave room for Uno/Nano, L298N, and pack",
                'guidance' => "28BYJ-48 + ULN2003 is common. If missing, demo one station and others assemble 4WD.\n0:00–0:30 Stepper demo.\n0:30–1:50 Chassis build: wheels, motor mounts, battery tray, electronics deck.\n1:50–2:00 Quiz.\nCoach: keep screws in a lid. Photograph wiring before tightening the top plate.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p><strong>Stepper motors</strong> move in steps — good for plotter-like precision or a gripper. <strong>4WD chassis kits</strong> are about structure: motors, tyres, screws, nuts, adhesives only where the teacher allows.</p>

<h3>Stepper idea</h3>
<p>Energise coils in sequence. Libraries such as <code>Stepper.h</code> hide the sequence. A 2048-step 28BYJ-48 can turn a known angle if you do the maths — or just count what a "quarter turn" is on your motor.</p>

<h3>Chassis build quality</h3>
<ul>
  <li>Wheels tight but not crushing the gearbox.</li>
  <li>Wires away from tyres.</li>
  <li>Switch accessible.</li>
  <li>Room for breadboard or ACEBOTT deck — do not bury USB.</li>
</ul>

<p>Make Brick / LEGO can add a bumper or phone holder. Ice-cream sticks and bamboo are braces, not electrical insulation — keep them off live terminals.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 14 — Stepper and Chassis Quiz',
                    'description' => 'Steps vs spin, and mechanical build quality.',
                    'questions' => [
                        $this->mc('A stepper motor is designed to move in:', 'Discrete steps for position control', ['Random Wi-Fi packets', 'Only 0 or 180 like a broken servo', 'Audio frequencies only']),
                        $this->mc('A common classroom 5 V stepper driver is:', 'ULN2003 (with 28BYJ-48)', ['HC-SR04', 'DHT11', 'RC522 RFID only']),
                        $this->mc('4WD means:', 'Four driven wheels', ['Four Unos', 'Four LCDs', 'Four glue guns']),
                        $this->mc('Screws and nuts on a chassis are:', 'Structural fasteners — count them at pack-up', ['PWM values', 'Serial baud options', 'I2C addresses']),
                        $this->mc('Tyres rubbing the chassis will:', 'Waste power and stall motors', ['Improve analogRead', 'Charge lithium faster', 'Fix floating pins']),
                        $this->mc('USB should remain:', 'Reachable for programming', ['Glued shut', 'Wired to L298N OUT2', 'Dipped in the water sensor']),
                        $this->mc('Craft sticks on a robot are best as:', 'Mechanical braces, kept off live metal', ['5 V conductors', 'ECHO signal wires', 'H-bridge heatsinks required by law']),
                        $this->mc('Photographing the wiring before closing the deck helps:', 'Debug later without a full teardown', ['Increase RPM past stall', 'Skip GND', 'Replace ACECode']),
                        $this->mc('LEGO / Make Brick on the chassis is for:', 'Bumpers, mounts, and creative bodies', ['Replacing the bootloader', 'Generating 12 V from 5 V magically', 'MQ calibration gas']),
                        $this->tf('Stepper, servo, and DC motor are three different motion styles you might mix on one robot.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 15: Drive Your Arduino 4WD — Shapes, Stop, and Avoid',
                'summary' => 'Program the camp 4WD (Uno + L298N) to drive shapes and stop for ultrasonic.',
                'objectives' => "Combine L298N helpers: forward, back, left, right, stop\nTune PWM so the robot is controllable indoors\nAdd HC-SR04 stop-and-turn\nOptional servo pan from Day 13\nSave a known-good Day15 sketch",
                'guidance' => "Open floor. Cones. One robot at a time in the arena.\n0:00–0:15 Helper functions on the board.\n0:15–1:00 Square / slalom.\n1:00–1:40 Avoid.\n1:40–2:00 Quiz. Next week ACEBOTT does this with a kit brain and app.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Put Week 3 on the floor. Your <strong>Arduino 4WD</strong> (or 2WD if that is the kit) must start, drive, stop on command, and not hit a bag.</p>

<pre>void forward(int spd) {
  analogWrite(ENA, spd); analogWrite(ENB, spd);
  digitalWrite(IN1, HIGH); digitalWrite(IN2, LOW);
  digitalWrite(IN3, HIGH); digitalWrite(IN4, LOW);
}
void halt() {
  analogWrite(ENA, 0); analogWrite(ENB, 0);
}
</pre>

<h3>Avoid loop</h3>
<p>If distance &lt; 15 cm → halt → reverse briefly → turn → else forward. Same algorithm as mBot Day 7, now with your driver.</p>

<h2>Success</h2>
<ul>
  <li>Button or Serial 'g' starts, 's' stops (pick one).</li>
  <li>30 seconds in the arena without a hard crash.</li>
  <li>Wires still attached at the end.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 15 — Arduino 4WD Challenge Quiz',
                    'description' => 'Helpers, avoidance, and indoor speed.',
                    'questions' => [
                        $this->mc('Helper functions like forward() are useful because:', 'You reuse one tested motion block instead of copying pin writes', ['They charge the pack', 'They replace ultrasonic physics', 'They disable GND']),
                        $this->mc('Indoor first speed should be:', 'Moderate PWM, then increase', ['Always 255', 'Always 0', 'Random 0–1023 on ENA']),
                        $this->mc('halt() should zero enables (or both direction pins to stop) so that:', 'The robot actually stops for people and obstacles', ['Serial baud drops', 'The LCD contrast rises', 'RFID writes tags']),
                        $this->mc('Avoidance still needs a:', 'forever-style loop that keeps reading the sensor', ['Single if in setup only', 'Glue-gun PID', 'Codeless drone firmware']),
                        $this->mc('This project proves you can:', 'Wire power, driver, motors, and a sensor into one behaviour', ['Skip the H-bridge', 'Run motors from pin 13', 'Ignore common ground']),
                        $this->mc('A start button is good because:', 'You can place the robot, then run', ['USB enumerates faster', 'Lithium becomes alkaline', 'ACECode uninstalls']),
                        $this->mc('If left and right motors fight (spin in place unintentionally), check:', 'IN pin mapping vs physical left/right', ['Whether A0 is named A5', 'The 16x2 backlight colour', 'MQ preheat only']),
                        $this->mc('Week 4 ACEBOTT will hide some wiring but you will still need:', 'The same ideas: speed, turn, sense, loop', ['A new law of physics', 'To abandon if/else', 'To remove all batteries']),
                        $this->mc('Arena testing with bags is to:', 'Tune threshold and speed without hurting people', ['Calibrate glue temperature', 'Flash Nano bootloaders', 'Print RFID UID fonts']),
                        $this->tf('A 4WD Arduino car that cannot stop is not demo-safe.', true),
                    ],
                ],
            ],
        ];
    }

    private function week4Lessons(): array
    {
        return [
            [
                'title' => 'Day 16: ACEBOTT QD001 V2 — Assemble, Firmware, App, and IR',
                'summary' => 'Build the ACEBOTT V2 4WD car and use out-of-the-box firmware, IR remote, and the ACEBOTT app.',
                'objectives' => "Identify V2 upgrades: printed manual, factory firmware, independent 4-motor control\nAssemble using the kit manual (screws, wheels, sensors)\nDrive with IR and the official mobile app\nName ACECode, Arduino, and Python as later paths\nContrast kit car vs homemade Uno 4WD",
                'guidance' => "One QD001 V2 per pair if possible. Charge packs first. App: ACEBOTT on student phones/tablets with camp Wi-Fi rules.\n0:00–0:20 V1 vs V2 table on the board.\n0:20–1:15 Assemble. Teacher checks battery polarity.\n1:15–1:45 IR + app drive (no coding yet — V2 firmware is pre-installed).\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>The <strong>ACEBOTT QD001 Smart Car V2</strong> is a STEM 4WD platform (ESP32). Official materials: mecanum/omni-style motion on many packs, line tracking, obstacle avoid, IR, web/app control, 16 tutorials. V2 is built for classrooms: <strong>printed assembly manual</strong>, <strong>factory firmware so it moves after assembly</strong>, and <strong>independent control of four motors</strong>.</p>

<h3>Session agenda</h3>
<ul>
  <li><strong>0:00–0:20</strong> Why a kit after three weeks of raw Arduino.</li>
  <li><strong>0:20–1:15</strong> Assemble. Keep screws in a lid.</li>
  <li><strong>1:15–1:45</strong> IR remote and ACEBOTT app (virtual wheel / gears if your app build has them).</li>
  <li><strong>1:45–2:00</strong> Quiz.</li>
</ul>

<h2>V2 vs homemade Uno car</h2>
<ul>
  <li>Kit: faster mechanical success, onboard Wi-Fi/Bluetooth, official tutorials.</li>
  <li>Uno 4WD: you understand every wire. Both skills matter.</li>
</ul>

<h2>Programming you will use this week</h2>
<ul>
  <li><strong>ACECode</strong> — Scratch-like blocks, robot modules, Online debug + Upload to the board.</li>
  <li><strong>Arduino IDE</strong> — same family as Weeks 1–3; ESP32 board package required.</li>
  <li><strong>mBlock</strong> — backup block path if ACECode install fails.</li>
  <li><strong>ACEBOTT App</strong> — remote and (with matching firmware) mobile learning.</li>
</ul>

<p>Do not skip the kit's line and ultrasonic mounts — Week 4 coding needs them seated like mBot Port 2/3, but on ACEBOTT connectors.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 16 — ACEBOTT V2 Getting Started Quiz',
                    'description' => 'V2 firmware, app, IR, and kit vs DIY.',
                    'questions' => [
                        $this->mc('ACEBOTT QD001 V2 is designed around:', 'An ESP32 controller', ['Only an Uno with no wireless', 'A 16x2 LCD as the brain', 'A glue gun MCU']),
                        $this->mc('A headline V2 classroom upgrade is:', 'Factory firmware so the car can be used right after assembly', ['Removing all motors', 'Banning the printed manual', 'Requiring Python before the wheels turn']),
                        $this->mc('V2 motor control is described as:', 'Independent control of four motors', ['One speed for all wheels with no turning', 'No PWM', 'Stepper-only']),
                        $this->mc('Official programming languages listed for the kit include:', 'ACECode (blocks), Arduino, and Python', ['Only Excel macros', 'Only SQL', 'Only Morse']),
                        $this->mc('The ACEBOTT mobile app is used to:', 'Drive and learn with the car (firmware must match)', ['Solder header pins', 'Cut chassis aluminium', 'Calibrate MQ in a closed box']),
                        $this->mc('IR control on the kit is similar in spirit to:', 'mBot factory Mode A / a TV-style remote', ['DHT humidity', 'L298N IN3 only', 'Water-level ADC']),
                        $this->mc('ACECode is:', 'A Scratch-like block editor with robot modules', ['A type of lithium cell', 'An H-bridge brand', 'A PIR lens']),
                        $this->mc('You still assembled an Uno 4WD so that you:', 'Understand drivers and power when the kit hides them', ['Can skip all sensors forever', 'Avoid USB', 'Replace ESP32 with cardboard']),
                        $this->mc('Screws from the ACEBOTT bag should:', 'Stay counted in a lid during the build', ['Go into the glue gun', 'Be stored in water', 'Be used as jumper wires']),
                        $this->tf('QD001 V2 tutorials were upgraded toward clearer knowledge points for classroom teaching, not only stories.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 17: ACECode — Install, Online Mode, Upload Mode, and Blink',
                'summary' => 'Install ACECode, connect the serial port, blink an onboard LED, then upload so the car runs untethered.',
                'objectives' => "Install ACECode from acebott.com/software\nConnect the correct COM port\nExplain Online (live debug) vs Upload (runs without the PC)\nUse the upload hat / start-the-program block\nNotice generated Arduino C / Python in Upload mode",
                'guidance' => "Download ACECode before class onto a shared drive. USB data cables, not charge-only.\n0:00–0:25 Install + board list (ESP32).\n0:25–1:00 Online blink.\n1:00–1:35 Upload mode; unplug; external power switch.\n1:35–1:50 Show generated C. Optional mBlock if ACECode fails.\n1:50–2:00 Quiz.\nHint from docs: USB power may be weak — use the kit battery and power switch.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p><strong>ACECode</strong> is ACEBOTT's graphical tool (Scratch 3.0 + robot blocks). It supports <strong>Online mode</strong> (debug while connected) and <strong>Upload mode</strong> (program stays on the ESP32; unplug the PC; keep the pack on).</p>

<h3>Connect</h3>
<ol>
  <li>Select the ESP32 / smart-car device in ACECode.</li>
  <li>Plug USB. Pick the new COM port (number varies).</li>
  <li>If it says no device: cable, driver, power switch, another USB jack.</li>
</ol>

<h3>Online blink</h3>
<p>Official getting-started: flash the onboard LED 1 s on / 1 s off, then click to run. Running blocks highlight.</p>

<h3>Upload mode</h3>
<ol>
  <li>Switch to Upload.</li>
  <li>Change the hat to the upload start block ("start the program" in the docs).</li>
  <li>Watch generated <strong>C and Python</strong> — this is your bridge from blocks to Week 1 Arduino.</li>
  <li>Upload to 100%. Unplug USB. Power from the kit battery.</li>
</ol>

<p>Add the <strong>Smart Car extension</strong> when you are ready for motors (tomorrow). Today is connect + blink + upload confidence.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 17 — ACECode Modes Quiz',
                    'description' => 'Install, COM port, online vs upload.',
                    'questions' => [
                        $this->mc('ACECode is downloaded from:', 'ACEBOTT\'s official software page', ['A random USB stick of unknown origin', 'The glue-gun carton', 'The DHT datasheet']),
                        $this->mc('Online mode is best for:', 'Live debugging while the USB cable is connected', ['Running for hours in the garden with no PC and no upload', 'Soldering', 'MQ calibration in a cupboard']),
                        $this->mc('Upload mode is best for:', 'A program that keeps running after you unplug the computer', ['Only drawing Scratch sprites with no robot', 'Charging via ECHO', 'Replacing tyres']),
                        $this->mc('In upload mode the docs tell you to start with:', 'The upload start / "start the program" hat', ['A random forever with no hat', 'Python pip install', 'Excel Solver']),
                        $this->mc('ACECode upload mode can show:', 'Generated Arduino C and Python', ['Only Morse code', 'Only L298N silkscreen', 'Only RFID UID fonts']),
                        $this->mc('If USB power is weak the docs recommend:', 'External kit power and the power switch', ['Shorting 5 V to GND', 'Removing all motors forever', 'Setting baud to 1']),
                        $this->mc('The COM port number:', 'Can change; pick the one that appears when you plug in', ['Is always COM1 worldwide', 'Is the same as analog 1023', 'Is the LCD contrast']),
                        $this->mc('mBlock is in this course as:', 'A backup block environment if ACECode cannot run', ['The only legal ACEBOTT language', 'A motor driver', 'A lithium chemistry']),
                        $this->mc('ESP32 is selected in ACECode because:', 'That is the QD001 controller family', ['The Uno has no USB', 'LCDs require ESP32', 'PIR requires ESP32']),
                        $this->tf('After a successful upload the ESP32 can run the program without the laptop if it has power.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 18: Four Independent Motors — Mecanum Moves and Drive Shapes',
                'summary' => 'Use ACECode smart-car motor blocks for forward, strafe, spin, and a slow indoor square.',
                'objectives' => "Enable the Smart Car extension\nCommand four motors, not one 'pair speed'\nTry sideways / diagonal moves if the kit has mecanum wheels\nDrive a square and a spin-in-place\nKeep indoor PWM modest",
                'guidance' => "Open space. Mecanum floors: smooth helps. If the camp has standard 4WD tyres not mecanum, teach independent left/right pairs still.\n0:00–0:20 Extension on.\n0:20–1:20 Motion palette: forward, back, left, right, rotate.\n1:20–1:45 Square challenge.\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>V2's teaching point: <strong>four motors can be set independently</strong>. Mecanum / omni wheels (on kits that include them) add sideways "strafe". If your class set has standard 4WD rubber tyres, you still program left vs right pairs — same mathematics as L298N, nicer blocks.</p>

<h3>ACECode pattern</h3>
<pre>when program starts (upload hat)
set all motors speed (40%)
move forward (1) seconds
spin clockwise (0.4) seconds
stop
</pre>
<p>Exact block names follow your ACECode version — use the Smart Car / chassis category.</p>

<h3>Why independence matters</h3>
<ul>
  <li>Tighter turns and posture fixes for teaching path planning.</li>
  <li>One failed motor is easier to diagnose (which corner is dead?).</li>
  <li>Matches advanced ACEBOTT expansions (arm, camera) that need a stable base.</li>
</ul>

<h2>Practice</h2>
<ol>
  <li>Forward 1 s, stop.</li>
  <li>Square.</li>
  <li>Stretch: sideways if mecanum; otherwise pivot 360° and halt on a mark.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 18 — ACEBOTT 4WD Motion Quiz',
                    'description' => 'Independent motors and indoor driving.',
                    'questions' => [
                        $this->mc('Independent four-motor control means:', 'Each wheel can be commanded, not only one global speed', ['The app removes three wheels', 'ESP32 has four Unos inside', 'PWM is illegal']),
                        $this->mc('Mecanum wheels are useful because they can:', 'Move in more directions (including sideways on a good floor)', ['Measure humidity', 'Replace ultrasonic', 'Solder LCDs']),
                        $this->mc('If the kit has ordinary 4WD tyres, you should still:', 'Program left and right sides as separate groups', ['Give up on turning', 'Unplug two motors as a rule', 'Use only the glue gun']),
                        $this->mc('Indoor first tests should use:', 'Low to medium speed', ['Maximum always', 'Zero forever', 'Random 0–1023 on all wheels']),
                        $this->mc('stop at the end of a shape is required so that:', 'The demo is safe', ['Wi-Fi turns off', 'The LCD contrast resets', 'RFID wipes']),
                        $this->mc('The Smart Car extension in ACECode adds:', 'Blocks for this chassis and its sensors', ['Microsoft Excel', 'A new glue-gun mode', 'MQ legal certification']),
                        $this->mc('A dead corner wheel is easier to spot when:', 'Motors are independent and you can test one at a time', ['All speeds are tied and you never look', 'You cover the IR remote', 'You remove GND']),
                        $this->mc('This day is the kit version of:', 'Arduino L298N forward/turn helpers', ['DHT libraries only', 'Water-level ADC only', 'Header pin soldering only']),
                        $this->mc('Upload still needs:', 'The correct start hat and a successful COM upload', ['HDMI', 'A 240 V motor pack into ESP32 3.3 V pin', 'No battery ever']),
                        $this->tf('V2\'s independent motors support clearer lessons about turning and path planning than a single shared speed.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 19: ACEBOTT Multi-Way Line Tracking',
                'summary' => 'Follow lines with the kit\'s line-patrol sensors using ACECode if/else, and compare with mBot 0–3 values.',
                'objectives' => "Mount the line sensors at the correct height\nRead multi-way line states in ACECode\nSteer toward the line\nTune speed for gentle curves\nCompare with Robotics 1 line values 0–3",
                'guidance' => "Black tape on light floor. ACEBOTT often has multiple IR probes (multi-way cruising in marketing).\n0:00–0:20 Recap mBot 0–3.\n0:20–0:50 Live readouts (LEDs or say the state).\n0:50–1:35 Follower.\n1:35–1:45 Oval challenge.\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>ACEBOTT advertises <strong>multiple-route / multi-way line tracking</strong>. Mechanically it is still IR looking at tape, like mBot's Port 2 — usually <strong>more probes</strong>, so you can handle wider lines and junctions if you write the ifs.</p>

<h3>Method</h3>
<ol>
  <li>Hold the car and watch each probe on/off (or analog) in Online mode.</li>
  <li>Write the table for <em>this</em> tape and floor.</li>
  <li>If left-of-centre sees background → steer left, etc.</li>
  <li>If all probes lost → slow search spin (mBot Day 9 idea).</li>
</ol>

<h3>Junctions (stretch)</h3>
<p>If two outer probes see line, you may be at a cross. Decide: always left, always right, or stop. Document the rule.</p>

<p>Arduino-path students may open generated C after it works in blocks.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 19 — ACEBOTT Line Tracking Quiz',
                    'description' => 'Multi-probe lines vs mBot, tuning, junctions.',
                    'questions' => [
                        $this->mc('Line tracking still depends on:', 'Contrast between tape and floor', ['The DHT humidity only', 'RFID UIDs', 'Glue temperature']),
                        $this->mc('You must confirm probe meanings on:', 'Your actual tape and lighting', ['A random YouTube screenshot only', 'The lithium label', 'The LCD contrast']),
                        $this->mc('Multi-way sensors compared with mBot\'s two probes can:', 'Give more information at junctions and wide lines', ['Remove the need for any if/else', 'Charge the pack', 'Replace ESP32']),
                        $this->mc('If the car leaves the oval, first try:', 'Lower speed and smaller corrections', ['PWM 255 and hope', 'Unplugging ultrasonic', 'Soldering the IR remote']),
                        $this->mc('A search spin when all probes lose the line is:', 'A recovery behaviour', ['A gas-sensor preheat', 'A servo attach pin', 'A CH340 driver']),
                        $this->mc('This is the ACEBOTT version of Robotics 1:', 'Mode C / line follower lessons', ['Traffic-light Week 1 only', 'MQ safety only', 'Codeless drones']),
                        $this->mc('An always-left rule at a cross is useful because:', 'It is consistent and testable', ['It disables four motors', 'It wipes firmware', 'It is required by USB']),
                        $this->mc('Online mode helps today because:', 'You can watch probe values live while you hold the car', ['It uploads factory Mode A only', 'It heats MQ', 'It prints 16x2 text via HDMI']),
                        $this->mc('Generated Arduino C after a working block program helps you:', 'See the same logic in text', ['Skip common ground on Uno forever', 'Avoid all sensors', 'Replace mecanum with LEDs']),
                        $this->tf('A thinner line than the gap between probes is harder to follow.', true),
                    ],
                ],
            ],
            [
                'title' => 'Day 20: Multi-Direction Avoid + IR Override',
                'summary' => 'Combine ultrasonic avoid with IR/app override, matching ACEBOTT\'s advertised behaviours.',
                'objectives' => "Read kit ultrasonic distance in ACECode\nAvoid in a cluttered arena\nAdd IR or app override that wins for safety or for manual drive\nKeep a stop button / no-key halt\nWrite a two-sentence strategy",
                'guidance' => "Arena of bags. Remotes labelled per car (interference).\n0:00–0:20 Demo a working avoider.\n0:20–1:10 Autonomous avoid.\n1:10–1:40 Mix: if remote used, manual; else avoid — or ultrasonic blocks forward on app 'up'.\n1:40–2:00 Quiz. Week 4 recap.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>ACEBOTT marketing: <strong>avoid obstacles in multiple directions</strong> and <strong>IR &amp; web &amp; app control</strong>. You will implement a classroom version: ultrasonic (and servo pan if you added one) plus a human override.</p>

<h3>Autonomous core</h3>
<pre>forever
  if distance &lt; threshold then
    stop
    turn (choose left or right)
  else
    drive forward slowly
</pre>

<h3>Override</h3>
<p>If IR/app says forward <strong>and</strong> distance is small → ignore forward, flash LED (mBot Day 10 idea). If no key → stop.</p>

<h2>Arena success</h2>
<ul>
  <li>45 seconds without a hard hit, or a clear recovery.</li>
  <li>Teacher can take over with IR/app.</li>
  <li>Strategy written in two sentences.</li>
</ul>
HTML,
                'quiz' => [
                    'title' => 'Day 20 — ACEBOTT Avoid and Override Quiz',
                    'description' => 'Ultrasonic avoid, remotes, safety.',
                    'questions' => [
                        $this->mc('Obstacle avoid must keep running inside:', 'A forever loop (or equivalent)', ['setup() only', 'A single wait 60 s', 'The DHT library']),
                        $this->mc('A small threshold plus high speed often causes:', 'Collisions before the turn finishes', ['Better Wi-Fi', 'Higher humidity accuracy', 'Automatic soldering']),
                        $this->mc('Multiple remotes on one car cause:', 'Interference', ['Faster motors legally', 'Extra analog bits', 'Free lithium']),
                        $this->mc('Blocking app-forward when distance is low is:', 'Combining two inputs with a safety rule', ['Removing the ESP32', 'A color-sensor requirement', 'MQ preheat']),
                        $this->mc('No-key → stop is:', 'A dead-man default', ['Required to use I2C', 'A mecanum-only law', 'An LCD contrast trick']),
                        $this->mc('Multi-direction avoid can use:', 'More than one reading (pan, extra modules, or kit multi-sonar)', ['Only the water sensor', 'Only RFID', 'Only the glue gun']),
                        $this->mc('This matches factory mBot Mode B, except:', 'You can change the program and mix app control', ['Physics is different', 'Ultrasound is banned', 'Loops are banned']),
                        $this->mc('Web control on ACEBOTT uses the ESP32\'s:', 'Wi-Fi', ['16x2 LCD only', 'ULN2003 only', 'CH340 glue']),
                        $this->mc('A written strategy helps because:', 'The teacher can mark thinking, not only a lucky run', ['It uploads firmware', 'It sets COM ports', 'It cuts tape']),
                        $this->tf('Human remote and autonomous avoid can share one robot if you define which input wins.', true),
                    ],
                ],
            ],
        ];
    }

    private function week5Lessons(): array
    {
        return [
            [
                'title' => 'Day 21: Bluetooth Module and ACEBOTT App Custom Control',
                'summary' => 'Pair Bluetooth (HC-05/ESP32 BLE) or the official app and map buttons to motions and lights.',
                'objectives' => "Distinguish classic Bluetooth serial (HC-05) from ESP32 BLE/Wi-Fi app control\nPair a device and send a simple command character\nMap F/B/L/R/S to motor helpers\nKeep ultrasonic safety on forward\nLog the MAC/name of the classroom device",
                'guidance' => "ACEBOTT path: official app + matching firmware. Arduino path: HC-05 on hardware serial or SoftwareSerial — 5 V/3.3 V caution on RX.\n0:00–0:25 Two stacks on the board.\n0:25–1:20 Pairing clinic.\n1:20–1:45 Command protocol (one letter per line).\n1:45–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p><strong>Bluetooth modules</strong> (and the ACEBOTT app talking to ESP32) let the robot leave the USB cable. You will send tiny commands: <code>F</code> forward, <code>S</code> stop.</p>

<h3>Uno + HC-05 (classic)</h3>
<ul>
  <li>VCC 5 V, GND common, TX/RX crossed. Many HC-05 RX pins want 3.3 V — use a divider.</li>
  <li>Phone serial terminal or a simple camp APK if provided.</li>
</ul>

<h3>ACEBOTT / ESP32</h3>
<p>Prefer the official app after the correct firmware. Custom ACECode: read app widgets if the extension exposes them, or fall back to IR.</p>

<pre>char c = Serial.read();
if (c == 'F') forward(120);
if (c == 'S') halt();
</pre>

<p>Still block <code>F</code> when ultrasonic says close.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 21 — Bluetooth and App Quiz',
                    'description' => 'Pairing, command bytes, safety.',
                    'questions' => [
                        $this->mc('Bluetooth in this lesson is mainly for:', 'Wireless commands from a phone or app', ['Measuring water depth', 'Heating MQ', 'LCD contrast']),
                        $this->mc('TX on the module usually connects to:', 'RX on the microcontroller (crossed)', ['The glue-gun tip', 'L298N OUT1', 'LCD V0']),
                        $this->mc('A one-letter protocol is good in class because:', 'It is easy to debug and hard to mistype', ['It requires 4K video', 'It disables ultrasonic', 'It replaces GND']),
                        $this->mc('HC-05 RX often needs:', 'Level shifting toward 3.3 V', ['12 V from the wall', 'Direct 240 V', 'An RFID field']),
                        $this->mc('ESP32 is convenient because:', 'Wi-Fi and Bluetooth live on the same chip used by ACEBOTT', ['It cannot run ACECode', 'It has no USB', 'It is a servo']),
                        $this->mc('The official ACEBOTT app expects:', 'Firmware that matches the app features', ['A random Blink sketch forever', 'No power switch', 'A DHT on every wheel']),
                        $this->mc('Forward-over-Bluetooth should still:', 'Respect the ultrasonic safety rule', ['Ignore all sensors', 'Run at 255 only', 'Disable halt()']),
                        $this->mc('Writing down the device name/MAC helps:', 'The next pair reconnect without chaos', ['Increase PWM bits', 'Calibrate LDR to 15', 'Cut header pins']),
                        $this->mc('SoftwareSerial is sometimes used when:', 'Hardware Serial is busy with USB debug', ['You have no motors', 'You have no breadboard holes left on purpose', 'You are soldering the iron to 5 V']),
                        $this->tf('USB must stay plugged in for Bluetooth commands to work after you have uploaded.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 22: Wi-Fi Control, RFID Access, and a Simple Smart Gate',
                'summary' => 'Use ESP32/ACEBOTT web control and/or an RFID module to make an access behaviour.',
                'objectives' => "Explain Wi-Fi vs Bluetooth range and campus network rules\nTry ACEBOTT web/app drive on the classroom AP\nRead an RFID UID on RC522 (or kit equivalent)\nCompare UID to an allow-list\nTrigger LED/servo/relay 'gate' on a valid tag",
                'guidance' => "Do not put robots on the school admin SSID if IT forbids it — use a camp hotspot.\nRFID: 3.3 V modules. SPI pins on Uno.\n0:00–0:20 Network rules.\n0:20–1:00 Wi-Fi drive demo on ACEBOTT.\n1:00–1:40 RFID allow-list + servo latch or LED.\n1:40–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p><strong>Wi-Fi modules</strong> / ESP32 onboard Wi-Fi enable web dashboards (ACEBOTT web control). <strong>RFID</strong> (RC522-style) identifies a card — smart-home door, factory gate, attendance.</p>

<h3>Wi-Fi</h3>
<ul>
  <li>ESP32 can serve a page or connect to the ACEBOTT cloud/app depending on firmware.</li>
  <li>Uno + ESP-01 is possible but fiddly; prefer ACEBOTT for wireless web in this camp.</li>
  <li>Never hard-code staff passwords into sketches that get shared.</li>
</ul>

<h3>RFID allow-list</h3>
<pre>if (uid == "ALLOWED") {
  servo.write(90); // open
} else {
  servo.write(0);
  Serial.println("DENIED");
}
</pre>

<p>Combine with PIR: door opens only if card is valid <strong>and</strong> someone is present — nested logic from Day 10.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 22 — Wi-Fi and RFID Quiz',
                    'description' => 'Networks, tags, allow-lists.',
                    'questions' => [
                        $this->mc('ACEBOTT web control is possible because the car\'s ESP32 has:', 'Wi-Fi', ['Only a 16x2 display', 'Only a glue gun', 'Only ultrasonic']),
                        $this->mc('Bluetooth vs Wi-Fi in class:', 'Wi-Fi often reaches across the room via an AP; Bluetooth is usually shorter range', ['Bluetooth always goes around the Earth', 'Wi-Fi cannot move motors', 'They are the same radio with the same app always']),
                        $this->mc('RFID in this lesson identifies:', 'A tag/card UID', ['Exact air humidity to 0.001%', 'Stepper steps', 'COM port letters']),
                        $this->mc('An allow-list means:', 'Only listed UIDs open the gate', ['Every card in the world works', 'No Serial is allowed', 'Motors run at 255']),
                        $this->mc('Many RC522 modules are:', '3.3 V logic — do not feed them 5 V VCC blindly', ['240 V', 'Powered from ECHO', 'H-bridges']),
                        $this->mc('A servo or relay as the "gate" is an:', 'Actuator / output', ['LDR', 'CH340', 'Mecanum roller']),
                        $this->mc('Campus Wi-Fi passwords should:', 'Not be pasted into shared student sketches', ['Be written on the chassis in marker', 'Be the RFID UID', 'Be analogRead(A0)']),
                        $this->mc('Valid card AND PIR is an example of:', 'Nested / combined conditions', ['PWM on ENA only', 'Skipping GND', 'Factory mBot Mode C only']),
                        $this->mc('IT/camp hotspot rules exist to:', 'Keep the school network safe and the robots online on a known AP', ['Heat MQ sensors', 'Charge lithium via Wi-Fi', 'Replace USB drivers']),
                        $this->tf('Any RFID card should open every student gate without checking the UID.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 23: Superbot, CLB BOT, LEGO, and Craft Bodies',
                'summary' => 'Mount electronics on Superbot/CLB BOT/LEGO/cardboard/stick frames and keep wiring serviceable.',
                'objectives' => "Choose a body system: Superbot, CLB BOT, Make Brick LEGO, cardboard, sticks, bamboo\nMount Arduino or ACEBOTT without hiding USB and switches\nRoute wires with ties and tape, not glue on electronics\nAdd a bumper or phone tray\nKeep mass low and wheels free",
                'guidance' => "Superbot Master uses Makerzoid Lab + Bluetooth extension if that kit is in the cupboard — parallel station. CLB BOT: treat as the camp's branded chassis.\n0:00–0:20 Body menu.\n0:20–1:40 Build. Checkpoint: still programmable.\n1:40–2:00 Quiz.\nCodeless drones: optional 10-minute contrast station.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>Kits are not only PCBs. <strong>Superbot</strong>, <strong>CLB BOT</strong>, <strong>Make Brick LEGO</strong>, cardboard, ice-cream sticks, bamboo, and cable ties make a robot look like a product. The electronics rules do not change.</p>

<h3>Superbot (if available)</h3>
<p>Makerzoid Lab in Chrome, enable Bluetooth, add the Superbot extension, connect the control-unit MAC. Blocks still mean start, move, sensors — same habits as ACECode.</p>

<h3>Build rules</h3>
<ul>
  <li>No hot glue on USB, motors, or lithium cells.</li>
  <li>Glue gun only on cardboard tabs at the teacher station.</li>
  <li>LEGO bumpers must not jam wheels.</li>
  <li>Live wires insulated with electrical tape; no bare strands on the chassis metal.</li>
  <li>You must still upload tomorrow — do not bury the port.</li>
</ul>

<h2>Exit photo</h2>
<p>One photo of the body + a sentence: what mission this body is for (delivery tray, guard, animal, factory cart).</p>
HTML,
                'quiz' => [
                    'title' => 'Day 23 — Bodies and Kit Platforms Quiz',
                    'description' => 'Superbot/CLB/LEGO/craft, serviceable wiring.',
                    'questions' => [
                        $this->mc('A craft body is successful only if:', 'You can still power, program, and service the electronics', ['USB is glued shut', 'Wheels cannot turn', 'All sensors are buried in glue']),
                        $this->mc('Hot glue on a lithium pack is:', 'Not allowed', ['Required', 'A type of PWM', 'An I2C address']),
                        $this->mc('Superbot programming (Makerzoid Lab) still needs:', 'A connected control unit / extension like other block platforms', ['A 16x2 LCD as the only brain', 'No Bluetooth ever', 'Mains 240 V into a breadboard']),
                        $this->mc('CLB BOT in this camp is treated as:', 'A classroom robot chassis/kit to program and dress', ['A type of resistor', 'An MQ gas', 'A servo horn size']),
                        $this->mc('Electrical tape on stripped wire is for:', 'Insulation so the chassis cannot short', ['Increasing baud', 'Cooling L298N below physics', 'RFID encryption']),
                        $this->mc('Cable ties should:', 'Hold looms without crushing encoder/sensor cables', ['Replace the Uno', 'Be melted on the iron', 'Go through water-level traces as conductors']),
                        $this->mc('LEGO bumpers must not:', 'Jam the tyres', ['Be used as decoration ever', 'Sit on the top deck', 'Hold a paper flag']),
                        $this->mc('Codeless motor drones still lack:', 'Programmed decisions from sensors', ['Wheels', 'Batteries', 'Any motion']),
                        $this->mc('Bamboo and ice-cream sticks are:', 'Structure, not wire', ['5 V rails', 'ECHO signals', 'Wi-Fi antennas required by ACEBOTT']),
                        $this->tf('You may fully enclose the USB port in cardboard if it looks neat.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 24: Relays, Fans, Soldering Safety, and Invent a Gadget',
                'summary' => 'Switch a fan/lamp with a relay, learn soldering safety, and start the capstone gadget.',
                'objectives' => "Drive a relay module from a digital pin (optocoupler input)\nKeep mains/high voltage teacher-only if used at all — prefer 5 V fans\nList soldering PPE: gun rest, ventilation, no touching the tip, flux sparingly\nSolder one practice joint or header strip under supervision\nFreeze a capstone idea with a success test",
                'guidance' => "Default: 5 V DC fans + relay modules, NOT mains. If no soldering booth, demo only.\n0:00–0:20 Relay as electrically isolated switch.\n0:20–0:50 Fan on PIR or button.\n0:50–1:20 Soldering demo + one header strip per pair if policy allows.\n1:20–1:50 Capstone proposals.\n1:50–2:00 Quiz.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>A <strong>relay module</strong> lets a small GPIO switch a bigger load (DC fan). <strong>Soldering</strong> (gun, stand, paste/flux, braid/pump) joins headers to LCDs and sensors — optional and supervised.</p>

<h3>Relay</h3>
<pre>pinMode(7, OUTPUT);
digitalWrite(7, HIGH); // check if your module is active-LOW
</pre>
<p>Read the silkscreen (IN, VCC, GND, COM, NO, NC). Never wire wall-mains without a qualified adult and a closed box.</p>

<h3>Soldering rules</h3>
<ul>
  <li>Iron in the stand. Wet sponge or brass wool. Goggles.</li>
  <li>Ventilation. No eating at the station. Wash hands.</li>
  <li>Heat the pad and lead, then feed solder. Not a blob on the iron only.</li>
  <li>One practice joint. Then stop. This is not a jewellery class.</li>
</ul>

<h3>Capstone menu (pick one for Day 25)</h3>
<ol>
  <li>Arduino 4WD delivery / guard with at least two sensors.</li>
  <li>ACEBOTT mission: line + avoid + app override.</li>
  <li>Smart-home: RFID or PIR + LCD + relay fan.</li>
  <li>Superbot/CLB dressed body with a clear job.</li>
</ol>
HTML,
                'quiz' => [
                    'title' => 'Day 24 — Relays, Fans, Soldering Quiz',
                    'description' => 'Switching loads and iron safety.',
                    'questions' => [
                        $this->mc('A relay is best described as:', 'An electrically controlled switch for a separate load', ['A type of LDR', 'A mecanum wheel', 'A COM port']),
                        $this->mc('Classroom fans in this lesson should be:', 'Low-voltage DC unless a qualified adult runs a mains demo', ['Always 240 V on the breadboard', 'Powered from pin 13', 'Powered from ECHO']),
                        $this->mc('Some relay modules are active-LOW, which means:', 'You must read the docs/silkscreen before assuming HIGH = on', ['They only work on ESP32', 'They replace ultrasonic', 'They cannot switch a fan']),
                        $this->mc('The soldering iron belongs:', 'In the stand when not in your hand', ['In a student pocket', 'On the lithium pack', 'On the LCD glass']),
                        $this->mc('Flux/paste is used to:', 'Help solder wet the joint — sparingly', ['Charge batteries', 'Cool ESP32 Wi-Fi', 'Inflate tyres']),
                        $this->mc('A good solder joint looks:', 'Shiny and wetted on pad and lead, not a cold blob', ['Like a huge ball sitting on top', 'Painted cardboard', 'Hot glue']),
                        $this->mc('After soldering you should:', 'Wash hands and not eat at the iron', ['Touch the tip to check heat', 'Blow solder with your mouth close', 'Store the iron in water']),
                        $this->mc('Header pin strips are often soldered to:', 'LCDs and sensor modules so they fit breadboards', ['Mecanum rollers', 'Glue sticks', 'MQ legal stamps']),
                        $this->mc('A capstone success test must be:', 'Visible in about one minute', ['A secret that never runs', 'Deleting halt()', 'Hiding USB forever']),
                        $this->tf('Students may wire live wall sockets on a breadboard if the relay module is blue.', false),
                    ],
                ],
            ],
            [
                'title' => 'Day 25: Capstone Demo Day — Present, Reflect, Pack Kits',
                'summary' => 'Present the Robotics 2 mission, peer-review, restore kit firmware if needed, and recap Arduino plus ACEBOTT.',
                'objectives' => "Give a 60–90 second demo: problem, sensors, live success, next step\nPeer-review with a simple rubric\nStop safely on command\nRestore ACEBOTT factory firmware or archive sketches as directed\nInventory kits: boards, sensors, screws, packs",
                'guidance' => "Same demo-day discipline as mBot Day 15, bigger robots.\n0:00–0:10 Freeze features except teacher-approved bugfixes.\n0:10–1:15 Demos + rubric.\n1:15–1:35 Firmware/files/inventory.\n1:35–1:50 Map of 5 weeks on the board.\n1:50–2:00 Final quiz. Batteries out.",
                'content' => <<<'HTML'
<h2>Today's goal (2 hours)</h2>
<p>This is <strong>demo day</strong> for Robotics 2. No new features after the first 10 minutes except fixes the teacher allows.</p>

<h3>Peer rubric (1–3 each)</h3>
<ul>
  <li>Mission actually happens.</li>
  <li>At least two electronic ideas named correctly (e.g. L298N + ultrasonic, or ACECode upload + line probes).</li>
  <li>Robot can stop.</li>
  <li>Talk is clear.</li>
</ul>

<h2>Kit close-down</h2>
<ol>
  <li>Power off. Packs out of robots if camp policy says so.</li>
  <li>ACEBOTT: restore factory firmware if the next class needs the app out of the box.</li>
  <li>Arduino: save sketches to the class folder as <code>TeamName-R2</code>.</li>
  <li>Count: Unos/Nanos, L298N, sensors, LCDs, screws, remotes.</li>
</ol>

<h2>What Robotics 2 covered</h2>
<ul>
  <li><strong>Weeks 1–2:</strong> Arduino circuits, Serial, analog, DHT/LCD, PIR/MQ/water/color.</li>
  <li><strong>Week 3:</strong> motors, L298N, servo, stepper, homemade 4WD.</li>
  <li><strong>Week 4:</strong> ACEBOTT V2, ACECode online/upload, 4 motors, line, avoid, IR/app.</li>
  <li><strong>Week 5:</strong> Bluetooth, Wi-Fi, RFID, bodies, relays, soldering safety, capstone.</li>
</ul>

<p>You started Robotics 1 by pressing mBot's mode button. You finish Robotics 2 by <em>designing the circuit and the program</em>. That is the jump.</p>
HTML,
                'quiz' => [
                    'title' => 'Day 25 — Robotics 2 Final Quiz',
                    'description' => 'Full-course recap of Arduino, drivers, ACEBOTT, and safety.',
                    'questions' => [
                        $this->mc('Arduino Uno programs are structured around:', 'setup() once and loop() repeatedly', ['Only Scratch sprites with no pins', 'Excel cells', 'HDMI frames']),
                        $this->mc('Motors in this course are driven with a chip such as:', 'L298N (or the driver inside ACEBOTT)', ['A 16x2 LCD controller as the H-bridge', 'An LDR', 'A glue stick']),
                        $this->mc('analogRead on Uno is typically:', '0–1023 from a voltage divider', ['Only line values 0–3 from mBot', 'Wi-Fi channel numbers', 'RFID UIDs']),
                        $this->mc('ACEBOTT QD001 V2 factory firmware exists so that:', 'The car can be used after assembly before students write code', ['Motors cannot ever be programmed', 'USB is removed', 'Python is banned']),
                        $this->mc('ACECode Upload mode programs should:', 'Use the upload start hat and can run without the PC', ['Only work while the USB cable is held', 'Erase all four motors', 'Require a DHT on pin 13']),
                        $this->mc('Independent four-motor control on V2 is for:', 'Better turning, diagnosis, and path lessons', ['Removing ultrasonic', 'Skipping GND on Uno 4WD', 'Charging AA in the ESP32']),
                        $this->mc('RFID allow-lists protect:', 'A gate/mission so random cards do not succeed', ['The DHT accuracy', 'Mecanum rubber', 'COM3 forever']),
                        $this->mc('A demo-safe robot always:', 'Can stop', ['Runs until the pack dies', 'Hides the USB under glue', 'Uses mains on the breadboard']),
                        $this->mc('Lithium cells must stay:', 'In a proper holder under teacher rules', ['Loose in a pocket', 'In the water-level tank', 'On the soldering iron rest']),
                        $this->tf('Robotics 2 expects you to wire sensors and drivers yourself, then also use ACEBOTT/ACECode when the kit is the right tool.', true),
                    ],
                ],
            ],
        ];
    }
}
