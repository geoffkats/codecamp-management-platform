# CodeCamp Management Platform

![CodeCamp Logo](docs/media/logo-placeholder.png)

A full-featured curriculum and learning management platform for building, reviewing, and delivering courses at scale. It blends a flexible course builder with live content workflows, rich lesson editing, and role-based review tools so teams can produce structured learning content quickly and safely.

## Public Overview

CodeCamp is a modern learning platform that helps teams design, review, and publish courses faster. It combines a structured curriculum builder with collaboration-friendly workflows so content moves from draft to publish without losing quality or governance.

## Internal Notes

- Designed for team-based content creation with approval gates.
- Optimized for large catalogs with consistent structure and recoverable archives.
- Built for extensibility across new lesson types and review rules.

## Screenshots

![Curriculum Builder](docs/media/screenshot-curriculum-builder.png)
![Lesson Editor](docs/media/screenshot-lesson-editor.png)
![Manage Mode](docs/media/screenshot-manage-mode.png)

## Why It Is Advanced

- End-to-end workflow: build, review, approve, publish, and iterate on lessons in one system.
- Rich lesson authoring: modern editor with images, code blocks, and formatting.
- Structured curriculum: courses, modules, lessons, quizzes, and assessments are organized for scalable delivery.
- Role-aware governance: instructors, collaborators, and reviewers can work together without stepping on each other.
- Performance-minded: lazy-loaded assets, selective eager loading, and optimized UI interactions.

## Core Capabilities

### Curriculum Builder

- Create and manage courses, modules, lessons, and assessments.
- Manage active and archived content with a restore window.
- Rich lesson editing with autosave and recovery.
- Dedicated management mode for structure and content operations.

### Content Lifecycle and Review

- Support for content approvals with reviewer notifications.
- Approval status tracking for lessons.
- Audit-friendly workflow for team-based content creation.

### Learning Content Features

- Lesson types (text, video, interactive, quiz).
- Attachments and supporting documents.
- Structured objectives and summaries for improved learner outcomes.

### Operations and Admin

- Role and permission aware actions.
- Clear feedback for actions like archive, restore, and approvals.
- VPS deploy, update, DNS, and login-cookie commands: [docs/VPS_COMMANDS.md](docs/VPS_COMMANDS.md).

## Feature Matrix

| Area | Capability | Notes |
| --- | --- | --- |
| Curriculum | Courses, modules, lessons | Structured hierarchy for scalable content |
| Authoring | Rich editor | Images, code blocks, formatting, autosave |
| Review | Approval workflow | Submit, review, approve with notifications |
| Management | Archive and restore | Recoverable within restore window |
| Assessments | Quizzes and assignments | Per-lesson assessments and tracking |
| Governance | Roles and permissions | Instructor, collaborator, reviewer controls |
| Performance | Optimized loading | Lazy loading and selective eager loading |
| Deployment | CI-friendly build | Vite production builds and caching |

## Tech Stack

- Backend: Laravel (PHP)
- Frontend: Blade, Livewire, Alpine.js
- Build: Vite
- Styling: Tailwind CSS
- Editor: TipTap

## Project Structure

- app/: application logic, Livewire components, services, policies
- resources/: frontend assets and Blade views
- routes/: web and API routes
- database/: migrations, seeders, factories
- public/: built assets and entry points
- docs/: system notes and plans

## Getting Started

### Requirements

- PHP 8.1+ (Laravel compatible)
- Composer
- Node.js 18+ and npm
- MySQL or compatible database
- A local server stack (e.g., WAMP/Laragon/Docker)

### Setup

1) Install PHP dependencies

```
composer install
```

2) Install frontend dependencies

```
npm install
```

3) Configure environment

```
cp .env.example .env
php artisan key:generate
```

4) Set database connection in .env and run migrations

```
php artisan migrate
```

5) Build assets

```
npm run build
```

6) Run the app

```
php artisan serve
```

If you use a local domain (e.g., codecamp-system.test), configure your local host and web server accordingly.

## Development Workflow

- `npm run dev` for Vite dev server
- `npm run build` for production builds
- Use Livewire components for interactive UI changes

## Deployment Notes

- Run migrations on deploy.
- Build assets on the server or during CI.
- Configure queues and cache for production performance.
- Ensure storage is writable and linked if needed:

```
php artisan storage:link
```

## Security and Permissions

- Access is role and permission aware.
- Approval workflows protect content integrity.
- Archived items are read-only and recoverable within the restore window.

## Contributing

- Keep changes focused and test in the builder UI.
- Follow existing patterns in Livewire components and Blade views.
- Prefer small, reviewable PRs with clear intent.

## License

Proprietary. All rights reserved.
