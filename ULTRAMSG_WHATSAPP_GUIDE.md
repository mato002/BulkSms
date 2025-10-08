# 📱 WhatsApp Integration with UltraMsg - Complete Guide

**Quick, Easy, and Powerful WhatsApp Integration** 🚀

## 🎉 Why UltraMsg?

### ✅ Advantages
- **5-Minute Setup** - No Meta Business verification needed
- **Use Your Number** - Works with your existing WhatsApp
- **No Template Approvals** - Send any message instantly
- **Free Tier Available** - Start for free
- **Instant Messaging** - No waiting periods
- **Simple API** - Easy to integrate

### 📌 Perfect For
- Quick WhatsApp integration
- MVP and prototypes
- Testing WhatsApp features
- Small to medium businesses
- Flexible messaging needs

---

## 🚀 Quick Start (5 Minutes!)

### Step 1: Create UltraMsg Account
1. Go to [ultramsg.com](https://ultramsg.com)
2. Click **Sign Up** (or Login)
3. Create your account

### Step 2: Create Instance
1. In your dashboard, click **Create Instance**
2. Choose a name for your instance
3. Select a plan (Free tier available!)
4. Click **Create**

### Step 3: Connect WhatsApp
1. You'll see a **QR Code**
2. Open WhatsApp on your phone
3. Go to **Linked Devices** → **Link a Device**
4. Scan the QR code
5. ✅ **Connected!**

### Step 4: Get Credentials
1. In UltraMsg dashboard, go to your instance
2. Copy your **Instance ID** (e.g., `instance12345`)
3. Copy your **API Token**

### Step 5: Configure in Application
1. Navigate to `/whatsapp` in your Laravel app
2. Click **"Quick Setup (UltraMsg)"**
3. Paste your **Instance ID** and **Token**
4. Click **Save Configuration**
5. Click **Test Connection** ✅
6. **Done!** Start sending messages! 🎉

---

## 💬 Sending Messages

### Send Text Message

```php
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

$dispatcher = app(MessageDispatcher::class);

$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Hello from UltraMsg WhatsApp! 👋'
);

$dispatcher->dispatch($message);
```

### Send Image with Caption

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Check out our new product!',
    metadata: [
        'media_type' => 'image',
        'media_url' => 'https://example.com/product.jpg'
    ]
);

$dispatcher->dispatch($message);
```

### Send Video

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Watch this amazing video!',
    metadata: [
        'media_type' => 'video',
        'media_url' => 'https://example.com/video.mp4'
    ]
);

$dispatcher->dispatch($message);
```

### Send Document

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Your invoice is attached',
    metadata: [
        'media_type' => 'document',
        'media_url' => 'https://example.com/invoice.pdf',
        'filename' => 'Invoice_2025.pdf'
    ]
);

$dispatcher->dispatch($message);
```

### Send Audio/Voice

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    metadata: [
        'media_type' => 'audio',
        'media_url' => 'https://example.com/audio.mp3'
    ]
);

$dispatcher->dispatch($message);
```

### Send Location

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Visit our office here!',
    metadata: [
        'media_type' => 'location',
        'latitude' => '-1.286389',
        'longitude' => '36.817223',
        'address' => 'Nairobi, Kenya'
    ]
);

$dispatcher->dispatch($message);
```

### Send Contact Card

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    metadata: [
        'media_type' => 'contact',
        'contact_data' => '+254700000000'
    ]
);

$dispatcher->dispatch($message);
```

### Send Button Message (Formatted as Text)

UltraMsg doesn't support native buttons, but we format them nicely:

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Would you like to proceed with your order?',
    metadata: [
        'interactive_type' => 'button',
        'header' => 'Order Confirmation',
        'footer' => 'Reply with number',
        'buttons' => [
            ['reply' => ['id' => '1', 'title' => 'Yes, Proceed']],
            ['reply' => ['id' => '2', 'title' => 'Cancel Order']],
            ['reply' => ['id' => '3', 'title' => 'Contact Support']]
        ]
    ]
);
```

**Result:**
```
*Order Confirmation*

Would you like to proceed with your order?

📌 *Options:*
1. Yes, Proceed
2. Cancel Order
3. Contact Support

_Reply with number_
```

### Send List Message (Formatted as Text)

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Choose a department to contact:',
    metadata: [
        'interactive_type' => 'list',
        'header' => 'Customer Support',
        'sections' => [
            [
                'title' => 'Departments',
                'rows' => [
                    ['title' => 'Sales', 'description' => 'Product inquiries'],
                    ['title' => 'Support', 'description' => 'Technical help'],
                    ['title' => 'Billing', 'description' => 'Payment issues']
                ]
            ]
        ]
    ]
);
```

---

## 📥 Receiving Messages

### Enable Webhooks

1. Go to UltraMsg Dashboard
2. Navigate to **Settings** → **Webhooks**
3. Set **Webhook URL** to:
   ```
   https://yourdomain.com/webhook/whatsapp
   ```
4. Select events to receive:
   - ✅ `message` - New messages
   - ✅ `message.ack` - Status updates
5. Save webhook configuration

### Incoming Messages

All incoming messages are automatically:
- ✅ Stored in `messages` table
- ✅ Contact auto-created in `contacts` table
- ✅ Conversation auto-created in `conversations` table
- ✅ Visible in your Inbox (`/inbox`)

### Supported Message Types

UltraMsg webhooks support:
- ✅ Text messages
- ✅ Images (with captions)
- ✅ Videos (with captions)
- ✅ Audio/Voice messages
- ✅ Documents
- ✅ Location sharing
- ✅ Contact cards (vCard)
- ✅ Stickers

---

## 🎯 Best Practices

### 1. Phone Number Format
- Always include country code
- Can use with or without `+`
- Examples:
  - ✅ `+254712345678`
  - ✅ `254712345678`
  - ✅ `254 712 345 678`

### 2. Message Delivery
- UltraMsg delivers messages instantly (no 24-hour window)
- Can send promotional content anytime
- No template approvals needed
- Rate limits apply based on your plan

### 3. Media Files
- **Images**: JPG, PNG (max 5MB)
- **Videos**: MP4, 3GP (max 16MB)
- **Audio**: MP3, OGG, AAC (max 16MB)
- **Documents**: PDF, DOC, XLS, etc. (max 100MB)
- Use publicly accessible URLs

### 4. Connection Stability
- Keep WhatsApp instance connected
- If disconnected, scan QR code again
- Set up webhook notifications for disconnection

### 5. Message Templates
- No pre-approval needed ✅
- Can send any message format
- Use variables dynamically
- Great for transactional messages

---

## 📊 Tracking & Status

### Message Status Flow

1. **sending** → Message being processed
2. **sent** → Delivered to WhatsApp (ack: 1)
3. **delivered** → Delivered to recipient device (ack: 2)
4. **read** → Read by recipient (ack: 3)
5. **failed** → Delivery failed

### Check Message Status

```php
$message = Message::where('provider_message_id', 'msg_id')->first();

echo $message->status; // sent, delivered, read, failed
echo $message->sent_at;
echo $message->delivered_at;
echo $message->read_at;
```

---

## 🔧 Advanced Features

### Bulk Messaging

```php
$contacts = Contact::where('client_id', $clientId)->get();

foreach ($contacts as $contact) {
    $message = new OutboundMessage(
        clientId: $clientId,
        channel: 'whatsapp',
        recipient: $contact->phone,
        body: "Hi {$contact->name}, special offer just for you!"
    );
    
    $dispatcher->dispatch($message);
    
    // Add delay to avoid rate limits
    usleep(500000); // 0.5 seconds
}
```

### Message with Custom Reference

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: '+254712345678',
    body: 'Your order #12345 is ready!',
    metadata: [
        'reference_id' => 'ORDER_12345',
        'priority' => 1
    ]
);
```

### Group Messages (if supported by your plan)

```php
$message = new OutboundMessage(
    clientId: auth()->user()->client_id,
    channel: 'whatsapp',
    recipient: 'group-id@g.us', // Group chat ID
    body: 'Hello everyone! 👋'
);
```

---

## 🐛 Troubleshooting

### Connection Test Fails

**Problem**: "Connection failed" when testing

**Solutions**:
1. Verify Instance ID is correct
2. Check API Token is valid
3. Ensure instance is connected (scan QR)
4. Check instance status in UltraMsg dashboard
5. Try reconnecting WhatsApp

### Messages Not Sending

**Problem**: Messages fail to send

**Solutions**:
1. Check recipient number format
2. Verify WhatsApp instance is active
3. Check your UltraMsg balance/quota
4. Ensure recipient hasn't blocked you
5. Review UltraMsg logs for errors

### Webhooks Not Working

**Problem**: Not receiving inbound messages

**Solutions**:
1. Verify webhook URL is publicly accessible
2. Check webhook is configured in UltraMsg
3. Ensure HTTPS is enabled (required)
4. Test webhook URL manually
5. Review Laravel logs for errors
6. Check webhook events are enabled

### Instance Disconnected

**Problem**: WhatsApp instance shows disconnected

**Solutions**:
1. Go to UltraMsg dashboard
2. Scan new QR code
3. Keep WhatsApp phone connected to internet
4. Don't logout from WhatsApp on phone

---

## 💰 Pricing & Plans

UltraMsg offers flexible pricing:

### Free Tier
- ✅ Great for testing
- Limited messages per day
- Perfect for development

### Paid Plans
- 💬 Unlimited messaging
- 📊 Advanced features
- 🚀 Higher rate limits
- 📞 Priority support

[View Pricing](https://ultramsg.com/pricing)

---

## 🔐 Security Best Practices

### 1. Protect Credentials
```env
# .env file
ULTRAMSG_INSTANCE_ID=your_instance_id
ULTRAMSG_TOKEN=your_token_here
```

Never commit credentials to version control!

### 2. Webhook Security
- Use custom webhook token
- Validate incoming requests
- Use HTTPS only
- Log all webhook events

### 3. Rate Limiting
- Implement delays between messages
- Monitor API usage
- Handle rate limit errors gracefully

---

## 📚 API Reference

### UltraMsg Endpoints Used

**Send Message**
```
POST https://api.ultramsg.com/{instance_id}/messages/chat
```

**Send Image**
```
POST https://api.ultramsg.com/{instance_id}/messages/image
```

**Send Document**
```
POST https://api.ultramsg.com/{instance_id}/messages/document
```

**Check Instance Status**
```
GET https://api.ultramsg.com/{instance_id}/instance/status
```

### Response Format

```json
{
  "sent": "true",
  "message": "Message sent successfully",
  "id": "message_id_12345"
}
```

---

## 🆚 UltraMsg vs WhatsApp Cloud API

| Feature | UltraMsg | WhatsApp Cloud API |
|---------|----------|-------------------|
| Setup Time | 5 minutes | Days/Weeks |
| Business Verification | ❌ Not required | ✅ Required |
| Template Approval | ❌ Not needed | ✅ Required |
| Use Personal Number | ✅ Yes | ❌ No |
| Free Tier | ✅ Yes | ✅ Yes |
| Official WhatsApp | ❌ Third-party | ✅ Official |
| Message Flexibility | ✅ Any message | ⚠️ Template-based |
| Best For | Quick setup, testing | Enterprise, scale |

---

## ✨ What's Implemented

Your Laravel app now has:

✅ **Complete UltraMsg Integration**
- Text, image, video, audio, document messages
- Location and contact sharing
- Interactive formatted messages
- Webhook support for incoming messages

✅ **User Interface**
- WhatsApp dashboard (`/whatsapp`)
- Simple configuration page
- Test message sending
- Connection testing

✅ **Automatic Processing**
- Incoming messages saved
- Contacts auto-created
- Conversations managed
- Status tracking

---

## 🚀 Next Steps

### Immediate
1. ✅ Configure UltraMsg credentials
2. ✅ Test connection
3. ✅ Send test message
4. ✅ Set up webhooks

### Enhancements
- **Auto-Responders**: Reply to keywords automatically
- **Quick Replies**: Save frequently used messages
- **Scheduled Messages**: Schedule messages for later
- **Broadcast Lists**: Organized group messaging
- **Analytics**: Track message performance

---

## 🆘 Support Resources

- **UltraMsg Docs**: [docs.ultramsg.com](https://docs.ultramsg.com)
- **API Reference**: [docs.ultramsg.com/api](https://docs.ultramsg.com/api)
- **Support**: Contact UltraMsg support
- **Community**: UltraMsg user forums

---

## 📝 Environment Variables

Add to your `.env`:

```env
# UltraMsg WhatsApp Configuration
ULTRAMSG_INSTANCE_ID=instance12345
ULTRAMSG_TOKEN=your_ultramsg_token_here
ULTRAMSG_WEBHOOK_TOKEN=your_custom_webhook_token
```

---

## 🎉 You're Ready!

**Congratulations!** Your WhatsApp integration with UltraMsg is complete and ready to use.

### Quick Checklist
- [x] ✅ UltraMsg account created
- [x] ✅ Instance connected
- [x] ✅ Credentials configured
- [x] ✅ Connection tested
- [x] ✅ Webhooks set up
- [x] ✅ Test message sent

**Start sending WhatsApp messages now!** 📱💚

---

## 💡 Pro Tips

1. **Test First** - Always test with your own number first
2. **Respect Users** - Don't spam, respect opt-outs
3. **Monitor Status** - Keep an eye on message delivery
4. **Use Templates** - Create message templates for consistency
5. **Handle Errors** - Always handle API errors gracefully
6. **Stay Connected** - Keep WhatsApp instance active

**Happy messaging! 🚀**

