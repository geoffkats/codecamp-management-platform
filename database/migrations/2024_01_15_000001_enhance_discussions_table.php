<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('discussions', 'lesson_id')) {
                $table->foreignId('lesson_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('discussions', 'subject_tag')) {
                $table->string('subject_tag')->nullable()->after('lesson_id');
            }
            if (!Schema::hasColumn('discussions', 'upvotes')) {
                $table->integer('upvotes')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('discussions', 'helpful_count')) {
                $table->integer('helpful_count')->default(0)->after('upvotes');
            }
            if (!Schema::hasColumn('discussions', 'has_best_answer')) {
                $table->boolean('has_best_answer')->default(false)->after('helpful_count');
            }
            if (!Schema::hasColumn('discussions', 'scratch_project_id')) {
                $table->string('scratch_project_id')->nullable()->after('content');
            }
            if (!Schema::hasColumn('discussions', 'code_snippets')) {
                $table->json('code_snippets')->nullable()->after('scratch_project_id');
            }
            if (!Schema::hasColumn('discussions', 'attachments')) {
                $table->json('attachments')->nullable()->after('code_snippets');
            }
        });

        // Create reactions table only if it doesn't exist
        if (!Schema::hasTable('discussion_reactions')) {
            Schema::create('discussion_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discussion_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('reaction_type'); // upvote, helpful, love, celebrate
                $table->timestamps();
                
                $table->unique(['discussion_id', 'user_id', 'reaction_type']);
                $table->index(['discussion_id', 'reaction_type']);
            });
        }

        // Create reply reactions table only if it doesn't exist
        if (!Schema::hasTable('discussion_reply_reactions')) {
            Schema::create('discussion_reply_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('discussion_reply_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('reaction_type');
                $table->timestamps();
                
                // Shorter unique constraint name
                $table->unique(['discussion_reply_id', 'user_id', 'reaction_type'], 'reply_reaction_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_reply_reactions');
        Schema::dropIfExists('discussion_reactions');
        
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn([
                'lesson_id',
                'subject_tag',
                'upvotes',
                'helpful_count',
                'has_best_answer',
                'scratch_project_id',
                'code_snippets',
                'attachments',
            ]);
        });
    }
};
