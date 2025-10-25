# 🎨 DESIGN SYSTEM QUICK REFERENCE

## 🎯 BUTTONS

```blade
<!-- Primary Action -->
<button class="btn-primary">Save Changes</button>
<button class="btn-primary btn-sm">Small Button</button>
<button class="btn-primary btn-lg">Large Button</button>

<!-- Secondary Action -->
<button class="btn-secondary">Cancel</button>

<!-- Danger/Delete -->
<button class="btn-danger">Delete</button>

<!-- Success/Approve -->
<button class="btn-success">Approve</button>

<!-- Minimal -->
<button class="btn-ghost">View Details</button>

<!-- Disabled -->
<button class="btn-primary" disabled>Processing...</button>
```

---

## 📦 CARDS

```blade
<!-- Basic Card -->
<div class="card">
    <div class="card-body">
        Content here
    </div>
</div>

<!-- Card with Hover Effect -->
<div class="card-hover">
    <div class="card-body">
        Hoverable card
    </div>
</div>

<!-- Complete Card -->
<div class="card-hover">
    <div class="card-header">
        <h3 class="text-heading">Title</h3>
    </div>
    <div class="card-body">
        <p class="text-body">Content</p>
    </div>
    <div class="card-footer">
        <button class="btn-primary">Action</button>
    </div>
</div>

<!-- Stat Card with Border -->
<div class="card-hover border-l-4 border-indigo-500">
    <div class="card-body">
        <p class="text-muted text-sm mb-2">Label</p>
        <p class="text-heading text-3xl font-bold">42</p>
    </div>
</div>
```

---

## 📝 FORMS

```blade
<!-- Text Input -->
<div>
    <label class="form-label">Email Address</label>
    <input type="email" class="form-input" placeholder="you@example.com" />
    <p class="form-helper">We'll never share your email</p>
</div>

<!-- Input with Error -->
<div>
    <label class="form-label">Password</label>
    <input type="password" class="form-input" aria-describedby="password-error" />
    <div class="form-error" id="password-error">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
        </svg>
        <span>Password is required</span>
    </div>
</div>

<!-- Input with Success -->
<div>
    <label class="form-label">Username</label>
    <input type="text" class="form-input" value="johndoe" />
    <div class="form-success">
        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
        </svg>
        <span>Username is available</span>
    </div>
</div>

<!-- Using Blade Components -->
<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" type="email" name="email" :value="old('email')" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>
```

---

## 🏷️ BADGES

```blade
<!-- Status Badges -->
<span class="badge-primary">In Progress</span>
<span class="badge-success">Approved</span>
<span class="badge-warning">Pending</span>
<span class="badge-danger">Rejected</span>
<span class="badge-info">New</span>

<!-- With Icon -->
<span class="badge-success">
    <svg class="w-3 h-3 mr-1 inline-block" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
    </svg>
    Verified
</span>
```

---

## ✏️ TYPOGRAPHY

```blade
<!-- Headings -->
<h1 class="text-heading text-4xl">Main Heading</h1>
<h2 class="text-heading text-3xl">Section Heading</h2>
<h3 class="text-heading text-2xl">Subsection</h3>
<h4 class="text-heading text-xl">Card Title</h4>

<!-- Body Text -->
<p class="text-body">Regular paragraph text that's easy to read</p>
<p class="text-body text-lg">Larger paragraph text</p>
<p class="text-body text-sm">Smaller paragraph text</p>

<!-- Secondary Text -->
<p class="text-muted">Supporting information or metadata</p>
<p class="text-muted text-xs">Tiny details</p>

<!-- Disabled Text -->
<p class="text-disabled">Disabled or unavailable option</p>
```

---

## ⏳ LOADING STATES

```blade
<!-- Spinner -->
<div class="flex items-center gap-2">
    <div class="spinner"></div>
    <span>Loading...</span>
</div>

<!-- Large Spinner -->
<div class="flex justify-center py-12">
    <div class="spinner-lg text-indigo-600"></div>
</div>

<!-- Skeleton Loader -->
<div class="space-y-4">
    <div class="skeleton h-4 w-3/4"></div>
    <div class="skeleton h-4 w-1/2"></div>
    <div class="skeleton h-32 w-full"></div>
</div>

<!-- Button with Spinner -->
<button class="btn-primary" disabled>
    <div class="spinner mr-2"></div>
    Processing...
</button>
```

---

## 🎨 COLORS

### **Primary Colors**

-   `text-indigo-600` / `bg-indigo-600` - Primary actions
-   `text-green-600` / `bg-green-600` - Success states
-   `text-yellow-600` / `bg-yellow-600` - Warning states
-   `text-red-600` / `bg-red-600` - Danger states
-   `text-blue-600` / `bg-blue-600` - Info states

### **Text Colors**

-   `text-gray-900` / `dark:text-gray-100` - Primary text
-   `text-gray-700` / `dark:text-gray-300` - Body text
-   `text-gray-600` / `dark:text-gray-400` - Muted text
-   `text-gray-400` / `dark:text-gray-500` - Disabled text

### **Backgrounds**

-   `bg-white` / `dark:bg-gray-800` - Cards, surfaces
-   `bg-gray-50` / `dark:bg-gray-900` - Page backgrounds
-   `bg-gray-100` / `dark:bg-gray-700` - Hover states

---

## 📐 SPACING

```blade
<!-- Padding -->
p-4   <!-- 16px - Small -->
p-6   <!-- 24px - Medium (cards) -->
p-8   <!-- 32px - Large -->

<!-- Gap -->
gap-3 <!-- 12px - Tight -->
gap-4 <!-- 16px - Medium -->
gap-6 <!-- 24px - Comfortable -->

<!-- Margin -->
mb-2  <!-- 8px - Tiny -->
mb-4  <!-- 16px - Small -->
mb-6  <!-- 24px - Medium -->
mb-8  <!-- 32px - Large -->
```

---

## 🔘 BORDER RADIUS

```blade
rounded-lg    <!-- 8px - Buttons, badges -->
rounded-xl    <!-- 12px - Cards, inputs -->
rounded-2xl   <!-- 16px - Large containers -->
rounded-full  <!-- Pills, avatars -->
```

---

## 🌫️ SHADOWS

```blade
shadow-sm     <!-- Subtle elevation -->
shadow-md     <!-- Medium elevation -->
shadow-lg     <!-- High elevation -->
shadow-xl     <!-- Maximum elevation (modals) -->

<!-- With hover -->
shadow-sm hover:shadow-md
```

---

## 🎭 TRANSITIONS

```blade
transition-all duration-200  <!-- Standard (200ms) -->
transition-fast              <!-- Fast (150ms) -->
transition-base              <!-- Base (200ms) -->
transition-slow              <!-- Slow (300ms) -->
```

---

## ♿ ACCESSIBILITY

```blade
<!-- Focus Ring -->
<button class="focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
    Accessible Button
</button>

<!-- ARIA Labels -->
<button aria-label="Close dialog">
    <svg>...</svg>
</button>

<!-- Form Associations -->
<label for="email" class="form-label">Email</label>
<input id="email" aria-describedby="email-error" />
<div id="email-error" class="form-error">Error message</div>
```

---

## 📱 RESPONSIVE

```blade
<!-- Mobile-first approach -->
<div class="text-sm sm:text-base lg:text-lg">Responsive text</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Responsive grid -->
</div>

<!-- Touch targets (minimum 44x44px) -->
<button class="p-3 min-w-[44px] min-h-[44px]">
    <svg class="w-5 h-5">...</svg>
</button>
```

---

## 🌙 DARK MODE

All components support dark mode automatically. Just use the standard classes:

```blade
<!-- Automatically switches -->
<div class="bg-white dark:bg-gray-800">
    <p class="text-gray-900 dark:text-gray-100">
        This text adapts to dark mode
    </p>
</div>
```

---

## 🚀 COMPONENT COMPOSITION

Combine classes for custom components:

```blade
<!-- Feature Card -->
<div class="card-hover border-t-4 border-indigo-500">
    <div class="card-body text-center">
        <div class="w-12 h-12 bg-indigo-100 rounded-full mx-auto mb-4 flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-600">...</svg>
        </div>
        <h3 class="text-heading text-lg mb-2">Feature Title</h3>
        <p class="text-muted text-sm">Description text</p>
    </div>
</div>

<!-- Alert -->
<div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
    <svg class="w-5 h-5 text-blue-600 flex-shrink-0">...</svg>
    <div>
        <h4 class="text-heading text-sm font-semibold mb-1">Information</h4>
        <p class="text-body text-sm">Your alert message here</p>
    </div>
</div>
```

---

## 💡 PRO TIPS

1. **Always use semantic classes** (`.btn-primary`, not inline Tailwind)
2. **Combine with Tailwind utilities** when needed (`.btn-primary w-full`)
3. **Use dark: prefix** for all color classes
4. **Test accessibility** with keyboard navigation
5. **Check mobile** with browser dev tools
6. **Use components** (`<x-primary-button>`) when available

---

**Need more help?** Check the full documentation:

-   `DESIGN_SYSTEM_SUMMARY.md` - Complete guide
-   `DESIGN_SYSTEM_CHECKLIST.md` - Implementation roadmap
-   `resources/css/app.css` - All available classes

**Rebuild after changes:**

```bash
npm run build
php artisan view:clear
```
