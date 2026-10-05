<?php

namespace App\Services\Assessments;

use Illuminate\Support\Facades\DB;

/**
 * Links every existing question to the assessment it was written for and places it in that
 * assessment's course (and module, when the assessment sits on a lesson). Idempotent.
 */
class QuestionBankBackfill
{
    public static function run(): void
    {
        // Positions copy the legacy `order` so attempts see exactly the order they always did.
        DB::statement('
            INSERT IGNORE INTO assessment_question (assessment_id, question_id, position, created_at, updated_at)
            SELECT q.assessment_id, q.id, q.`order`, NOW(), NOW()
            FROM questions q
            INNER JOIN assessments a ON a.id = q.assessment_id
        ');

        // NULL module ids don't collide in a unique index, so dedupe explicitly with <=>.
        DB::statement('
            INSERT INTO question_placements (question_id, course_id, course_module_id, created_at, updated_at)
            SELECT q.id, a.course_id, m.id, NOW(), NOW()
            FROM questions q
            INNER JOIN assessments a ON a.id = q.assessment_id
            INNER JOIN courses c ON c.id = a.course_id
            LEFT JOIN lessons l ON l.id = a.lesson_id
            LEFT JOIN course_modules m ON m.id = l.module_id AND m.course_id = a.course_id
            WHERE NOT EXISTS (
                SELECT 1 FROM question_placements p
                WHERE p.question_id = q.id AND p.course_id = a.course_id AND p.course_module_id <=> m.id
            )
        ');
    }
}
