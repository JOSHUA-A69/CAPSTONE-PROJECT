# 🔧 Cache Issues Fix - Windows File Permission Error

## Error You Saw

```
In Filesystem.php line 233:
rename(C:\Users\Hannah\Desktop\CAPSTONE-PROJECT\bootstrap\cache\serF285.tmp,
C:\Users\Hannah\Desktop\CAPSTONE-PROJECT\bootstrap\cache/services.php):
Access is denied (code: 5)
```

## ✅ **FIXED!**

I've cleared all the caches for you. The error is gone now.

---

## What Caused This?

This is a **Windows file permission issue** that happens when:

-   ✅ Laravel tries to write cache files
-   ✅ Windows locks the file temporarily
-   ✅ Antivirus software scans the file
-   ✅ Another process has the file open

---

## How to Fix (If It Happens Again)

### **Option 1: Clear Individual Caches** ⭐ Recommended

Run these commands **inside Docker** (not on Windows):

```bash
# Clear all caches at once
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan view:clear
docker exec laravel_app php artisan route:clear
```

### **Option 2: One Command to Clear All**

```bash
# This clears everything
docker exec laravel_app php artisan optimize:clear
```

**Note**: If you get the same error with `optimize:clear`, use Option 1 instead (clear caches individually).

---

## Why Run Inside Docker?

✅ **Running inside Docker** (`docker exec laravel_app`) avoids Windows permission issues  
❌ **Running on Windows** (`php artisan`) can trigger permission errors

The Docker container has proper Unix-style permissions and doesn't have Windows file locking issues.

---

## Prevention Tips

### **1. Don't Run Artisan Commands on Windows**

❌ **DON'T DO THIS** (on Windows):

```bash
php artisan optimize:clear
php artisan cache:clear
```

✅ **DO THIS** (inside Docker):

```bash
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan config:clear
```

### **2. Exclude Laravel from Antivirus**

Add these folders to your antivirus **exclusion list**:

-   `C:\Users\Hannah\Desktop\CAPSTONE-PROJECT\bootstrap\cache`
-   `C:\Users\Hannah\Desktop\CAPSTONE-PROJECT\storage`

**Windows Defender Exclusion Steps:**

1. Windows Security → Virus & threat protection
2. Manage settings → Exclusions
3. Add folder: `C:\Users\Hannah\Desktop\CAPSTONE-PROJECT`

### **3. Close VS Code Before Clearing Caches**

Sometimes VS Code locks files. If you get errors:

1. Close VS Code
2. Run cache clear commands
3. Reopen VS Code

---

## What Each Cache Does

| Cache                 | What It Stores                        | When to Clear                      |
| --------------------- | ------------------------------------- | ---------------------------------- |
| **Application Cache** | General app data                      | After changing cache-related code  |
| **Config Cache**      | Configuration files (.env, config/\*) | After editing .env or config files |
| **View Cache**        | Compiled Blade templates              | After editing .blade.php files     |
| **Route Cache**       | Route definitions                     | After editing routes/web.php       |

---

## Quick Commands Reference

### **Clear All Caches (Safe Method)**

```bash
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan view:clear
docker exec laravel_app php artisan route:clear
```

### **After Editing .env File**

```bash
docker exec laravel_app php artisan config:clear
```

### **After Editing Blade Views**

```bash
docker exec laravel_app php artisan view:clear
```

### **After Editing Routes**

```bash
docker exec laravel_app php artisan route:clear
```

### **After Editing Any Code**

```bash
docker exec laravel_app php artisan optimize:clear
```

---

## Troubleshooting

### **Still Getting Permission Errors?**

**1. Restart Docker:**

```bash
docker compose restart
```

**2. Delete Cache Files Manually:**

Close VS Code, then delete these folders:

-   `bootstrap\cache\*` (keep the .gitignore file)
-   `storage\framework\cache\*`
-   `storage\framework\views\*`

**3. Check File Permissions (Windows):**

-   Right-click `CAPSTONE-PROJECT` folder
-   Properties → Security tab
-   Make sure your user has "Full control"

**4. Run as Administrator:**

Open PowerShell/Command Prompt as Administrator, then:

```bash
docker exec laravel_app php artisan cache:clear
```

---

## When You See These Errors

### **"Access is denied (code: 5)"**

✅ Run commands inside Docker: `docker exec laravel_app`

### **"Permission denied"**

✅ Close VS Code and try again  
✅ Check antivirus exclusions

### **"File is locked"**

✅ Restart Docker containers  
✅ Close all IDEs/editors

### **"Cannot write to bootstrap/cache"**

✅ Delete cache files manually  
✅ Run `docker compose restart`

---

## Current Status

✅ **All caches cleared successfully**  
✅ **Staff dashboard fixed** (removed non-existent `staff.services.index` route)  
✅ **System ready to use**

---

## What Was Fixed Today

1. ✅ **Removed broken link** from staff dashboard (Manage Services)
2. ✅ **Cleared all caches** (application, config, view, route)
3. ✅ **No more route errors** on staff dashboard

---

## Test It Now

1. **Refresh your browser** (Ctrl + Shift + R)
2. **Go to staff dashboard**: http://localhost:8000/staff
3. **You should see only 2 boxes**:
    - Manage Reservations
    - Manage Organizations
4. **No more errors!** ✅

---

## Pro Tips

💡 **Always use Docker commands** for artisan  
💡 **Clear caches after code changes**  
💡 **Restart browser after clearing view cache**  
💡 **Use hard refresh** (Ctrl + Shift + R) to see changes

---

_Last Updated: October 17, 2025_  
_Issue: Windows file permission error on cache operations_  
_Solution: Use Docker exec for all artisan commands_
