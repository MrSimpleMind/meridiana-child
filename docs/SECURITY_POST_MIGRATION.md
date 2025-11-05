# 🔒 SECURITY HARDENING - Post-Migration Checklist

**Data**: 5 Novembre 2025
**Scopo**: Proteggere sito dopo migrazione a live
**Timeline**: 2-4 ore
**Importanza**: 🔴 CRITICAL

---

## ⚡ IMMEDIATE ACTIONS (First Hour)

### 1. Update Everything
**Time**: 30 min

```
☐ WordPress Core
  wp-admin > Dashboard > Updates
  Click: Update to latest version
  Time: 5-10 min

☐ All Plugins
  wp-admin > Plugins > Updates
  Click: Update all plugins
  Time: 10-15 min

☐ Theme
  wp-admin > Appearance > Themes
  Update Blocksy if update available
  Time: 5 min

✓ Verification:
  Dashboard should show: "All good!"
```

### 2. Strong Admin Password
**Time**: 5 min

```
☐ WordPress Users
  wp-admin > Users > Your Account

☐ Generate Strong Password
  Click: "Generate Password"
  Copy: Generated password (16+ chars)

☐ Requirements:
  Uppercase: YES
  Lowercase: YES
  Numbers: YES
  Symbols: YES
  Length: 16+ characters

Example: Tr0p!c@lThund3r2025

☐ Save: Click "Update Profile"
```

### 3. Install Wordfence
**Time**: 15 min

```
☐ Add Plugin
  wp-admin > Plugins > Add New
  Search: "Wordfence Security"

☐ Install & Activate
  Click: Install Now
  Click: Activate Plugin

☐ Configure
  Go: Wordfence > Firewall
  Enable: All default options
  Click: Save Changes

✓ Wordfence is now protecting your site
```

---

## 🔧 WEEK 1 SECURITY SETUP

### Task 1: File Editing Protection
**Time**: 5 min

```
☐ Edit wp-config.php
  Via: FTP/SFTP or cPanel file manager
  Location: /public_html/wp-config.php

☐ Add Line (BEFORE "That's all, stop editing!"):
  define( 'DISALLOW_FILE_EDIT', true );

Result: wp-admin theme/plugin editor disabled
Benefit: Reduces damage if account hacked
```

### Task 2: Login Security
**Time**: 15 min

```
☐ Login URL Obfuscation
  Wordfence > Login Security
  Option: Change login URL (optional)
  Example: /wp-login.php → /secure-admin/

☐ Limit Login Attempts
  Wordfence > Login Security
  Max attempts: 5
  Block duration: 24 hours
  Lock out on failed 2FA: YES

✓ Prevents brute force attacks
```

### Task 3: 2FA Setup (Optional but Recommended)
**Time**: 10 min

```
☐ Wordfence 2FA
  Wordfence > Login Security
  Enable: Two-Factor Authentication

☐ Download Authenticator
  Phone: Google Authenticator or Authy
  Scan: QR code from Wordfence

☐ Test: Logout and login with 2FA code

✓ Blocks access even if password leaked
```

### Task 4: Database Security
**Time**: 10 min

```
☐ Table Prefix
  Check: wp-config.php line with $table_prefix
  Status: Should NOT be default "wp_"
  If default:
    - Complex migration needed
    - Contact Siteground if concerned
    - Current setup is OK

☐ Database User Password
  Ask: Siteground via support
  Action: Change database user password
  Store: In secure password manager
```

### Task 5: Backup Verification
**Time**: 10 min

```
☐ Siteground Backups
  cPanel > Backups
  Verify: Daily backup schedule enabled
  Frequency: At least daily
  Retention: 30 days minimum

☐ Test Restore (Optional)
  Warning: Don't actually restore
  But verify you CAN restore if needed
  Process: cPanel > Backups > Restore

✓ Backups are your safety net
```

---

## 🎯 WEEK 2 SECURITY AUDIT

### Task 1: Malware Scanning
**Time**: 30 min (semi-automated)

```
☐ Wordfence Scan
  Wordfence > Scan
  Click: Start Full Scan
  Wait: 15-30 min (runs in background)
  Review: Scan results
  Action: Quarantine any suspicious files

☐ Verify Results
  Status: Should show "All Clear" or minor issues
  If: Major issues found
    → Contact Siteground support
    → Provide scan report
```

### Task 2: Security Headers Configuration
**Time**: 20 min

```
Siteground provides:
✓ HTTPS/SSL         (automatic)
✓ HSTS headers      (automatic)
✓ X-Frame-Options   (automatic)

For Advanced (optional):
☐ cPanel > ModSecurity
  Verify: Enabled
  Level: Medium or High

☐ cPanel > SSL/TLS Status
  Verify: Green lock on all pages
  Test: https://yourdomain.com/
```

### Task 3: User Access Review
**Time**: 15 min

```
☐ wp-admin > Users > All Users
  Review: List of users
  Remove: Any suspicious accounts
  Check: User roles appropriate
    - Admin: Only you + 1 backup
    - Instructor: Course creators
    - Student: Regular users

☐ Remove Demo/Test Users
  Search: "test", "demo", "admin2"
  Delete: Any temporary accounts

✓ Only legitimate users should have access
```

### Task 4: Plugin Audit
**Time**: 15 min

```
☐ wp-admin > Plugins > All Plugins
  Review: Every installed plugin
  Status: All active plugins needed?

Remove if:
- "Duplicate Page" (test plugin)
- Any "debug" plugins
- Any development-only plugins
- Unused plugins

Keep:
✓ LearnDash
✓ Advanced Custom Fields Pro
✓ Super Progressive Web Apps
✓ PDF Embedder
✓ Wordfence Security
✓ Others you actively use

☐ Deactivate: Click deactivate
  Verify: Site still works
  Delete: If confirmed you don't need it
```

### Task 5: Settings Audit
**Time**: 15 min

```
☐ WordPress Settings > General
  Verify: Siteurl = https://yourdomain.com
  Verify: Home = https://yourdomain.com
  Verify: Not HTTP (should be HTTPS)

☐ WordPress Settings > Discussion
  Spam comments: Enable moderation
  Email notifications: Enable

☐ WordPress Settings > Permalinks
  Current: Should be custom (not default)
  Example: /%postname%/
  Status: Don't change - it's working

✓ Core settings look good
```

---

## 📋 MONTH 1 ONGOING SECURITY

### Weekly Tasks (15 min each)

```
☐ MONDAY: Check Wordfence alerts
  Wordfence > Firewall Alerts
  Review: Any blocked attacks
  Take action: If patterns emerge

☐ Update Check
  wp-admin > Dashboard > Updates
  Update: Any new patches
  Test: Check site still works

☐ Backup Verification
  Siteground cPanel > Backups
  Verify: Today's backup exists
  Size: Should be similar to yesterday
```

### Monthly Tasks (1 hour)

```
☐ Full Security Scan
  Wordfence > Scan
  Run: Full website scan
  Review: Results completely

☐ Activity Log Review
  Wordfence > Activity Log
  Look for: Unusual activity
  Delete: Suspicious actions if any

☐ User Audit
  wp-admin > Users
  Verify: All active users are legitimate
  Check: No unauthorized accounts created

☐ SSL Certificate Check
  HTTPS browser: Lock icon visible
  Certificate: Check expiry date (Let's Encrypt auto-renews)
  Status: Should always be green
```

---

## 🔐 SPECIFIC SECURITY FOR LA MERIDIANA

### OneSignal API Keys
**Risk**: Medium (stored in database)

```
Protection:
☐ Monthly rotation (if possible via OneSignal)
☐ Limit OneSignal permissions to minimum needed
☐ Monitor OneSignal access logs
☐ Alert: If any unauthorized sends detected
```

### LearnDash Data
**Risk**: Medium (contains user progress)

```
Protection:
☐ Database backups: Daily (automatic)
☐ Encryption: Database-level (contact Siteground)
☐ Access: Only wp-admin users can modify
☐ Audit: Monitor unauthorized progress changes

Check:
☐ wp-admin > LearnDash > Courses
  Only authorized people can edit courses
☐ wp-admin > Users
  Student role cannot edit others' progress
```

### ACF Form Data
**Risk**: Low (properly validated)

```
Protection:
☐ Nonce validation: Enabled ✓
☐ Capability checks: Enabled ✓
☐ Input sanitization: Enabled ✓
☐ File uploads: Scan before save

The code is SAFE - no additional protection needed
```

---

## ⚠️ SECURITY INCIDENT RESPONSE

### If You Notice Suspicious Activity

```
IMMEDIATE (0-15 min):
1. Don't panic
2. Change WordPress admin password
3. Take screenshot of suspicious activity
4. Note: Date, time, what you saw

INVESTIGATION (15-60 min):
1. Wordfence > Firewall Alerts
2. Wordfence > Activity Log
3. Look for: Unauthorized users, changes
4. Check: Modified files via Wordfence scan
5. Database: Any suspicious user accounts

REMEDIATION (1-4 hours):
1. Wordfence > Scan
2. Quarantine any malicious files
3. Delete suspicious user accounts
4. Change all passwords
5. Update all plugins/WordPress

COMMUNICATION (4-24 hours):
1. Assess: Do users need to know?
2. If: User data was accessed
   → Email affected users
   → Recommend password change
3. Contact: Siteground support if needed
4. Document: What happened + how you fixed it

RECOVERY (24+ hours):
1. Monitor: Wordfence alerts closely
2. Daily scans: Until confirmed clean
3. Review: Security measures to prevent recurrence
4. Update: Security settings based on incident
```

---

## ✅ FINAL SECURITY CHECKLIST

### Pre-Go-Live (Mandatory)

```
WORDPRESS UPDATES:
☐ WordPress core: Latest version
☐ All plugins: Updated
☐ Theme: Updated

PASSWORDS & AUTHENTICATION:
☐ Admin password: 16+ chars, strong
☐ Database password: Changed
☐ FTP/SFTP password: Changed or SSH keys used
☐ 2FA: Enabled on admin account

ACCESS CONTROL:
☐ DISALLOW_FILE_EDIT: Enabled in wp-config.php
☐ Login attempts: Limited to 5 max
☐ Admin URL: Secured
☐ User roles: Properly assigned

DATA PROTECTION:
☐ HTTPS/SSL: Active on all pages
☐ Database backups: Encrypted, stored offsite
☐ Backup restore: Procedure tested
☐ File permissions: Correct (644 files, 755 dirs)

MONITORING:
☐ Wordfence: Installed and configured
☐ Malware scanning: Scheduled daily
☐ Activity logging: Enabled
☐ Email alerts: Configured

DOCUMENTATION:
☐ Passwords: Stored securely (password manager)
☐ Incident response: Plan documented
☐ Admin procedures: Documented
☐ Support contacts: Saved (Siteground, registrar)
```

### Post-Go-Live (Ongoing)

```
DAILY:
☐ Check Wordfence alerts (5 min)
☐ Verify site is accessible (2 min)

WEEKLY:
☐ Review activity logs (10 min)
☐ Check for updates available (5 min)
☐ Backup verification (5 min)

MONTHLY:
☐ Full security scan (15 min)
☐ User audit (10 min)
☐ Password rotation for admin (5 min)
☐ Update WordPress & plugins (15 min)

QUARTERLY:
☐ Full security audit (1 hour)
☐ Penetration testing (optional)
☐ Review permissions (30 min)
☐ Update security procedures (30 min)

ANNUALLY:
☐ Professional security assessment
☐ Disaster recovery drill
☐ Update security policies
☐ Team training on security
```

---

## 📞 WHEN TO CONTACT SUPPORT

### Contact Siteground If:

```
✅ SSL certificate isn't working
✅ Login keeps failing (forgot password)
✅ Database connection error
✅ Can't access cPanel
✅ Malware suspected (let them help clean)
✅ Site is down or extremely slow
✅ Need help with DNS/domain transfer
✅ Want to upgrade backup plan

Phone: +1-888-376-6848 (24/7)
Email: support@siteground.com
Chat: Via cPanel
```

### DIY First If:

```
✓ Need to update WordPress
✓ Need to update plugins
✓ Need to change WordPress password
✓ Need to change admin email
✓ Need to install a plugin
✓ Have a question about wp-admin

All doable without support!
```

---

## 🎓 SECURITY TRAINING

### For Your Team:

```
Everyone should know:
- Don't share passwords
- Use strong passwords (16+ chars)
- Don't click suspicious links
- Report suspicious activity immediately
- Update WordPress when prompted
- Enable 2FA when available

Admin only should know:
- How to backup & restore
- How to scan with Wordfence
- How to add/remove users
- How to manage SSL certificate
- How to contact support
```

---

**Security Checklist**: 5 Novembre 2025
**Implementation Time**: 2-4 hours
**Ongoing Maintenance**: 1-2 hours/month
**Critical**: Do immediately after migration!

