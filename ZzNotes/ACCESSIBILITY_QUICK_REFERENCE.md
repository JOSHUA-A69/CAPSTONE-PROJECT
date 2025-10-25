# Accessibility Quick Reference - Developer Guide

**Version:** Phase 5  
**Last Updated:** October 2025  
**WCAG Level:** AA Compliant

---

## Table of Contents

1. [Forms](#forms)
2. [Tables](#tables)
3. [Buttons & Links](#buttons--links)
4. [Screen Reader Utilities](#screen-reader-utilities)
5. [Focus Management](#focus-management)
6. [ARIA Patterns](#aria-patterns)
7. [Testing Checklist](#testing-checklist)

---

## Forms

### Basic Form Field Pattern

```blade
<label for="field_name">
    Field Label<span class="required-indicator" aria-label="required">*</span>
</label>
<input
    type="text"
    id="field_name"
    name="field_name"
    required
    aria-required="true"
    aria-describedby="field_name_help"
    @error('field_name')
        aria-invalid="true"
        aria-describedby="field_name_error"
    @enderror
>
<span id="field_name_help" class="sr-only">Help text for screen readers</span>
@error('field_name')
    <div id="field_name_error" role="alert" class="error-message">
        {{ $message }}
    </div>
@enderror
```

### Select Dropdown

```blade
<label for="status">Status Filter</label>
<select
    id="status"
    name="status"
    aria-describedby="status_help"
>
    <option value="">All Statuses</option>
    <option value="pending">Pending</option>
    <option value="approved">Approved</option>
</select>
<span id="status_help" class="sr-only">Filter results by status</span>
```

### Checkbox/Radio Groups

```blade
<fieldset>
    <legend>Choose Options</legend>
    <div>
        <input type="checkbox" id="option1" name="options[]" value="1">
        <label for="option1">Option 1</label>
    </div>
    <div>
        <input type="checkbox" id="option2" name="options[]" value="2">
        <label for="option2">Option 2</label>
    </div>
</fieldset>
```

### Dynamic Character Counter

```blade
<textarea
    id="description"
    name="description"
    maxlength="500"
    aria-describedby="description_counter"
></textarea>
<div id="description_counter" aria-live="polite">
    0 / 500 characters
</div>
```

---

## Tables

### Basic Accessible Table

```blade
<table role="table" aria-label="Descriptive table name">
    <caption class="sr-only">Detailed table description for screen readers</caption>
    <thead>
        <tr>
            <th scope="col">Column 1</th>
            <th scope="col">Column 2</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->value }}</td>
            <td>
                <a href="{{ route('item.show', $item) }}" aria-label="View details for {{ $item->name }}">
                    View
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### Responsive Table (with horizontal scroll)

```blade
<div class="table-responsive">
    <table role="table" aria-label="Reservations">
        <!-- Table content -->
    </table>
</div>
```

### Row Headers (for complex tables)

```blade
<tr>
    <th scope="row">Row Header</th>
    <td>Data 1</td>
    <td>Data 2</td>
</tr>
```

---

## Buttons & Links

### Primary Action Button

```blade
<button type="submit" class="btn-primary">
    <svg class="w-5 h-5 mr-2" aria-hidden="true">
        <!-- Icon -->
    </svg>
    Submit Form
</button>
```

### Icon-Only Button (requires aria-label)

```blade
<button type="button" class="btn-secondary" aria-label="Edit reservation">
    <svg class="w-5 h-5" aria-hidden="true">
        <path d="..."/>
    </svg>
</button>
```

### Link with Context

```blade
<!-- BAD: Link text is unclear -->
<a href="{{ route('reservation.show', $id) }}">View</a>

<!-- GOOD: Link text provides context -->
<a href="{{ route('reservation.show', $id) }}" aria-label="View details for {{ $reservation->activity_name }}">
    View Details →
</a>
```

### Disabled Button

```blade
<button type="submit" class="btn-primary" disabled aria-disabled="true">
    Cannot Submit
</button>
```

---

## Screen Reader Utilities

### Hide Content Visually (but keep for screen readers)

```blade
<span class="sr-only">Additional context for screen readers</span>
```

### Skip Navigation Link

```blade
<a href="#main-content" class="skip-link">
    Skip to main content
</a>

<!-- Later in the page -->
<main id="main-content" tabindex="-1">
    <!-- Content -->
</main>
```

### Announce Dynamic Content

```javascript
// Announce to screen reader (polite)
announceToScreenReader("Reservation saved successfully");

// Announce immediately (assertive)
announceToScreenReader("Error: Form submission failed", "assertive");
```

### ARIA Live Region (in HTML)

```blade
<div aria-live="polite" aria-atomic="true">
    <span id="status-message"></span>
</div>

<script>
document.getElementById('status-message').textContent = 'New message received';
</script>
```

---

## Focus Management

### Focus Indicators (built into buttons)

```blade
<!-- Already has focus:ring classes -->
<button class="btn-primary">Button</button>

<!-- Add focus ring to custom elements -->
<div class="custom-element focus-ring" tabindex="0">
    Focusable content
</div>
```

### Trap Focus in Modal

```javascript
// Automatically trapped for elements with role="dialog"
<div role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <h2 id="modal-title">Modal Title</h2>
    <!-- Modal content -->
</div>

// Or manually trap focus
const modal = document.getElementById('my-modal');
trapFocus(modal);
```

### Manage Focus on Route Change

```javascript
// Focus main content after navigation
document.getElementById("main-content").focus();
```

---

## ARIA Patterns

### Modal Dialog

```blade
<div
    role="dialog"
    aria-modal="true"
    aria-labelledby="dialog-title"
    aria-describedby="dialog-description"
>
    <h2 id="dialog-title">Confirm Action</h2>
    <p id="dialog-description">Are you sure you want to proceed?</p>

    <button type="button">Cancel</button>
    <button type="button">Confirm</button>
</div>
```

### Alert Dialog (for important messages)

```blade
<div role="alertdialog" aria-labelledby="alert-title" aria-describedby="alert-message">
    <h2 id="alert-title">Warning</h2>
    <p id="alert-message">This action cannot be undone.</p>
    <button type="button">Understood</button>
</div>
```

### Status Messages

```blade
<!-- Success message -->
<div role="status" class="badge-success">
    <svg aria-hidden="true"><!-- Icon --></svg>
    Reservation created successfully
</div>

<!-- Error message -->
<div role="alert" class="badge-danger">
    <svg aria-hidden="true"><!-- Icon --></svg>
    Error: Invalid date selected
</div>
```

### Tooltip

```blade
<button
    type="button"
    aria-describedby="tooltip-1"
    aria-label="Help information"
>
    ?
</button>
<span id="tooltip-1" role="tooltip" class="tooltiptext">
    This is helpful information
</span>
```

### Disclosure (Expandable Section)

```blade
<button
    type="button"
    aria-expanded="false"
    aria-controls="section-1"
    @click="expanded = !expanded"
>
    Show Details
</button>
<div id="section-1" x-show="expanded" x-cloak>
    <!-- Hidden content -->
</div>
```

### Tab Panel

```blade
<div role="tablist" aria-label="Reservation tabs">
    <button role="tab" aria-selected="true" aria-controls="panel-1">
        All
    </button>
    <button role="tab" aria-selected="false" aria-controls="panel-2">
        Pending
    </button>
</div>

<div id="panel-1" role="tabpanel" tabindex="0">
    <!-- Panel 1 content -->
</div>
<div id="panel-2" role="tabpanel" tabindex="0" hidden>
    <!-- Panel 2 content -->
</div>
```

---

## Testing Checklist

### Keyboard Navigation

```
□ Tab through entire page
□ Shift+Tab reverses order
□ Enter activates links/buttons
□ Space toggles checkboxes
□ Escape closes modals
□ No keyboard traps
□ Skip link works (Tab on page load)
□ Focus visible on all interactive elements
□ Focus order is logical
```

### Screen Reader Testing

```
□ Page title announced
□ Heading hierarchy correct (h1 → h2 → h3)
□ All images have alt text
□ Form labels announced
□ Required fields indicated
□ Error messages announced
□ Table headers announced
□ Link purpose clear from text
□ Button text descriptive
□ Status messages announced
```

### Color & Contrast

```
□ Text contrast ratio ≥ 4.5:1 (normal)
□ Large text contrast ≥ 3:1
□ UI components contrast ≥ 3:1
□ Not relying solely on color
□ Focus indicators visible
□ Error states not color-only
```

### Responsive & Mobile

```
□ Works at 320px width
□ Touch targets ≥ 44x44px
□ No horizontal scrolling (except tables)
□ Pinch-to-zoom enabled
□ Orientation changes supported
```

---

## Common Mistakes to Avoid

### ❌ Don't Do This

```blade
<!-- Missing label association -->
<label>Name</label>
<input type="text" name="name">

<!-- Generic link text -->
<a href="{{ route('show', $id) }}">Click here</a>

<!-- Icon without alternative -->
<button><i class="icon-delete"></i></button>

<!-- Missing required indicator -->
<input type="text" required>

<!-- Table without headers -->
<table>
    <tr><td>Name</td><td>Email</td></tr>
</table>

<!-- Div button without role -->
<div onclick="submit()">Submit</div>
```

### ✅ Do This Instead

```blade
<!-- Proper label association -->
<label for="name">Name</label>
<input type="text" id="name" name="name">

<!-- Descriptive link text -->
<a href="{{ route('show', $id) }}" aria-label="View details for {{ $item->name }}">
    View Details
</a>

<!-- Icon with label -->
<button aria-label="Delete reservation">
    <i class="icon-delete" aria-hidden="true"></i>
</button>

<!-- Clear required indicator -->
<label for="email">
    Email<span class="required-indicator" aria-label="required">*</span>
</label>
<input type="email" id="email" required aria-required="true">

<!-- Table with proper headers -->
<table>
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>John</td><td>john@example.com</td></tr>
    </tbody>
</table>

<!-- Use actual button -->
<button type="button" onclick="submit()">Submit</button>
```

---

## Quick Reference: ARIA Attributes

| Attribute          | Purpose                          | Example                                 |
| ------------------ | -------------------------------- | --------------------------------------- |
| `aria-label`       | Provides label when none visible | `<button aria-label="Close">×</button>` |
| `aria-labelledby`  | References visible label by ID   | `<div aria-labelledby="heading-1">`     |
| `aria-describedby` | References description by ID     | `<input aria-describedby="help-text">`  |
| `aria-required`    | Indicates required field         | `<input aria-required="true">`          |
| `aria-invalid`     | Indicates validation error       | `<input aria-invalid="true">`           |
| `aria-live`        | Announces dynamic changes        | `<div aria-live="polite">`              |
| `aria-expanded`    | Indicates expanded state         | `<button aria-expanded="false">`        |
| `aria-controls`    | References controlled element    | `<button aria-controls="panel-1">`      |
| `aria-current`     | Indicates current item           | `<a aria-current="page">Home</a>`       |
| `aria-hidden`      | Hides from screen readers        | `<svg aria-hidden="true">`              |
| `aria-modal`       | Indicates modal dialog           | `<div role="dialog" aria-modal="true">` |
| `role`             | Defines element's role           | `<div role="alert">`                    |

---

## Resources

### Testing Tools

-   **axe DevTools** - Browser extension for automated testing
-   **WAVE** - Web accessibility evaluation tool
-   **Lighthouse** - Chrome DevTools accessibility audit
-   **Pa11y** - Command-line accessibility testing

### Screen Readers

-   **NVDA** (Windows) - Free, https://www.nvaccess.org/
-   **JAWS** (Windows) - Industry standard
-   **VoiceOver** (Mac/iOS) - Built-in (Cmd + F5)
-   **TalkBack** (Android) - Built-in

### Documentation

-   **WCAG 2.1** - https://www.w3.org/WAI/WCAG21/quickref/
-   **ARIA Authoring Practices** - https://www.w3.org/WAI/ARIA/apg/
-   **WebAIM** - https://webaim.org/
-   **MDN Accessibility** - https://developer.mozilla.org/en-US/docs/Web/Accessibility

---

## Need Help?

**Common Questions:**

**Q: When should I use `aria-label` vs `aria-labelledby`?**

-   Use `aria-label` when there's no visible label
-   Use `aria-labelledby` to reference an existing visible element

**Q: Do I need both `required` and `aria-required="true"`?**

-   Yes! `required` provides browser validation
-   `aria-required="true"` announces to screen readers

**Q: When should I use `role="alert"` vs `role="status"`?**

-   `role="alert"` for important/urgent messages (interrupts)
-   `role="status"` for informational updates (polite)

**Q: Should all SVG icons have `aria-hidden="true"`?**

-   Yes, if the icon is decorative or has adjacent text
-   No, if the icon is the only content (use `aria-label` instead)

**Q: How do I test if my site is accessible?**

1. Use keyboard only (unplug mouse)
2. Run axe DevTools scan
3. Test with screen reader
4. Check color contrast
5. Test at 320px width

---

**Last Updated:** Phase 5 Complete - October 2025  
**Next Update:** Phase 6 (Performance Optimization)
