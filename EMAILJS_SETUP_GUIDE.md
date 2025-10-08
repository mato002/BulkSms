# EmailJS Setup Guide for Password Reset

## 🚀 Quick Setup (5 minutes)

### Step 1: Create EmailJS Account
1. Go to [https://www.emailjs.com/](https://www.emailjs.com/)
2. Sign up for a free account
3. Verify your email

### Step 2: Create Email Service
1. In EmailJS dashboard, go to **"Email Services"**
2. Click **"Add New Service"**
3. Choose your email provider:
   - **Gmail** (recommended for testing)
   - **Outlook**
   - **Yahoo**
   - Or any SMTP service

### Step 3: Create Email Template
1. Go to **"Email Templates"**
2. Click **"Create New Template"**
3. Use this template:

```html
Subject: Password Reset Request - Bulk SMS CRM

Hello,

A password reset has been requested for your Bulk SMS CRM account.

Email: {{to_email}}

Please contact your system administrator to reset your password.

Contact Information:
- Email: admin@bulksmscrm.com
- Phone: +1 (555) 123-4567

Best regards,
Bulk SMS CRM Team
```

### Step 4: Get Your Credentials
After creating service and template, you'll get:
- **Service ID** (e.g., `service_abc123`)
- **Template ID** (e.g., `template_xyz789`)
- **Public Key** (e.g., `user_abcdef123456`)

### Step 5: Update Your Code
Replace these values in `resources/views/auth/forgot-password.blade.php`:

```javascript
// Line 407: Replace with your public key
emailjs.init("YOUR_EMAILJS_PUBLIC_KEY");

// Line 442: Replace with your service and template IDs
emailjs.send('YOUR_SERVICE_ID', 'YOUR_TEMPLATE_ID', templateParams)
```

## 📧 Example Configuration

```javascript
// Your actual values would look like this:
emailjs.init("user_abc123def456");

emailjs.send('service_gmail123', 'template_reset456', templateParams)
```

## 🧪 Testing

1. Visit: `http://127.0.0.1:8000/forgot-password`
2. Enter any email address
3. Click "Send Reset Link"
4. Check the email inbox
5. User will see the manual reset page

## 💡 Features Included

✅ **Client-side email sending** - No server configuration needed
✅ **Professional email template** - Branded with your company info
✅ **Success/error handling** - User feedback on email status
✅ **Loading states** - Button shows "Sending..." during process
✅ **Manual reset page** - Directs users to contact admin
✅ **Responsive design** - Works on all devices

## 🔧 Troubleshooting

### Email not sending?
1. Check browser console for errors
2. Verify EmailJS credentials are correct
3. Make sure email service is active in EmailJS dashboard
4. Check if email provider blocks automated emails

### Template not working?
1. Verify template ID matches exactly
2. Check template variables ({{to_email}}, etc.)
3. Test template in EmailJS dashboard first

### Still having issues?
1. Use EmailJS test feature in dashboard
2. Check EmailJS documentation
3. Try with a different email provider

## 🎯 Benefits

- **No server setup** required
- **Free tier** available (200 emails/month)
- **Works immediately** after configuration
- **Professional appearance** 
- **Admin-friendly** workflow

## 📝 Next Steps

After setup:
1. Test the forgot password flow
2. Customize the email template with your branding
3. Update contact information in manual reset page
4. Consider upgrading EmailJS plan for production use

---

**Ready to go!** Your password reset system will work without any server-side email configuration! 🚀

