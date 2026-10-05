<?php

namespace App\Services\Assessments\QuestionTypes;

use App\Services\Assessments\QuestionGrade;

class FileUploadType extends QuestionType
{
    public function key(): string
    {
        return 'file_upload';
    }

    public function label(): string
    {
        return 'File Upload';
    }

    public function aliases(): array
    {
        return ['upload', 'file'];
    }

    public function isAutoGradable(array $question): bool
    {
        return false;
    }

    public function grade(array $question, mixed $answer): QuestionGrade
    {
        $files = [];
        if (is_array($answer)) {
            $files = $answer['files'] ?? (isset($answer['path']) ? [$answer] : []);
        }

        return new QuestionGrade(null, $this->points($question), null, true, '', null, [], array_values(array_filter($files)));
    }
}
