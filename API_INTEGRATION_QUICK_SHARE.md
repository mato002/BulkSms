# 🚀 API Integration - Quick Share Guide

## For Organizations Integrating with Prady Tech SMS API

---

## 📍 Access Documentation

**Visit our API Documentation:**
```
https://crm.pradytecai.com/api-documentation
```

Click on **"Integration Guide"** in the sidebar for complete code examples.

---

## 🔐 Your API Credentials

```
API Base URL:  https://crm.pradytecai.com/api
Client ID:     1
API Key:       bae377bc-0282-4fc9-a2a1-e338b18da77a
Sender ID:     PRADY_TECH
```

---

## ⚡ Quick Start (Copy & Paste)

### Step 1: Add to your .env file

```env
SMS_API_URL=https://crm.pradytecai.com/api
SMS_CLIENT_ID=1
SMS_API_KEY=bae377bc-0282-4fc9-a2a1-e338b18da77a
SMS_SENDER_ID=PRADY_TECH
```

### Step 2: Test with cURL

```bash
curl -X POST https://crm.pradytecai.com/api/1/messages/send \
  -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  -H "Content-Type: application/json" \
  -d '{
    "channel": "sms",
    "recipient": "254712345678",
    "body": "Hello from Prady Tech!",
    "sender": "PRADY_TECH"
  }'
```

### Step 3: Check Balance

```bash
curl -H "X-API-Key: bae377bc-0282-4fc9-a2a1-e338b18da77a" \
  https://crm.pradytecai.com/api/1/client/balance
```

---

## 📚 Complete Integration Code

Visit the documentation page and choose your language:
- **PHP**: Ready-to-use class
- **Laravel**: Service with dependency injection
- **Python**: Complete implementation
- **Node.js**: Full integration

**URL:** https://crm.pradytecai.com/api-documentation#integration-guide

---

## 💰 Pricing

- **1-160 characters** = 1 unit = KSH 1.00
- **161-320 characters** = 2 units = KSH 2.00
- **321-480 characters** = 3 units = KSH 3.00

---

## 🔒 Security Reminders

- ✅ Store API key in environment variables
- ✅ Never commit to version control
- ✅ Always use HTTPS
- ✅ Phone format: `254XXXXXXXXX`

---

## 📞 Support

**Email:** support@pradytecai.com  
**API Documentation:** https://crm.pradytecai.com/api-documentation  
**Status:** Active & Ready to Use

---

## ✅ Testing Checklist

Before going live:
- [ ] Test API health check
- [ ] Verify API key works
- [ ] Send test SMS to your number
- [ ] Confirm SMS delivered
- [ ] Check balance is deducted
- [ ] Review SMS history

---

**Last Updated:** October 19, 2025  
**Version:** 1.0.0  
**Status:** Production Ready

