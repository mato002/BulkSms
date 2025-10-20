# 🔴 **IMPORTANT: M-Pesa Sandbox vs Production**

## ⚠️ **Current Situation:**

You are using **SANDBOX credentials** - these are **TEST** credentials from Safaricom.

### **Sandbox Credentials (What you have now):**
```
Environment: sandbox
Shortcode: 174379 (Safaricom test shortcode)
Consumer Key: Yt36YTWRLf1CL3RW47GidbAXtW1OcO...
```

---

## 🚫 **Why You're Not Receiving M-Pesa Prompts:**

**Sandbox STK Push prompts DO NOT go to real phones!**

- ❌ Your phone 254728883160 will **NEVER** receive sandbox prompts
- ❌ Sandbox only works with the "M-Pesa Sandbox App" (simulator)
- ❌ Sandbox is for testing/development ONLY

---

## ✅ **To Receive Real M-Pesa Prompts:**

You need **PRODUCTION credentials** from Safaricom.

### **Step 1: Apply for Production API**

1. Go to: **https://developer.safaricom.co.ke**
2. Login/Register your account
3. Create a **PRODUCTION app** (not sandbox)
4. Fill in business details
5. Submit for approval (takes 1-3 business days)

### **Step 2: Get Your Production Credentials**

After approval, you'll receive:
- ✅ Production Consumer Key
- ✅ Production Consumer Secret
- ✅ Production Passkey
- ✅ Your Business Paybill/Till Number

### **Step 3: Configure Production in BulkSms**

Add these to your `.env` file:

```env
# M-Pesa PRODUCTION Settings
MPESA_ENV=production
MPESA_CONSUMER_KEY=YOUR_PRODUCTION_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_PRODUCTION_CONSUMER_SECRET
MPESA_PASSKEY=YOUR_PRODUCTION_PASSKEY
MPESA_SHORTCODE=YOUR_PAYBILL_OR_TILL_NUMBER
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
MPESA_CALLBACK_URL=https://crm.pradytecai.com/api/webhooks/mpesa/callback
MPESA_TIMEOUT_URL=https://crm.pradytecai.com/api/webhooks/mpesa/timeout
```

**Then clear cache:**
```bash
php artisan config:clear
```

---

## 📱 **After Setting Production:**

Once you have production credentials configured:
1. STK push prompts will go to **REAL phones**
2. Customers can pay with their M-Pesa
3. Payments will be **REAL money** (not test)
4. All transactions will be live

---

## 💡 **Current Sandbox Setup:**

To keep your current sandbox config in `.env`, add:

```env
# M-Pesa Sandbox (for testing only - does NOT work with real phones)
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=Yt36YTWRLf1CL3RW47GidbAXtW1OcO4m7U5VuvA6x84BdoQV
MPESA_CONSUMER_SECRET=p3o13LwjC48GjBGdvcnpptuQc90OSlHJvBeTwkXyJBNQFGJQnqN5gws4gf6frGdh
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_SHORTCODE=174379
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
MPESA_CALLBACK_URL=https://crm.pradytecai.com/api/webhooks/mpesa/callback
MPESA_TIMEOUT_URL=https://crm.pradytecai.com/api/webhooks/mpesa/timeout
```

---

## 📋 **Summary:**

| Feature | Sandbox | Production |
|---------|---------|------------|
| Real phone prompts | ❌ No | ✅ Yes |
| Real money | ❌ No | ✅ Yes |
| Testing | ✅ Yes | ⚠️ Live |
| Approval needed | ❌ No | ✅ Yes |
| Shortcode | 174379 (test) | Your paybill |

---

## 🎯 **What You Need to Do:**

1. **For Testing:** Keep using sandbox (won't work with real phones)
2. **For Production:** Apply for Safaricom Daraja Production API

**Contact Safaricom to get production credentials!**

📞 **Safaricom Support:**
- Website: https://developer.safaricom.co.ke
- Email: apisupport@safaricom.co.ke
- Developer Portal Support

---

**Once you have production credentials, share them with me and I'll configure the system!** 🚀



