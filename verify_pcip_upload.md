# 🔍 Verify PCIP Upload on Hostinger

## ✅ **Files That Should Be Uploaded:**

### **1. FortressSmsService.php**
**Location:** `app/Services/FortressSmsService.php`
**Size:** ~3-4 KB
**Purpose:** SMS sending service

### **2. services.php (Updated)**
**Location:** `config/services.php`
**Contains:** Fortress SMS configuration block

### **3. .env File (Updated)**
**Location:** `.env` (root folder)
**Should have:**
```env
FORTRESS_SMS_API_URL=https://crm.pradytecai.com/api/2/messages/send
FORTRESS_SMS_API_KEY=USr4kTWk6nEcolzwiSTZJgMjZ0c4F2uh
FORTRESS_SMS_CLIENT_ID=2
FORTRESS_SMS_SENDER_ID=FORTRESS
FORTRESS_SMS_ENABLED=true
```

---

## 📋 **Step-by-Step Verification:**

### **Method 1: Via Hostinger File Manager** (Easiest)

1. **Login to Hostinger:**
   - Go to: https://hpanel.hostinger.com
   - Login with your credentials

2. **Open File Manager:**
   - Click on **File Manager**
   - Navigate to your PCIP folder (usually `public_html/pcip` or `domains/your-domain.com/public_html`)

3. **Verify Files:**

   #### Check #1: FortressSmsService.php
   ```
   Navigate to: app/Services/
   File should exist: FortressSmsService.php
   ```
   - ✅ File exists
   - ✅ File size is ~3-4 KB
   - ✅ Last modified: Today's date

   #### Check #2: services.php
   ```
   Navigate to: config/
   File should exist: services.php
   ```
   - Click to edit/view
   - Search for: `fortress_sms`
   - Should find configuration block

   #### Check #3: .env File
   ```
   Navigate to: root folder (where artisan file is)
   File should exist: .env
   ```
   - Click to edit/view
   - Search for: `FORTRESS_SMS`
   - Should find 5 lines of config

---

### **Method 2: Via SSH** (If you have SSH access)

1. **Connect via SSH:**
```bash
ssh your-username@your-domain.com
cd domains/your-domain.com/public_html/pcip
```

2. **Check Files:**
```bash
# Check if service file exists
ls -la app/Services/FortressSmsService.php

# View service file
cat app/Services/FortressSmsService.php | head -20

# Check .env for Fortress config
grep FORTRESS .env

# Check config file
grep fortress_sms config/services.php
```

3. **Test SMS Sending:**
```bash
php artisan tinker
>>> (new \App\Services\FortressSmsService())->send('254728883160', 'Test from PCIP');
>>> exit
```

---

### **Method 3: Create Test Endpoint** (Recommended)

Create this test file to verify everything works:

**File:** `public/test-fortress-sms.php`
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Fortress SMS Integration...<br><br>";

try {
    $sms = new \App\Services\FortressSmsService();
    
    echo "✅ Service loaded successfully<br>";
    echo "✅ Configuration found<br><br>";
    
    // Uncomment to test actual sending:
    // $result = $sms->send('254728883160', 'Test from PCIP');
    // echo "Result: " . json_encode($result);
    
    echo "<strong>Setup is correct!</strong>";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
```

**Access:** `https://your-pcip-domain.com/test-fortress-sms.php`

---

## 🔍 **Quick Verification Checklist:**

- [ ] FortressSmsService.php exists in `app/Services/`
- [ ] services.php contains `fortress_sms` configuration
- [ ] .env has `FORTRESS_SMS_API_URL` line
- [ ] .env has `FORTRESS_SMS_API_KEY` line
- [ ] .env has `FORTRESS_SMS_ENABLED=true` line
- [ ] File permissions are correct (644 for files, 755 for folders)
- [ ] Laravel cache cleared: `php artisan config:clear`

---

## 🚨 **Common Issues:**

### **Issue 1: Files not visible**
**Solution:** Check you're in the right directory (public_html/pcip)

### **Issue 2: Permission denied**
**Solution:** Set correct permissions:
```bash
chmod 644 app/Services/FortressSmsService.php
chmod 644 config/services.php
chmod 644 .env
```

### **Issue 3: Config not loading**
**Solution:** Clear Laravel cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📞 **What's Your PCIP Domain?**

Please provide your PCIP domain and I can:
1. Check if it's accessible
2. Create a test endpoint
3. Verify the setup remotely

**Example:** `https://pcip.yourdomain.com`

---

## ✅ **Once Verified:**

Test sending SMS from PCIP:
```php
use App\Services\FortressSmsService;

$sms = new FortressSmsService();
$result = $sms->send('254728883160', 'Hello from PCIP!');

if ($result['success']) {
    echo "SMS sent!";
}
```

**Ready to verify your Hostinger upload!** 🚀



