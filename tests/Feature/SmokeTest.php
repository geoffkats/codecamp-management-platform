<?php

use Illuminate\Support\Facades\Schema;

it('loads the full schema', function () {
    expect(Schema::hasTable('questions'))->toBeTrue()
        ->and(Schema::hasTable('assessment_attempts'))->toBeTrue();
});
