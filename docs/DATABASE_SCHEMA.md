# Database Schema Documentation

## Overview

This document provides comprehensive documentation of the e-learning platform's database schema. The system is built on MySQL/MariaDB and includes 40+ tables organized into logical modules.

---

# Table of Contents

1. [Core System Tables](#1-core-system-tables)
2. [User Management](#2-user-management)
3. [Course Management](#3-course-management)
4. [Lesson Management](#4-lesson-management)
5. [Assessment System](#5-assessment-system)
6. [Gamification System](#6-gamification-system)
7. [Progress Tracking](#7-progress-tracking)
8. [Communication](#8-communication)
9. [Content Approval](#9-content-approval)
10. [System Administration](#10-system-administration)

---

# 1. Core System Tables

## 1.1 Users Table

**Purpose:** Stores all user accounts (students, teachers, admins, supervisors)

```sql
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `id`: Primary key
- `email`: Unique identifier for authentication
- `is_active`: Account status flag
- `profile_image`: Avatar/profile picture path
- `bio`: User biography
- `last_login_at`: Last activity tracking

## 1.2 Roles Table

**Purpose:** Defines user roles and their permissions

```sql
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `name`: Role identifier (student, teacher, admin, supervisor)
- `display_name`: Human-readable role name
- `permissions`: JSON array of permission strings
- Default roles: student, teacher, admin

## 1.3 User Roles Table

**Purpose:** Pivot table linking users to their roles

```sql
CREATE TABLE `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Relationships:**
- Many-to-many: Users ↔ Roles

---

# 2. Course Management

## 2.1 Courses Table

**Purpose:** Stores course information and metadata

```sql
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `instructor_id` bigint unsigned NOT NULL,
  `featured_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty_level` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Beginner',
  `estimated_duration` int DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `requirements` json DEFAULT NULL,
  `what_you_learn` json DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','draft') DEFAULT 'draft',
  `submitted_for_approval_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_slug_unique` (`slug`),
  KEY `courses_instructor_id_foreign` (`instructor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `instructor_id`: Foreign key to users table (teacher)
- `slug`: URL-friendly identifier
- `difficulty_level`: Beginner, Intermediate, Advanced
- `approval_status`: Content approval workflow
- `tags`, `requirements`, `what_you_learn`: JSON arrays

## 2.2 Course Modules Table

**Purpose:** Organizes lessons into modules within courses

```sql
CREATE TABLE `course_modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `overview` text COLLATE utf8mb4_unicode_ci,
  `order_index` int NOT NULL DEFAULT '0',
  `estimated_duration_hours` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `approval_status` enum('pending','approved','rejected','draft') DEFAULT 'draft',
  `submitted_for_approval_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_modules_course_id_order_index_index` (`course_id`,`order_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Relationships:**
- Belongs to: Course
- Has many: Lessons

## 2.3 Course Enrollments Table

**Purpose:** Tracks student enrollments in courses

```sql
CREATE TABLE `course_enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `enrolled_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `lessons_completed` int NOT NULL DEFAULT '0',
  `quizzes_completed` int NOT NULL DEFAULT '0',
  `average_quiz_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_enrollments_user_id_course_id_unique` (`user_id`,`course_id`),
  KEY `course_enrollments_course_id_foreign` (`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `progress_percentage`: 0.00 to 100.00
- `completed_at`: NULL until course completion
- Unique constraint on user-course combination

---

# 3. Lesson Management

## 3.1 Lessons Table

**Purpose:** Stores individual lesson content and metadata

```sql
CREATE TABLE `lessons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `module_id` bigint unsigned DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `order` int NOT NULL DEFAULT '0',
  `order_index` int NOT NULL DEFAULT '0',
  `duration_minutes` int DEFAULT NULL,
  `video_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_duration` int DEFAULT NULL,
  `question_of_day` text COLLATE utf8mb4_unicode_ci,
  `objectives` text COLLATE utf8mb4_unicode_ci,
  `implementation_guidance` text COLLATE utf8mb4_unicode_ci,
  `lesson_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `has_levels` tinyint(1) NOT NULL DEFAULT '0',
  `total_levels` int NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_free_preview` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `attachments` json DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','draft') DEFAULT 'draft',
  `submitted_for_approval_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lessons_course_id_slug_unique` (`course_id`,`slug`),
  KEY `lessons_module_id_order_index_index` (`module_id`,`order_index`),
  KEY `lessons_lesson_type_difficulty_level_index` (`lesson_type`,`difficulty_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `lesson_type`: video, text, interactive, quiz, assignment
- `has_levels`: Indicates if lesson has multiple activity levels
- `content`: Long text field for rich content
- `attachments`: JSON array of attachment references

## 3.2 Lesson Resources Table

**Purpose:** Stores additional resources attached to lessons

```sql
CREATE TABLE `lesson_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `mime_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` enum('teacher','student','both') DEFAULT 'both',
  `resource_category` enum('lesson_plan','presentation','worksheet','video','audio','document','image','assessment','rubric','answer_key','implementation_guide','professional_development','other') DEFAULT 'document',
  `is_downloadable` tinyint(1) NOT NULL DEFAULT '1',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `order_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_resources_lesson_id_resource_type_index` (`lesson_id`,`resource_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `resource_type`: teacher-only, student-only, or both
- `resource_category`: Types of educational resources

## 3.3 Lesson Activities Table

**Purpose:** Defines activities within lessons

```sql
CREATE TABLE `lesson_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `instructions` longtext COLLATE utf8mb4_unicode_ci,
  `activity_type` enum('exploration','skill_building','practice','assessment','challenge','warm_up','wrap_up','group_work','individual_work','discussion','presentation','coding','debugging','research') DEFAULT 'practice',
  `level_type` enum('concept','activity','assessment','survey','video','text','map','unplugged','online','choice_level') DEFAULT 'activity',
  `level_status` enum('not_started','in_progress','keep_working','needs_review','completed') DEFAULT 'not_started',
  `expected_duration_minutes` int NOT NULL DEFAULT '30',
  `order_index` int NOT NULL DEFAULT '0',
  `level_details` json DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_activities_lesson_id_order_index_index` (`lesson_id`,`order_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 3.4 Lesson Attachments Table

**Purpose:** File attachments for lessons

```sql
CREATE TABLE `lesson_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_attachments_lesson_id_foreign` (`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 3.5 Lesson Practice Responses Table

**Purpose:** Student practice submissions for lessons

```sql
CREATE TABLE `lesson_practice_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `response` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_data` json DEFAULT NULL,
  `submitted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_practice_responses_user_id_lesson_id_unique` (`user_id`,`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 4. Assessment System

## 4.1 Assessments Table

**Purpose:** Stores comprehensive assessments with multiple types

```sql
CREATE TABLE `assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assessment_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_attempts` int unsigned NOT NULL DEFAULT '1',
  `time_limit_minutes` int unsigned DEFAULT NULL,
  `passing_score` int unsigned NOT NULL DEFAULT '70',
  `xp_reward` int unsigned NOT NULL DEFAULT '50',
  `questions` json DEFAULT NULL,
  `rubric_criteria` json DEFAULT NULL,
  `assignment_data` json DEFAULT NULL,
  `project_test_data` json DEFAULT NULL,
  `survey_data` json DEFAULT NULL,
  `peer_review_data` json DEFAULT NULL,
  `self_assessment_data` json DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `show_results_immediately` tinyint(1) NOT NULL DEFAULT '1',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `approval_status` enum('pending','approved','rejected','draft') DEFAULT 'draft',
  `submitted_for_approval_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessments_course_id_foreign` (`course_id`),
  KEY `assessments_lesson_id_foreign` (`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `assessment_type`: quiz, assignment, pre_project_test, post_project_test, unit_survey, rubric_assessment, peer_review, self_assessment
- JSON fields for type-specific data
- Approval workflow integrated

## 4.2 Assessment Attempts Table

**Purpose:** Tracks student attempts on assessments

```sql
CREATE TABLE `assessment_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `time_spent` int NOT NULL DEFAULT '0',
  `is_passed` tinyint(1) NOT NULL DEFAULT '0',
  `answers` json DEFAULT NULL,
  `status` enum('in_progress','completed','abandoned') DEFAULT 'in_progress',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assessment_attempts_assessment_id_user_id_index` (`assessment_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.3 Quizzes Table (Legacy)

**Purpose:** Older quiz structure (superseded by assessments)

```sql
CREATE TABLE `quizzes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `time_limit` int DEFAULT NULL,
  `max_attempts` int NOT NULL DEFAULT '3',
  `passing_score` decimal(5,2) NOT NULL DEFAULT '70.00',
  `is_randomized` tinyint(1) NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `show_correct_answers` tinyint(1) NOT NULL DEFAULT '1',
  `allow_review` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quizzes_lesson_id_foreign` (`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.4 Questions Table

**Purpose:** Stores quiz/assessment questions

```sql
CREATE TABLE `questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quiz_id` bigint unsigned NOT NULL,
  `assessment_id` bigint unsigned DEFAULT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '10',
  `order` int NOT NULL DEFAULT '0',
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `media_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_alt_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_position` enum('top','bottom','left','right') DEFAULT 'top',
  `media_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questions_quiz_id_foreign` (`quiz_id`),
  KEY `questions_assessment_id_index` (`assessment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.5 Question Options Table

**Purpose:** Answer options for questions

```sql
CREATE TABLE `question_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question_id` bigint unsigned NOT NULL,
  `option_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_alt_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_options_question_id_foreign` (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.6 Assignments Table

**Purpose:** Assignment-type assessments

```sql
CREATE TABLE `assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `due_date` timestamp NULL DEFAULT NULL,
  `max_points` int NOT NULL DEFAULT '100',
  `status` enum('draft','active','inactive','archived') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_course_id_status_index` (`course_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 4.7 Assignment Submissions Table

**Purpose:** Student submissions for assignments

```sql
CREATE TABLE `assignment_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assignment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `attachments` json DEFAULT NULL,
  `status` enum('draft','submitted','graded','returned') DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `points_earned` int DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_at` timestamp NULL DEFAULT NULL,
  `graded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_submissions_assignment_id_user_id_unique` (`assignment_id`,`user_id`),
  KEY `assignment_submissions_user_id_status_index` (`user_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 5. Gamification System

## 5.1 User Points Table

**Purpose:** Tracks student points, levels, and XP

```sql
CREATE TABLE `user_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `total_points` int NOT NULL DEFAULT '0',
  `level` int NOT NULL DEFAULT '1',
  `points_to_next_level` int NOT NULL DEFAULT '100',
  `xp_multiplier` decimal(3,2) DEFAULT NULL,
  `multiplier_expires_at` timestamp NULL DEFAULT NULL,
  `multiplier_reason` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_points_user_id_unique` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Fields:**
- `xp_multiplier`: Temporary point multipliers
- Level progression system

## 5.2 Badges Table

**Purpose:** Achievement badges available in the system

```sql
CREATE TABLE `badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `criteria` json NOT NULL,
  `points_reward` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_slug_unique` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.3 User Badges Table

**Purpose:** Pivot table tracking badges earned by users

```sql
CREATE TABLE `user_badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `badge_id` bigint unsigned NOT NULL,
  `earned_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_badges_user_id_badge_id_unique` (`user_id`,`badge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.4 Leaderboards Table

**Purpose:** Ranking data for competitions

```sql
CREATE TABLE `leaderboards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `points` int NOT NULL,
  `rank` int NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `last_updated` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leaderboards_type_course_id_rank_index` (`type`,`course_id`,`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.5 Daily Challenges Table

**Purpose:** Daily challenge definitions

```sql
CREATE TABLE `daily_challenges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('lesson_completion','quiz_score','study_time','course_progress','forum_participation','assignment_submission') NOT NULL,
  `requirements` json DEFAULT NULL,
  `reward_points` int NOT NULL DEFAULT '100',
  `date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `difficulty_level` enum('easy','medium','hard') DEFAULT 'medium',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_challenges_date_is_active_index` (`date`,`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.6 Daily Challenge Attempts Table

**Purpose:** Student participation in daily challenges

```sql
CREATE TABLE `daily_challenge_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `challenge_id` bigint unsigned NOT NULL,
  `attempted_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `points_earned` int NOT NULL DEFAULT '0',
  `details` json DEFAULT NULL,
  `progress_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_challenge_attempts_user_id_challenge_id_unique` (`user_id`,`challenge_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.7 Game Records Table

**Purpose:** Game-based learning records

```sql
CREATE TABLE `game_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `game_type` enum('math-quiz','word-puzzle','memory-game','typing-test') NOT NULL,
  `game_data` json NOT NULL,
  `user_answers` json NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `xp_earned` int NOT NULL DEFAULT '0',
  `play_time_seconds` int NOT NULL DEFAULT '0',
  `accuracy` decimal(5,2) NOT NULL DEFAULT '0.00',
  `wpm` int NOT NULL DEFAULT '0',
  `words_typed` int NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_records_user_id_game_type_index` (`user_id`,`game_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5.8 Gamification Notifications Table

**Purpose:** In-app notifications for gamification events

```sql
CREATE TABLE `gamification_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(191) NOT NULL,
  `title` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gamification_notifications_user_id_is_read_index` (`user_id`,`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 6. Progress Tracking

## 6.1 User Progress Table

**Purpose:** General user progress tracking

```sql
CREATE TABLE `user_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `type` enum('course_enrolled','lesson_started','lesson_completed','quiz_started','quiz_completed') NOT NULL,
  `metadata` json DEFAULT NULL,
  `points_earned` int NOT NULL DEFAULT '0',
  `score` decimal(5,2) DEFAULT NULL,
  `time_spent` int NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_progress_user_id_course_id_index` (`user_id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6.2 Lesson Progress Table

**Purpose:** Lesson-specific progress

```sql
CREATE TABLE `lesson_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `time_spent` int NOT NULL DEFAULT '0',
  `progress_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_progress_user_id_lesson_id_unique` (`user_id`,`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6.3 Student Lesson Progress Table

**Purpose:** Detailed lesson progress with status tracking

```sql
CREATE TABLE `student_lesson_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `activity_id` bigint unsigned DEFAULT NULL,
  `status` enum('not_started','in_progress','keep_working','needs_review','completed') DEFAULT 'not_started',
  `progress_percentage` int NOT NULL DEFAULT '0',
  `time_spent_minutes` int NOT NULL DEFAULT '0',
  `attempts` int NOT NULL DEFAULT '0',
  `completion_data` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_accessed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_lesson_progress_user_id_lesson_id_activity_id_unique` (`user_id`,`lesson_id`,`activity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6.4 Video Progress Table

**Purpose:** Video viewing progress

```sql
CREATE TABLE `video_progress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `video_url` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_seconds` int NOT NULL,
  `watched_seconds` int NOT NULL DEFAULT '0',
  `progress_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `last_position_seconds` int NOT NULL DEFAULT '0',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `last_watched_at` timestamp NULL DEFAULT NULL,
  `watch_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_progress_user_id_lesson_id_video_url_unique` (`user_id`,`lesson_id`,`video_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6.5 Quiz Attempts Table

**Purpose:** Quiz attempt records (legacy)

```sql
CREATE TABLE `quiz_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `quiz_id` bigint unsigned NOT NULL,
  `attempt_number` int NOT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_passed` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` timestamp NOT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quiz_attempts_user_id_quiz_id_attempt_number_unique` (`user_id`,`quiz_id`,`attempt_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 6.6 Grades Table

**Purpose:** Grade records

```sql
CREATE TABLE `grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `gradeable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gradeable_id` bigint unsigned NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `letter_grade` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` bigint unsigned DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grades_user_id_course_id_index` (`user_id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 7. Communication

## 7.1 Notifications Table

**Purpose:** General in-app notifications

```sql
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','success','warning','error','achievement','reminder','system') NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 7.2 Discussions Table

**Purpose:** Course/lesson discussion threads

```sql
CREATE TABLE `discussions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','closed','archived') DEFAULT 'active',
  `views_count` int NOT NULL DEFAULT '0',
  `replies_count` int NOT NULL DEFAULT '0',
  `last_reply_at` timestamp NULL DEFAULT NULL,
  `last_reply_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussions_course_id_status_index` (`course_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 7.3 Discussion Replies Table

**Purpose:** Reply messages in discussion threads

```sql
CREATE TABLE `discussion_replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discussion_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_solution` tinyint(1) NOT NULL DEFAULT '0',
  `likes_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussion_replies_discussion_id_created_at_index` (`discussion_id`,`created_at`),
  KEY `discussion_replies_parent_id_index` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 8. Content Approval

## 8.1 Content Approvals Table

**Purpose:** Approval workflow tracking

```sql
CREATE TABLE `content_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(191) NOT NULL,
  `approvable_id` bigint unsigned NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `submitted_by` bigint unsigned DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `submitted_at` timestamp DEFAULT NULL,
  `reviewed_at` timestamp DEFAULT NULL,
  `read_at` timestamp DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `category` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_approvals_status_submitted_at_index` (`status`,`submitted_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Features:**
- Polymorphic relationships (courses, modules, lessons, assessments)
- Priority levels
- Read tracking
- Category classification

---

# 9. System Administration

## 9.1 Certificates Table

**Purpose:** Generated certificates

```sql
CREATE TABLE `certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `certificate_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `issued_at` timestamp NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `completion_data` json NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificates_certificate_number_unique` (`certificate_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 9.2 Activity Log Table

**Purpose:** System activity logging

```sql
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(191) DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(191) DEFAULT NULL,
  `causer_type` varchar(191) DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 9.3 Laravel System Tables

### Sessions Table
```sql
CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Migrations Table
```sql
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Password Reset Tokens Table
```sql
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Jobs Table
```sql
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Failed Jobs Table
```sql
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

## 9.4 Telescope Tables (Debugging)

**Purpose:** Laravel Telescope debugging information

```sql
CREATE TABLE `telescope_entries` (
  `sequence` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `batch_id` char(36) NOT NULL,
  `family_hash` varchar(191) DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`),
  UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

```sql
CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) NOT NULL,
  `tag` varchar(191) NOT NULL,
  PRIMARY KEY (`entry_uuid`,`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

---

# Database Relationships Summary

## Key Relationships

### Users & Authentication
- **users** → **user_roles** → **roles** (Many-to-Many)

### Course Hierarchy
- **courses** → **course_modules** → **lessons**
- **courses** → **course_enrollments** ← **users**

### Content & Assessments
- **lessons** → **assessments**
- **lessons** → **quizzes**
- **lessons** → **lesson_resources**
- **lessons** → **lesson_activities**
- **assessments** → **assessment_attempts**
- **quizzes** → **questions** → **question_options**
- **quizzes** → **quiz_attempts**

### Progress Tracking
- **users** → **user_progress** → **courses**
- **users** → **lesson_progress** → **lessons**
- **users** → **student_lesson_progress** → **lessons**
- **users** → **video_progress** → **lessons**

### Gamification
- **users** → **user_points** (One-to-One)
- **users** → **user_badges** → **badges** (Many-to-Many)
- **users** → **daily_challenge_attempts** → **daily_challenges**
- **users** → **leaderboards**
- **users** → **game_records**

### Communication
- **users** → **notifications**
- **users** → **discussions** → **discussion_replies**
- **users** → **gamification_notifications**

### Administration
- **content_approvals** → (polymorphic) courses/lessons/modules/assessments
- **activity_log** → (polymorphic) all activity tracking
- **certificates** → **users** + **courses**

---

# Indexing Strategy

## Primary Indexes
- All tables have `id` as PRIMARY KEY
- Foreign key columns are indexed
- Unique constraints create unique indexes

## Performance Indexes
- **Composite Indexes:** Status + timestamp combinations
- **User-based Indexes:** User_id + type combinations
- **Date Indexes:** For time-based queries
- **Polymorphic Indexes:** Type + ID combinations

## Common Query Patterns
- User progress tracking: `user_id + course_id`
- Status filtering: `status + submitted_at`
- Time ranges: `created_at`, `completed_at`
- Search: `title`, `name`, `email`

---

# Data Types Summary

## Common Patterns
- **IDs:** `bigint unsigned NOT NULL AUTO_INCREMENT`
- **Timestamps:** `timestamp NULL DEFAULT NULL`
- **Booleans:** `tinyint(1) NOT NULL DEFAULT '0'`
- **JSON Data:** `json DEFAULT NULL`
- **Enum:** `enum('value1','value2','value3')`
- **Text:** `text`, `longtext`
- **Strings:** `varchar(191)` or `varchar(50)`
- **Decimals:** `decimal(5,2)` for scores, percentages
- **Integers:** `int` for counts, durations

---

# Notes

1. **Storage Engine:** MyISAM used throughout (consider InnoDB for production)
2. **Character Set:** utf8mb4_unicode_ci for full Unicode support
3. **Soft Deletes:** Only `courses` table uses `deleted_at`
4. **JSON Fields:** Extensively used for flexible data storage
5. **Polymorphic Relations:** Used in content_approvals and activity_log
6. **Migration History:** Tracked in `migrations` table
7. **Cascade Deletes:** Configured on critical foreign keys
8. **Unique Constraints:** Email, slugs, user-role combinations

---

# Schema Version

**Generated:** Database schema as of latest migrations  
**Total Tables:** 40+ tables  
**Last Updated:** Following complete system migration

