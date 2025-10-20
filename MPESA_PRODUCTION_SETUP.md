# 📱 M-Pesa Production Setup Guide

## Current Status
- ✅ M-Pesa integration code is working
- ✅ Sandbox testing successful
- ⚠️ Using **Sandbox** environment (test only)
- ❌ Real phones won't receive sandbox prompts

---

## 🚀 How to Enable Real M-Pesa Payments

### Step 1: Get Production Credentials

1. **Visit Safaricom Daraja Portal**
   - Go to: https://developer.safaricom.co.ke
   - Login or create account

2. **Create Production App**
   - Click "My Apps" → "Add a new App"
   - Select "Lipa Na M-Pesa Online"
   - Fill in business details
   - Submit for approval

3. **Wait for Approval**
   - Usually takes 1-3 business days
   - Safaricom will verify your business details

4. **Get Your Credentials**
   Once approved, you'll receive:
   - ✅ Production Consumer Key
   - ✅ Production Consumer Secret
   - ✅ Production Passkey
   - ✅ Production Paybill/Till Number

---

### Step 2: Update Configuration

Once you have production credentials, update your `.env` file:

**File:** `C:\xampp\htdocs\BulkSms\.env`

```env
# M-Pesa Configuration
MPESA_ENV=production
MPESA_CONSUMER_KEY=YOUR_PRODUCTION_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_PRODUCTION_CONSUMER_SECRET
MPESA_PASSKEY=YOUR_PRODUCTION_PASSKEY
MPESA_SHORTCODE=YOUR_PRODUCTION_PAYBILL_NUMBER

# Callback URLs (Use your production domain)
MPESA_CALLBACK_URL=https://crm.pradytecai.com/api/webhooks/mpesa/callback
MPESA_TIMEOUT_URL=https://crm.pradytecai.com/api/webhooks/mpesa/timeout

# Transaction Type
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
```

---

### Step 3: Configure Webhooks on Safaricom

In your Daraja portal, set these callback URLs:

**Validation URL:**
```
https://crm.pradytecai.com/api/webhooks/mpesa/validation
```

**Confirmation URL:**
```
https://crm.pradytecai.com/api/webhooks/mpesa/callback
```

**Timeout URL:**
```
https://crm.pradytecai.com/api/webhooks/mpesa/timeout
```

⚠️ **Important:** These URLs must be:
- Public (accessible from internet)
- HTTPS (SSL certificate required)
- Responding within 30 seconds

---

### Step 4: Test in Production

After configuration, clear cache and test:

```bash
cd C:\xampp\htdocs\BulkSms
php artisan config:clear
php artisan cache:clear
```

Then test again:
```bash
php test_mpesa_stk_push.php
```

**Now you'll receive the prompt on your real phone!** 📱

---

## 🧪 Testing Options

### **Option A: Keep Using Sandbox (Testing Only)**

**Pros:**
- Free to test
- No approval needed
- Immediate testing

**Cons:**
- Won't send to real phones
- Can't collect real money
- Limited to sandbox app only

**Use When:**
- Developing/testing features
- Not ready for production
- Just want to verify integration

---

### **Option B: Switch to Production** ⭐ **Recommended**

**Pros:**
- Send to real phones
- Collect real payments
- Full M-Pesa functionality

**Cons:**
- Requires business verification
- Takes 1-3 days approval
- Transaction fees apply

**Use When:**
- Ready to go live
- Want to accept real payments
- Need to send to actual customers

---

## 📝 Current Sandbox Credentials

**Environment:** Sandbox (Test)

```
Consumer Key: Yt36YTWRLf1CL3RW47GidbAXtW1OcO4m7U5VuvA6x84BdoQV
Consumer Secret: p3o13LwjC48GjBGdvcnpptuQc90OSlHJvBeTwkXyJBNQFGJQnqN5gws4gf6frGdh
Passkey: bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
Shortcode: 174379 (Sandbox test shortcode)
```

**⚠️ These are TEST credentials** - won't work on real phones.

---

## ✅ Verification Checklist

After switching to production:

- [ ] Production credentials added to `.env`
- [ ] `MPESA_ENV=production` set
- [ ] Callback URLs configured on Daraja
- [ ] Domain has valid SSL certificate
- [ ] Config cache cleared
- [ ] Test STK push sent successfully
- [ ] Real phone receives M-Pesa prompt ✅

---

## 🔧 Troubleshooting

### "Request accepted but no prompt on phone"
- **Cause:** Using sandbox with real phone
- **Solution:** Switch to production OR use sandbox app

### "Invalid credentials"
- **Cause:** Wrong consumer key/secret
- **Solution:** Double-check credentials from Daraja portal

### "Callback URL not reachable"
- **Cause:** URL not accessible or no HTTPS
- **Solution:** Ensure domain is public with valid SSL

### "Request timeout"
- **Cause:** Callback took too long to respond
- **Solution:** Optimize callback processing speed

---

## 📞 Support

**Safaricom Support:**
- Email: apisupport@safaricom.co.ke
- Phone: 0711 051 111

**Daraja Portal:**
- https://developer.safaricom.co.ke

---

## 💡 Quick Summary

**To receive M-Pesa prompts on real phones:**

1. Get production credentials from Safaricom
2. Update `.env` with production settings
3. Set `MPESA_ENV=production`
4. Clear cache: `php artisan config:clear`
5. Test again

**That's it!** Real phones will then receive M-Pesa prompts. 🎉

---

**Current Status:** ⚠️ Sandbox (Testing Mode)  
**To Go Live:** Apply for production credentials at https://developer.safaricom.co.ke



