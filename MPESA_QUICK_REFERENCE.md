# M-Pesa Quick Reference Card

## 🎯 Current Status
```
✅ M-Pesa Integration: WORKING
❌ Real Phone Popups: NOT AVAILABLE (Sandbox Mode)
🔄 Solution: Get Production Credentials
```

---

## 🚀 Quick Commands

### Test M-Pesa Configuration
```bash
php test_mpesa_detailed.php
```

### Credit User Manually
```bash
php manual_credit_user.php
```

### Check Configuration
```bash
php check_mpesa_config.php
```

### Clear Config Cache (After .env Changes)
```bash
php artisan config:clear
```

### View M-Pesa Logs
```bash
tail -f storage/logs/laravel.log | grep -i mpesa
```

---

## 📝 Production Credentials Needed

Update these 5 lines in `.env`:
```env
MPESA_ENV=production
MPESA_CONSUMER_KEY=your_production_key
MPESA_CONSUMER_SECRET=your_production_secret
MPESA_PASSKEY=your_production_passkey
MPESA_SHORTCODE=your_paybill_number
```

Get credentials from: https://developer.safaricom.co.ke/

---

## ⚡ Manual Credit (While Waiting)

### Option 1: Use Script
```bash
php manual_credit_user.php
# Follow prompts
```

### Option 2: Use Tinker
```bash
php artisan tinker
>>> $user = App\Models\Client::where('contact', '254728883160')->first();
>>> $user->addBalance(1000);  # Credit KES 1000
>>> exit
```

---

## 🔍 Troubleshooting

| Issue | Solution |
|-------|----------|
| No popup received | Normal in sandbox - need production |
| Authentication failed | Check CONSUMER_KEY and CONSUMER_SECRET |
| Config not updating | Run `php artisan config:clear` |
| Callback not received | Check HTTPS, URL accessibility |

---

## 📞 Support Contacts

**Safaricom M-Pesa:**
- Email: apisupport@safaricom.co.ke  
- Phone: 0711 051 222
- Portal: https://developer.safaricom.co.ke/

---

## ✅ Pre-Launch Checklist

Before going live with production:
- [ ] Got production credentials
- [ ] Updated .env file
- [ ] Ran `php artisan config:clear`
- [ ] Tested with `php test_mpesa_detailed.php`
- [ ] Tested with small amount (KES 10)
- [ ] Verified callback working
- [ ] Checked balance updates correctly
- [ ] SSL certificate valid
- [ ] Monitored logs for errors

---

**Quick Start:** Apply at https://developer.safaricom.co.ke/ → Wait 1-2 weeks → Update .env → Go Live! 🚀




