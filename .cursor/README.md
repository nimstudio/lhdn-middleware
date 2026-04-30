# LHDN Invoice Submission Middleware - Cursor Project Guide

## Project Overview
Laravel 11 SaaS application for Malaysian companies to submit invoices to LHDN's MyInvois system.

**Tech Stack:**
- Laravel 11.x
- PHP 8.2+
- Filament 3.x
- MySQL 8.0+
- Redis
- Livewire 3.x

## Quick Reference

### Important Files
- **PRD:** `prd.md` - Full product requirements document
- **Progress:** `.cursor/progress.md` - Current implementation status
- **Tasks:** `.cursor/tasks.md` - Pending tasks and checklist
- **Conventions:** `.cursor/conventions.md` - Code standards and patterns
- **Laravel Best Practices:** `.cursor/laravel-best-practices.md` - **MUST READ & FOLLOW**
- **Architecture:** `.cursor/architecture.md` - System design decisions

### Key Commands
```bash
# Development
php artisan serve
npm run dev

# Queue worker
php artisan queue:work

# Testing
php artisan test
```

## Current Phase
Check `.cursor/progress.md` for the latest status.

## Getting Started
1. Review `prd.md` for full requirements
2. Check `.cursor/progress.md` for current status
3. Review `.cursor/tasks.md` for next steps
4. Follow `.cursor/conventions.md` for code standards
