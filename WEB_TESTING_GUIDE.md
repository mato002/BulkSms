# 🌐 Web Testing Guide - Where to Go

## 🚀 Start Your Server First

```bash
php artisan serve
```

Then open: **http://localhost:8000**

---

## ✅ FLOW 1: SENDER ONBOARDING (User Registration)

### Step 1: Register
**URL:** http://localhost:8000/register

Fill in:
- Name: `Your Name`
- Email: `your@email.com`
- Password: `password123`
- Confirm Password: `password123`

Click **Register**

✅ **Success:** You'll be redirected to the dashboard

---

## ✅ FLOW 2: TOP-UP PROCESS

### Step 1: Go to Settings
**URL:** http://localhost:8000/settings

### Step 2: Check Current Balance
Look for the "Balance" section - you'll see your current balance

### Step 3: Top-Up (if available in UI)
- If there's a "Top Up" button, click it
- Enter amount (e.g., 100)
- Select payment method (M-Pesa)
- Enter phone: `254712345678`
- Click "Submit"

✅ **Success:** You'll see a transaction confirmation

**Alternative:** Check balance via API:
```bash
# In another terminal
curl http://localhost:8000/api/1/client/balance \
  -H "Authorization: Bearer YOUR_API_KEY"
```

---

## ✅ FLOW 3: SENDING SMS

### Option A: Send via Campaign

**URL:** http://localhost:8000/campaigns/create

1. Fill in campaign details:
   - Name: `Test Campaign`
   - Message: `Hello, this is a test message`
   - Sender ID: `PRADY_TECH`
   
2. Select recipients or add phone number: `254712345678`

3. Click **Send Campaign**

✅ **Success:** Campaign status shows "Sent" or "Processing"

### Option B: Send via Messages

**URL:** http://localhost:8000/messages

1. Click "New Message"
2. Enter phone: `254712345678`
3. Type message: `Test SMS`
4. Click "Send"

✅ **Success:** Message appears in sent messages list

---

## ✅ FLOW 4: BALANCE CHECK

### Multiple Places to Check:

### 1. Dashboard (Home Page)
**URL:** http://localhost:8000/

Look for:
- 💰 Balance widget (top right or cards)
- Shows: KES amount and Units

### 2. Settings Page
**URL:** http://localhost:8000/settings

Balance section shows:
- Current Balance (KES)
- Available Units
- Price per Unit

### 3. Analytics Page
**URL:** http://localhost:8000/analytics

Shows:
- Balance trends
- Usage statistics
- Transaction history

### 4. Notifications
**URL:** http://localhost:8000/notifications

Check for:
- Low balance alerts
- Top-up confirmations

---

## 🎯 Quick Visual Test Route

Visit each page in order:

```
1. http://localhost:8000/register          → Register
2. http://localhost:8000/                  → Dashboard (see balance)
3. http://localhost:8000/settings          → Check settings & balance
4. http://localhost:8000/campaigns/create  → Create & send campaign
5. http://localhost:8000/campaigns         → View campaign status
6. http://localhost:8000/analytics         → Check analytics
7. http://localhost:8000/messages          → View sent messages
```

---

## 🔍 What to Look For

### ✅ Everything Working:
- ✓ Can register/login
- ✓ Dashboard shows balance
- ✓ Can create campaigns
- ✓ Messages send successfully
- ✓ Balance decreases after sending
- ✓ Analytics show data

### ⚠️ Issues:
- ✗ Can't login → Check database
- ✗ No balance shown → Check client setup
- ✗ Can't send → Check SMS gateway config
- ✗ Wrong balance → Check calculations

---

## 📱 Test Everything in 5 Minutes

1. **Register** (1 min)
   - http://localhost:8000/register
   - Create account

2. **Check Dashboard** (30 sec)
   - http://localhost:8000/
   - Verify balance shows

3. **View Settings** (30 sec)
   - http://localhost:8000/settings
   - Check balance details

4. **Send Test SMS** (2 min)
   - http://localhost:8000/campaigns/create
   - Create simple campaign
   - Send to your number

5. **Verify** (1 min)
   - Check campaigns page
   - Check messages page
   - Check balance decreased

---

## 🎉 Success Indicators

You'll know everything works when:

✅ Login successful
✅ Dashboard loads with data
✅ Balance is visible
✅ Can create campaigns
✅ SMS sends (check status)
✅ Balance updates after sending
✅ Transaction history shows
✅ Analytics display correctly

---

## 🛠️ If Something Doesn't Work

**Can't access pages?**
```bash
php artisan serve
```

**Login not working?**
```bash
php artisan migrate:fresh --seed
```

**Balance not showing?**
```sql
UPDATE clients SET balance = 1000 WHERE id = 1;
```

**Need test user?**
- Email: `admin@example.com`
- Password: `password` (if seeded)

---

**Start here:** http://localhost:8000/register

