# 🔑 Elevated Registration Codes Reference

## Overview

Your system requires **elevated registration codes** for users who want to register as:

-   **Admin**
-   **Staff**
-   **Adviser**
-   **Priest**

Regular **requestor** accounts don't need a code.

---

## Current Codes (Development)

### **Default Codes in `.env`:**

```
ELEVATED_REGISTRATION_CODES=CREAM2025,ADMIN2025,HNUS3CR3T
```

**These codes work for ANY elevated role (admin, staff, adviser, priest).**

### **Individual Codes:**

| Code        | Purpose                      | Status    |
| ----------- | ---------------------------- | --------- |
| `CREAM2025` | General elevated role access | ✅ Active |
| `ADMIN2025` | Admin/Staff access           | ✅ Active |
| `HNUS3CR3T` | Backup code                  | ✅ Active |

---

## How It Works

### **Registration Flow:**

1. User goes to: http://localhost:8000/register
2. Selects role from dropdown
3. If role is elevated (admin, staff, adviser, priest):
    - **"Elevated registration code" field appears**
    - User must enter one of the valid codes
    - Form won't submit without valid code
4. If role is requestor:
    - No code needed
    - Register normally

### **Code Validation:**

```php
// In config/registration.php
'elevated_roles' => ['admin', 'staff', 'adviser', 'priest'],
'elevated_codes' => array_filter(array_map('trim', explode(',', env('ELEVATED_REGISTRATION_CODES', '')))),
```

The system:

-   ✅ Checks if selected role is in `elevated_roles` array
-   ✅ Validates entered code against `elevated_codes` list
-   ✅ Trims whitespace from codes
-   ✅ Case-sensitive matching

---

## How to Change/Add Codes

### **Option 1: Edit `.env` File (Recommended)**

1. Open `.env` file
2. Find the line: `ELEVATED_REGISTRATION_CODES=CREAM2025,ADMIN2025,HNUS3CR3T`
3. Add/remove codes (comma-separated, no spaces):

```env
# Multiple codes
ELEVATED_REGISTRATION_CODES=CODE1,CODE2,CODE3

# Single code
ELEVATED_REGISTRATION_CODES=ONLYCODE

# Remove all codes (blocks all elevated registration)
ELEVATED_REGISTRATION_CODES=
```

4. Save file
5. Clear config cache:

```bash
docker exec laravel_app php artisan config:clear
```

### **Option 2: Command Line (Temporary)**

```bash
# Set via environment variable (until container restart)
docker exec laravel_app sh -c 'export ELEVATED_REGISTRATION_CODES=NEWCODE1,NEWCODE2'
```

---

## Production Setup

### **🚨 IMPORTANT: Change Codes Before Production!**

The default codes (`CREAM2025`, `ADMIN2025`, `HNUS3CR3T`) are for development only.

**Before deploying to production:**

1. Generate strong, random codes:

```bash
# Generate a random code
openssl rand -base64 12

# Example output: xK8mP3nQ9vR2
```

2. Update `.env` on production server:

```env
ELEVATED_REGISTRATION_CODES=xK8mP3nQ9vR2,aB4cD9eF6gH1,mN7oP2qR5sT8
```

3. **Keep codes secret!**

    - Don't commit to Git
    - Share only with authorized personnel
    - Rotate periodically

4. Consider **one-time use codes**:
    - Issue unique code per person
    - Disable after first use
    - (Requires custom implementation)

---

## Testing Registration

### **Test as Requestor (No Code Needed):**

1. Go to: http://localhost:8000/register
2. Fill form:
    - First name: John
    - Last name: Doe
    - Email: john@test.com
    - Phone: 09171234567
    - Role: **Requestor** ← Select this
    - Password: password
3. **No elevated code field shown**
4. Click Register
5. ✅ Should succeed

---

### **Test as Admin (Code Required):**

1. Go to: http://localhost:8000/register
2. Fill form:
    - First name: Admin
    - Last name: User
    - Email: admin@test.com
    - Phone: 09181234567
    - Role: **Admin** ← Select this
    - **Elevated code: CREAM2025** ← Enter valid code
    - Password: password
3. **Elevated code field appears**
4. Click Register
5. ✅ Should succeed

---

### **Test Invalid Code:**

1. Select elevated role (admin/staff/adviser/priest)
2. Enter wrong code: `WRONGCODE`
3. Click Register
4. ❌ Error: "The provided elevated registration code is invalid."

---

## Configuration Reference

### **File: `config/registration.php`**

```php
return [
    // Allow role selection on registration form
    'allow_role_selection' => env('ALLOW_ROLE_SELECTION', true),

    // Roles requiring elevated code
    'elevated_roles' => ['admin', 'staff', 'adviser', 'priest'],

    // Valid codes from .env
    'elevated_codes' => array_filter(
        array_map('trim', explode(',', env('ELEVATED_REGISTRATION_CODES', '')))
    ),
];
```

### **File: `.env`**

```env
# Enable role selection on registration form
ALLOW_ROLE_SELECTION=true

# Comma-separated codes (no spaces!)
ELEVATED_REGISTRATION_CODES=CREAM2025,ADMIN2025,HNUS3CR3T
```

---

## Disable Role Selection (Production)

If you want **only admins to assign roles** (no self-registration for elevated roles):

1. Edit `.env`:

```env
ALLOW_ROLE_SELECTION=false
ELEVATED_REGISTRATION_CODES=
```

2. All new registrations will be **requestor** role by default
3. Admins must manually change roles in database/admin panel

---

## Quick Commands

### **Clear Config Cache:**

```bash
docker exec laravel_app php artisan config:clear
```

### **Check Current Config:**

```bash
docker exec laravel_app php artisan tinker

# Then in Tinker:
config('registration.elevated_codes')
// Output: ["CREAM2025", "ADMIN2025", "HNUS3CR3T"]
```

### **Test Code Validation:**

```bash
docker exec laravel_app php artisan tinker

# In Tinker:
$codes = config('registration.elevated_codes');
in_array('CREAM2025', $codes);  // Returns: true
in_array('WRONGCODE', $codes);  // Returns: false
```

---

## Security Best Practices

### **Development:**

✅ Use simple codes for testing
✅ Keep `ALLOW_ROLE_SELECTION=true` for easy testing
✅ Share codes with team members

### **Production:**

✅ Use strong, random codes (12+ characters)
✅ Consider `ALLOW_ROLE_SELECTION=false` (manual role assignment)
✅ Never commit codes to Git
✅ Rotate codes every 3-6 months
✅ Use one-time codes if possible
✅ Log code usage for security audit
✅ Limit number of valid codes

---

## Troubleshooting

### **"The elevated code field is required" error:**

-   ✅ Selected role is admin/staff/adviser/priest
-   ✅ Must enter a valid code
-   ✅ Check `.env` has codes configured

### **"The provided elevated registration code is invalid" error:**

-   ❌ Code doesn't match any in `.env`
-   ❌ Check for typos
-   ❌ Codes are case-sensitive
-   ❌ No spaces allowed in code
-   ✅ Run `php artisan config:clear`

### **Code field doesn't appear:**

-   ❌ Selected role is requestor (no code needed)
-   ❌ Check `ALLOW_ROLE_SELECTION=true` in `.env`
-   ✅ Try selecting admin/staff/adviser/priest

### **Any code works (security issue!):**

-   ❌ Validation not working
-   ✅ Check `config/registration.php` exists
-   ✅ Run `php artisan config:clear`
-   ✅ Check browser console for JavaScript errors

---

## Current Setup Summary

**For your development environment:**

✅ **Elevated codes are**: `CREAM2025`, `ADMIN2025`, `HNUS3CR3T`  
✅ **Use any of these codes** when registering as admin, staff, adviser, or priest  
✅ **No code needed** for requestor role  
✅ **Codes are case-sensitive**

**Test Registration URLs:**

-   Register: http://localhost:8000/register
-   Login: http://localhost:8000/login

---

## Example Registration

### **Creating an Admin Account:**

```
First name: System
Last name: Administrator
Email: sysadmin@hnu.edu.ph
Phone: 09171234567
Role: Admin
Elevated Code: CREAM2025
Password: SecurePass123!
Confirm Password: SecurePass123!
```

Click **Register** → ✅ Success!

---

**Remember**: Change these codes before going to production! 🔒

**File Generated**: October 17, 2025  
**Purpose**: Development testing of elevated role registration
