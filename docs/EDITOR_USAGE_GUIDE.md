# Summernote Editor Usage Guide

## Performance Optimization

Summernote and jQuery are now **conditionally loaded** only on pages that need rich text editing. This reduces initial page load by ~150KB on pages that don't need the editor.

## How to Use

### On pages that need the Summernote editor:

Add this line at the top of your Blade template:

```blade
@include('partials.editor-scripts')
```

### Pages that currently need the editor:

1. **Course Management**
   - `resources/views/courses/create.blade.php`
   - `resources/views/courses/edit.blade.php`

2. **Lesson Management**
   - `resources/views/lessons/create.blade.php`
   - `resources/views/lessons/edit.blade.php`
   - `resources/views/livewire/curriculum/builder.blade.php` (modal forms)

3. **Module Management**
   - Any page with rich text content editing

4. **Assessment/Quiz Creation**
   - Pages with question/description editors

### Example Implementation:

```blade
{{-- At the top of your blade file --}}
@include('partials.editor-scripts')

{{-- Your page content --}}
<div>
    <textarea id="summernote"></textarea>
</div>

@push('scripts')
<script>
    // Wait for Summernote to load
    document.addEventListener('DOMContentLoaded', function() {
        $('#summernote').summernote({
            height: 300,
            // your config
        });
    });
</script>
@endpush
```

## Benefits

- **Faster page loads**: Pages without editors load 150KB+ less JavaScript
- **Better FCP/LCP**: Reduced blocking time for initial render
- **Improved TBT**: Less JavaScript to parse and execute
- **Better UX**: Faster perceived performance

## Migration Checklist

- [ ] Identify all pages using Summernote
- [ ] Add `@include('partials.editor-scripts')` to those pages
- [ ] Test editor functionality
- [ ] Remove from pages that don't need it
- [ ] Rebuild assets with `npm run build`
