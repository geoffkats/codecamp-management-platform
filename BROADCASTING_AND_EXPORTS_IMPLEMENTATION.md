# Broadcasting, Notifications, Exports & Search Implementation Guide

## ✅ What's Been Implemented

### 1. **Notification Service** (`app/Services/NotificationService.php`)
- Real-time notification system
- Badge earned notifications
- Course completion notifications  
- Achievement notifications
- Assessment result notifications
- Email notification support

### 2. **Broadcasting Events**
- `NotificationCreated` - Broadcasts real-time notifications
- `BadgeEarned` - Already exists, enhanced with notifications

### 3. **Email Templates**
- `NotificationMail` - General notification emails
- `CourseCompletedMail` - Course completion emails

### 4. **Export Service** (`app/Services/ExportService.php`)
- Export assessment results (PDF/CSV)
- Export progress reports (PDF/CSV)
- Export certificates (PDF)

### 5. **Search Service** (`app/Services/SearchService.php`)
- Global search across courses, lessons, assessments
- Advanced filters for assessments
- Badge search by criteria

### 6. **Integration**
- BadgeAwardingService now uses NotificationService
- Course completion triggers notifications
- Badge awards trigger real-time notifications

## 🚀 Setup Instructions

### Step 1: Install Required Packages

```bash
# For PDF exports
composer require barryvdh/laravel-dompdf

# For broadcasting (choose one):
# Option A: Pusher (recommended for production)
composer require pusher/pusher-php-server

# Option B: Laravel Reverb (native Laravel solution)
composer require laravel/reverb --dev
php artisan reverb:install
```

### Step 2: Configure Broadcasting

#### For Pusher:
1. Get credentials from [pusher.com](https://pusher.com)
2. Add to `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster
```

#### For Laravel Reverb:
```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
```

### Step 3: Configure Broadcasting Routes

Add to `routes/channels.php`:
```php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### Step 4: Frontend Setup (Laravel Echo + Pusher)

Install via npm:
```bash
npm install --save-dev laravel-echo pusher-js
```

Add to `resources/js/app.js`:
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    forceTLS: true
});

// Listen for notifications
window.Echo.private(`user.${window.userId}`)
    .notification((notification) => {
        // Show toast notification
        console.log('New notification:', notification);
        // You can integrate with your toast library here
    });
```

### Step 5: Create Email Templates

Create `resources/views/emails/notification.blade.php`:
```blade
<x-mail::message>
# {{ $title }}

{{ $message }}

@if(isset($data['action_url']))
<x-mail::button :url="$data['action_url']">
View Details
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```

Create `resources/views/emails/course-completed.blade.php`:
```blade
<x-mail::message>
# 🎓 Course Completed!

Congratulations {{ $user->name }}!

You've successfully completed the course **{{ $course->title }}**.

<x-mail::button :url="route('courses.show', $course)">
View Course
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```

### Step 6: Create Export Views

Create `resources/views/exports/assessment-results.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <title>Assessment Results - {{ $assessment->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>{{ $assessment->title }}</h1>
    <h2>Results Summary</h2>
    <p>Total Attempts: {{ $statistics['total_attempts'] }}</p>
    <p>Passed: {{ $statistics['passed'] }}</p>
    <p>Failed: {{ $statistics['failed'] }}</p>
    <p>Average Score: {{ number_format($statistics['average_score'], 2) }}%</p>
    
    <h2>Detailed Results</h2>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Score</th>
                <th>Passed</th>
                <th>Completed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attempts as $attempt)
            <tr>
                <td>{{ $attempt->user->name }}</td>
                <td>{{ number_format($attempt->score, 2) }}%</td>
                <td>{{ $attempt->is_passed ? 'Yes' : 'No' }}</td>
                <td>{{ $attempt->completed_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

Create `resources/views/exports/progress-report.blade.php` and `resources/views/exports/certificate.blade.php` similarly.

### Step 7: Add Export Routes

Add to `routes/web.php`:
```php
Route::middleware(['auth'])->group(function () {
    // Export routes
    Route::get('/assessments/{assessment}/export', function (Assessment $assessment) {
        return app(\App\Services\ExportService::class)
            ->exportAssessmentResults($assessment, request('format', 'pdf'));
    })->name('assessments.export');
    
    Route::get('/progress/export', function () {
        return app(\App\Services\ExportService::class)
            ->exportProgressReport(auth()->user(), request('format', 'pdf'));
    })->name('progress.export');
    
    Route::get('/certificates/{certificate}/export', function (Certificate $certificate) {
        return app(\App\Services\ExportService::class)
            ->exportCertificate($certificate);
    })->name('certificates.export');
});
```

### Step 8: Add Search Components

Create `app/Livewire/Search/GlobalSearch.php`:
```php
<?php

namespace App\Livewire\Search;

use App\Services\SearchService;
use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';
    public $results = [];

    public function search()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $searchService = app(SearchService::class);
        $this->results = $searchService->globalSearch($this->query);
    }

    public function render()
    {
        return view('livewire.search.global-search');
    }
}
```

### Step 9: Add Notification Toast Component

Create a Livewire component to show notification toasts when badges are earned:
```php
// app/Livewire/Components/NotificationToast.php
// Listen for badge earned events and show toast
```

## 📝 Usage Examples

### Using NotificationService:
```php
$notificationService = app(\App\Services\NotificationService::class);

// Notify badge earned (automatically done by BadgeAwardingService)
$notificationService->notifyBadgeEarned($user, $badge);

// Notify course completion (automatically done in Lessons/View.php)
$notificationService->notifyCourseCompleted($user, $course);

// Custom notification
$notificationService->notify(
    $user,
    'Custom Title',
    'Custom message',
    'info',
    ['action_url' => route('some.route')]
);
```

### Using ExportService:
```php
$exportService = app(\App\Services\ExportService::class);

// Export assessment results
return $exportService->exportAssessmentResults($assessment, 'pdf');

// Export progress report
return $exportService->exportProgressReport($user, 'csv');

// Export certificate
return $exportService->exportCertificate($certificate);
```

### Using SearchService:
```php
$searchService = app(\App\Services\SearchService::class);

// Global search
$results = $searchService->globalSearch('Laravel');

// Search courses
$courses = $searchService->searchCourses('PHP', ['difficulty' => 'intermediate']);

// Search badges
$badges = $searchService->searchBadges('course', ['type' => 'course_completion']);
```

## 🔔 Real-time Notifications Setup

The notification system is configured to broadcast to private channels. Users will receive notifications in real-time when:
- Badges are earned
- Courses are completed
- Assessments are graded
- Achievements are unlocked

## 📊 Export Features

All exports support:
- PDF format (using DomPDF)
- CSV format (for data analysis)
- Customizable templates
- Statistics and summaries

## 🔍 Search Features

The search system provides:
- Full-text search across courses, lessons, assessments
- Filtering by type, category, difficulty
- Badge search by criteria
- Advanced filtering options

## 🎯 Next Steps

1. **Install packages**: Run composer and npm install commands
2. **Configure broadcasting**: Set up Pusher or Reverb
3. **Create views**: Add email and export templates
4. **Add UI components**: Create search bar and notification toasts
5. **Test**: Test notifications, exports, and search functionality

## 📦 Required Packages Summary

```json
{
    "require": {
        "barryvdh/laravel-dompdf": "^3.0",
        "pusher/pusher-php-server": "^7.0"
    },
    "devDependencies": {
        "laravel-echo": "^1.16.0",
        "pusher-js": "^8.0.0"
    }
}
```

## ⚠️ Important Notes

- Broadcasting requires queue workers: `php artisan queue:work`
- Email requires SMTP configuration in `.env`
- PDF exports require DomPDF package
- Frontend Echo setup required for real-time UI updates

