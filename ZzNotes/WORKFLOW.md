# 🔄 Simple Git Workflow - Work on Multiple Devices

## ⚡ Quick Commands

### Before You Start Working (ANY Device)

```bash
cd /c/Users/Hannah/Desktop/CAPSTONE-PROJECT
git pull origin new-feature
composer install
npm install
php artisan serve
```

### After You Finish Working (ANY Device)

```bash
git add .
git commit -m "Your description here"
git push origin new-feature
```

---

## 📝 Daily Workflow

### Starting Your Day

1. Open terminal
2. Go to project folder
3. Pull latest changes: `git pull origin new-feature`
4. Start server: `php artisan serve`

### Ending Your Day

1. Save changes: `git add .`
2. Commit: `git commit -m "What you did today"`
3. Push: `git push origin new-feature`

---

## 🎯 Golden Rules

✅ **ALWAYS PULL BEFORE YOU START**

```bash
git pull origin new-feature
```

✅ **ALWAYS PUSH WHEN YOU FINISH**

```bash
git add .
git commit -m "Description"
git push origin new-feature
```

❌ **NEVER** work without pulling first
❌ **NEVER** forget to push before switching devices

---

## 🔥 Quick Fix If You Forgot to Push

On your other device:

```bash
git pull origin new-feature
# Fix any conflicts in files
git add .
git commit -m "Fixed conflicts"
git push origin new-feature
```

---

## 💾 Check Status Anytime

```bash
# See what you changed
git status

# See recent commits
git log --oneline -5

# Check if remote has updates
git fetch
git status
```

---

## 🚀 Complete Session

```bash
# START SESSION
cd /c/Users/Hannah/Desktop/CAPSTONE-PROJECT
git pull origin new-feature
composer install
npm install
php artisan serve

# [CODE HERE]

# END SESSION
git add .
git commit -m "Added new feature"
git push origin new-feature
```

---

## 📱 Print & Keep This

```
┌─────────────────────────────────┐
│  BEFORE: git pull origin new-feature
│
│  DURING: code & save files
│
│  AFTER:  git add .
│          git commit -m "message"
│          git push origin new-feature
└─────────────────────────────────┘
```

---

**Remember: Pull → Code → Push** 🔄
