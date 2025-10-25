# Responsive Utilities - Quick Reference

**Version:** Phase 4  
**Last Updated:** January 2025  
**Location:** `resources/css/app.css` (lines 147, 237, 290-385)

---

## Table of Contents

1. [Mobile Typography](#mobile-typography)
2. [Touch Targets](#touch-targets)
3. [Spacing](#spacing)
4. [Layout](#layout)
5. [Visibility](#visibility)
6. [Tables](#tables)
7. [Buttons](#buttons)
8. [Badges](#badges)
9. [Safe Areas](#safe-areas)

---

## Mobile Typography

Scale text appropriately across breakpoints:

```html
<!-- Small text: xs on mobile, sm on desktop -->
<p class="text-mobile-sm">Small text</p>

<!-- Base text: sm on mobile, base on desktop -->
<p class="text-mobile-base">Body text</p>

<!-- Large text: base on mobile, lg on desktop -->
<h3 class="text-mobile-lg">Heading</h3>
```

---

## Touch Targets

Ensure all interactive elements meet WCAG 2.1 Level AAA (44x44px minimum):

```html
<!-- Apply to buttons, links, inputs -->
<button class="touch-target btn-primary">Accessible Button</button>

<!-- Or use manual sizing -->
<a href="#" class="min-h-[44px] min-w-[44px] flex items-center justify-center">
    Icon Link
</a>
```

**When to use:**

-   All buttons
-   Icon-only links
-   Custom controls (checkboxes, radio buttons)
-   Mobile navigation items

---

## Spacing

Responsive padding that adapts to viewport:

```html
<!-- Horizontal padding: 4 mobile, 6 tablet, 8 desktop -->
<div class="mobile-px">Content</div>

<!-- Vertical padding: 3 mobile, 4 desktop -->
<div class="mobile-py">Content</div>

<!-- All-side padding: 4 mobile, 6 desktop -->
<div class="mobile-p">Content</div>

<!-- Responsive container with auto margins -->
<div class="container-responsive">Centered content with responsive padding</div>
```

**Common patterns:**

```html
<!-- Card with mobile-optimized padding -->
<div class="card mobile-p">
    <!-- Already handled by .card class -->
</div>

<!-- Section spacing -->
<section class="mobile-py">
    <!-- Content -->
</section>
```

---

## Layout

Responsive flexbox utilities:

```html
<!-- Stack vertically on mobile, horizontally on desktop -->
<div class="stack-mobile">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>
<!-- Result: 
     Mobile: Column (vertical)
     Desktop: Row (horizontal) with gap-3 -->

<!-- Manual responsive flex -->
<div class="flex flex-col sm:flex-row gap-4">
    <!-- Same effect as .stack-mobile but with gap-4 -->
</div>
```

**Common use cases:**

-   Form layouts (labels + inputs)
-   Button groups
-   Header actions
-   Filter controls

---

## Visibility

Show/hide content based on viewport:

```html
<!-- Hide on mobile, show on desktop -->
<div class="hide-mobile">
    Desktop-only content (e.g., detailed descriptions)
</div>

<!-- Show on mobile, hide on desktop -->
<div class="show-mobile">Mobile-only content (e.g., hamburger menu)</div>
```

**Examples:**

```html
<!-- Responsive table columns -->
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th class="hide-mobile">Email</th>
            <th class="hide-mobile">Phone</th>
            <th>Actions</th>
        </tr>
    </thead>
</table>

<!-- Mobile navigation -->
<nav class="show-mobile">
    <!-- Mobile menu -->
</nav>
<nav class="hide-mobile">
    <!-- Desktop menu -->
</nav>
```

---

## Tables

Prevent table layout breaks on mobile:

```html
<div class="table-responsive">
    <table class="min-w-full">
        <!-- Table content -->
    </table>
</div>
```

**What it does:**

-   Enables horizontal scroll on mobile (<640px)
-   Adds smooth touch scrolling on iOS
-   Negative margin (-mx-4) extends to card edges on mobile
-   Resets margin (sm:mx-0) on desktop

**Full pattern:**

```html
<div class="card">
    <div class="overflow-x-auto">
        @if($items->isEmpty())
        <div class="p-12 text-center">
            <p>No items found</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <!-- Headers -->
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Rows -->
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
```

**When to use:**

-   All data tables with 4+ columns
-   Tables with long content
-   Admin/staff index pages
-   Reports and listings

---

## Buttons

Responsive button widths:

```html
<!-- Full-width on mobile, auto on desktop -->
<button class="btn-primary btn-mobile">Save Changes</button>

<!-- Already full-width (common pattern) -->
<button class="btn-primary w-full sm:w-auto">Submit</button>

<!-- Always full-width -->
<button class="btn-primary w-full">Mobile Action</button>

<!-- Never full-width -->
<button class="btn-primary">Desktop Action</button>
```

**Button group pattern:**

```html
<!-- Stack buttons on mobile, row on desktop -->
<div class="stack-mobile">
    <button class="btn-primary btn-mobile">Primary</button>
    <button class="btn-secondary btn-mobile">Secondary</button>
</div>
```

**When to use `.btn-mobile`:**

-   Primary action buttons in headers
-   Form submit buttons
-   Modal action buttons
-   Create/Edit buttons

**When NOT to use:**

-   Buttons already in `.stack-mobile` container
-   Icon-only buttons
-   Inline text buttons
-   Navigation links

---

## Badges

Status indicators with responsive variants:

```html
<!-- Standard badges -->
<span class="badge-success">Approved</span>
<span class="badge-warning">Pending</span>
<span class="badge-danger">Rejected</span>
<span class="badge-info">In Review</span>

<!-- NEW: Secondary badge for neutral states -->
<span class="badge-secondary">Completed</span>
<span class="badge-secondary">Cancelled</span>
<span class="badge-secondary">{{ $status }}</span>
```

**Badge colors:**

-   `.badge-success` - Green (approved, confirmed, active)
-   `.badge-warning` - Yellow (pending, awaiting)
-   `.badge-danger` - Red (rejected, error, critical)
-   `.badge-info` - Blue (in progress, review, information)
-   `.badge-secondary` - Gray (completed, cancelled, neutral)

---

## Safe Areas

Support for devices with notches/rounded corners:

```html
<!-- Add top safe area padding -->
<header class="safe-top">Header content</header>

<!-- Add bottom safe area padding -->
<footer class="safe-bottom">Footer content</footer>
```

**What it does:**

-   Adds `padding-top: env(safe-area-inset-top)` for notched devices
-   Adds `padding-bottom: env(safe-area-inset-bottom)` for home indicator
-   No effect on non-notched devices

**When to use:**

-   Fixed headers that reach top edge
-   Fixed footers that reach bottom edge
-   Full-screen modals
-   Mobile navigation bars

---

## Breakpoint Reference

All utilities use these Tailwind breakpoints:

| Prefix | Min Width | Typical Device         |
| ------ | --------- | ---------------------- |
| (none) | 0px       | Mobile (default)       |
| `sm:`  | 640px     | Large phones, tablets  |
| `md:`  | 768px     | Tablets, small laptops |
| `lg:`  | 1024px    | Laptops, desktops      |
| `xl:`  | 1280px    | Large desktops         |
| `2xl:` | 1536px    | Extra large displays   |

**Design system focus:**

-   Mobile: <640px (default styles)
-   Tablet: 640px-1024px (sm: and md:)
-   Desktop: 1024px+ (lg:)

---

## Common Patterns

### Responsive Card Header

```html
<div class="card">
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
    >
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold">Title</h2>
            <p class="text-sm text-muted">Description</p>
        </div>
        <a href="#" class="btn-primary btn-mobile">Action</a>
    </div>
    <!-- Content -->
</div>
```

### Responsive Form Layout

```html
<form class="space-y-6">
    <div class="stack-mobile">
        <label class="w-full sm:w-1/3">Name</label>
        <input type="text" class="flex-1" />
    </div>

    <div class="stack-mobile">
        <label class="w-full sm:w-1/3">Email</label>
        <input type="email" class="flex-1" />
    </div>

    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
        <button type="button" class="btn-secondary btn-mobile">Cancel</button>
        <button type="submit" class="btn-primary btn-mobile">Save</button>
    </div>
</form>
```

### Responsive Grid

```html
<!-- 1 column mobile, 2 tablet, 3 desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="card">Card 1</div>
    <div class="card">Card 2</div>
    <div class="card">Card 3</div>
</div>
```

### Responsive Stats

```html
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="card text-center">
        <div class="text-3xl font-bold text-indigo-600">42</div>
        <div class="text-sm text-muted">Total</div>
    </div>
    <!-- More stats -->
</div>
```

---

## Testing Checklist

Before deploying responsive changes:

### Mobile (320px - 640px)

-   [ ] Tables scroll horizontally without breaking
-   [ ] Buttons are full-width and tappable (44x44px min)
-   [ ] Text is legible (not too small)
-   [ ] Forms are usable with proper spacing
-   [ ] Cards have appropriate padding (p-4)
-   [ ] No horizontal overflow

### Tablet (640px - 1024px)

-   [ ] Tables display normally
-   [ ] Buttons transition to auto-width
-   [ ] Layout uses available space efficiently
-   [ ] Touch targets remain accessible
-   [ ] Multi-column layouts appear correctly

### Desktop (1024px+)

-   [ ] All functionality works as before
-   [ ] No unexpected layout shifts
-   [ ] Optimal use of screen space
-   [ ] Mouse interactions work properly

---

## Browser DevTools Testing

### Chrome/Edge

1. Open DevTools (F12)
2. Click "Toggle device toolbar" (Ctrl+Shift+M)
3. Select device or enter custom dimensions
4. Test at: 320px, 375px, 768px, 1024px

### Firefox

1. Open DevTools (F12)
2. Click "Responsive Design Mode" (Ctrl+Shift+M)
3. Use preset devices or custom sizes

### Safari

1. Develop menu → Enter Responsive Design Mode
2. Choose iPhone/iPad presets
3. Or set custom viewport

---

## Performance Notes

**CSS Size Impact:**

-   Total utilities: ~50 lines of CSS
-   Gzipped size: +0.07 kB
-   Minimal performance impact
-   One-time load, cached by browser

**Runtime Performance:**

-   No JavaScript required
-   CSS-only transformations
-   GPU-accelerated where possible
-   Smooth 60fps animations

---

## Need Help?

**Common Issues:**

**Q: Table still overflowing on mobile?**

-   Ensure `.table-responsive` wrapper is present
-   Check for `min-w-full` on table
-   Verify no fixed widths on columns

**Q: Buttons not full-width on mobile?**

-   Add `.btn-mobile` class
-   Or use `w-full sm:w-auto`
-   Check parent container isn't constraining width

**Q: Text too small on mobile?**

-   Use `.text-mobile-base` or `.text-mobile-lg`
-   Ensure base font size is appropriate
-   Check for manual font size overrides

**Q: Touch targets too small?**

-   Add `.touch-target` class
-   Or manually: `min-h-[44px] min-w-[44px]`
-   Ensure padding isn't reducing effective size

---

**Last Updated:** Phase 4 Complete - January 2025  
**Next Update:** Phase 5 (Accessibility Audit)
