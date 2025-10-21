# M-Pesa Production Setup - Complete Guide

## 🎯 Current Status

✅ **Your M-Pesa integration is working!**
- Authentication: SUCCESSFUL
- STK Push: SUCCESSFUL
- Code: WORKING PERFECTLY

❌ **Problem: You're in SANDBOX mode**
- Sandbox doesn't send popups to real phones (0728883160)
- You need PRODUCTION credentials to receive real popups

---

## 📋 Step-by-Step: Get Production Credentials

### Step 1: Apply for Production Access

1. **Go to Safaricom Developer Portal**
   - Visit: https://developer.safaricom.co.ke/
   - Click "Login" or "Register" if new

2. **Create Production App**
   - Go to "My Apps" → "Create App"
   - Select "Lipa Na M-Pesa Online"
   - Fill in your business details:
     - App Name: BulkSMS Platform (or your business name)
     - Description: SMS Platform Payment System
     - Environment: **Production**

3. **Submit Business Documents**
   - Business registration certificate
   - KRA PIN certificate
   - Director/Owner ID
   - Paybill/Till Number details

4. **Wait for Approval**
   - Takes 1-2 weeks typically
   - You'll receive email confirmation
   - Production credentials will be available in your app

---

## 🔧 Step 2: Update Your Configuration

Once you have production credentials, update your `.env` file:

### Current Configuration (Sandbox):
```env
MPESA_ENV=sandbox
MPESA_CONSUMER_KEY=Yt36YTWRLf1CL3RW47GidbAXtW1OcO4m7U5VuvA6x84BdoQV
MPESA_CONSUMER_SECRET=p3o13LwjC48GjBGdvcnpptuQc90OSlHJvBeTwkXyJBNQFGJQnqN5gws4gf6frGdh
MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_SHORTCODE=174379
```

### New Configuration (Production):
```env
MPESA_ENV=production
MPESA_CONSUMER_KEY=YOUR_PRODUCTION_CONSUMER_KEY
MPESA_CONSUMER_SECRET=YOUR_PRODUCTION_CONSUMER_SECRET
MPESA_PASSKEY=YOUR_PRODUCTION_PASSKEY
MPESA_SHORTCODE=YOUR_PAYBILL_NUMBER
MPESA_TRANSACTION_TYPE=CustomerPayBillOnline
MPESA_CALLBACK_URL=https://crm.pradytecai.com/api/webhooks/mpesa/callback
MPESA_TIMEOUT_URL=https://crm.pradytecai.com/api/webhooks/mpesa/timeout
```

### Where to Get Production Credentials:
1. **Consumer Key & Secret**: From your Production App on developer.safaricom.co.ke
2. **Passkey**: Provided by Safaricom when your app is approved
3. **Shortcode**: Your Paybill or Till Number
4. **Callback URLs**: Keep as shown above (must be HTTPS and publicly accessible)

---

## ✅ Step 3: Test Production Setup

After updating to production credentials:

```bash
# Test the configuration
php test_mpesa_detailed.php
```

You should see:
- ✅ Environment: production
- ✅ Authentication successful
- ✅ STK Push sent
- 📱 **POPUP RECEIVED ON YOUR PHONE!**

---

## 🚀 Quick Test in Browser

1. Login to your platform: https://crm.pradytecai.com
2. Go to Wallet → Top Up
3. Select M-Pesa
4. Enter amount: KES 100
5. Enter phone: 728883160
6. Click "Proceed with M-Pesa"
7. **You should receive the popup within 10-30 seconds!**

---

## ⚠️ Important: Callback URLs

Your callback URLs **MUST**:
1. Be **HTTPS** (not HTTP)
2. Be **publicly accessible** (not localhost)
3. Return proper responses

### Test Your Callbacks:

```bash
# Test if callbacks are accessible
curl -X POST https://crm.pradytecai.com/api/webhooks/mpesa/callback -H "Content-Type: application/json" -d '{"test":"data"}'
```

If you get errors:
1. Check your firewall settings on Hostinger
2. Ensure SSL certificate is valid
3. Check route is registered in `routes/api.php`

---

## 🔄 Alternative: While Waiting for Production Approval

### Option 1: Manual Payment System

Users can pay manually and you credit them:

1. **Customer pays via M-Pesa**:
   - Paybill: YOUR_PAYBILL_NUMBER
   - Account: Their phone number or unique ID
   - Amount: What they want to top up

2. **You verify payment**:
   - Check M-Pesa statement
   - Verify transaction

3. **Credit their account manually**:
   ```bash
   php artisan tinker
   >>> $user = App\Models\Client::where('contact', '254728883160')->first();
   >>> $user->addBalance(1000); // Add KES 1000
   >>> echo "Balance updated!";
   ```

### Option 2: Bank Transfer

Accept bank transfers and update balance manually:
1. Customer transfers to your bank
2. Sends proof via email/WhatsApp
3. You verify and credit their account

### Option 3: Manual M-Pesa Payments in System

Your system already has manual payment option:
1. User submits top-up request with "Manual Payment"
2. You get notification
3. Customer pays via phone/paybill
4. You approve and credit in admin panel

---

## 🛠️ Troubleshooting Production Issues

### Issue 1: "Invalid Credentials"
**Solution**: Double-check all credentials in .env file
- Copy-paste directly from Safaricom portal
- No extra spaces or quotes
- Run: `php artisan config:clear`

### Issue 2: "Request Rejected"
**Solution**: 
- Ensure your Paybill is active and approved for STK Push
- Contact Safaricom to enable STK Push on your shortcode
- Some paybills need manual activation

### Issue 3: "Callback Not Received"
**Solution**:
- Check callback URLs are HTTPS and accessible
- Test with: `curl https://crm.pradytecai.com/api/webhooks/mpesa/callback`
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Ensure no firewall blocking Safaricom IPs

### Issue 4: "User Cancelled"
**Solution**: This is normal - user cancelled the prompt
- Check Result Code: 1032 = User cancelled
- Check Result Code: 0 = Success
- Check Result Code: 1 = Insufficient funds

---

## 📊 Monitoring & Logs

### Check M-Pesa Logs:
```bash
tail -f storage/logs/laravel.log | grep -i mpesa
```

### Check Recent Transactions:
```bash
php artisan tinker
>>> App\Models\WalletTransaction::latest()->take(10)->get();
```

### Test Callbacks Manually:
```bash
php artisan tinker
>>> $mpesa = new App\Services\MpesaService();
>>> $mpesa->handleCallback([...test data...]);
```

---

## 📝 Important Notes

1. **Sandbox vs Production**:
   - Sandbox = Testing only, no real popups
   - Production = Real transactions, real popups

2. **Transaction Costs**:
   - Check Safaricom's pricing for STK Push
   - Usually KES 2-5 per transaction
   - Factor this into your pricing

3. **Testing in Production**:
   - Start with small amounts (KES 10-50)
   - Use your own phone number first
   - Verify callbacks are working

4. **Go-Live Checklist**:
   - [ ] Production credentials from Safaricom
   - [ ] Updated .env file with production settings
   - [ ] Cleared config cache (`php artisan config:clear`)
   - [ ] Tested with small amount
   - [ ] Verified callback is working
   - [ ] Checked balance updates correctly
   - [ ] SSL certificate is valid
   - [ ] Callback URLs are accessible

---

## 🎓 How M-Pesa STK Push Works

1. **User initiates top-up** on your website
2. **Your system sends request** to M-Pesa API
3. **M-Pesa sends popup** to user's phone
4. **User enters PIN** and confirms
5. **M-Pesa processes payment**
6. **M-Pesa sends callback** to your system
7. **Your system updates balance** automatically
8. **User sees updated balance**

All of this happens automatically once you're in production mode!

---

## 🆘 Need Help?

### Safaricom Support:
- Email: apisupport@safaricom.co.ke
- Phone: 0711 051 222
- Portal: https://developer.safaricom.co.ke/support

### Your Platform Logs:
```bash
# View detailed M-Pesa logs
cat storage/logs/laravel.log | grep -i mpesa

# Monitor live
tail -f storage/logs/laravel.log
```

---

## ✅ Summary

**Current Status**: ✅ Integration WORKING, but in sandbox mode

**What You Need**: Production credentials from Safaricom

**Timeline**: 1-2 weeks for approval

**Once Approved**: Update 5 lines in .env file and you're LIVE!

**Result**: Real M-Pesa popups on real phones! 🎉

---

**Last Updated**: October 18, 2025
**Tested On**: BulkSMS Platform v1.0
**Status**: Ready for Production Switch




