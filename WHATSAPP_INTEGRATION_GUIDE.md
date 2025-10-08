# 📱 WhatsApp Cloud API Integration Guide

Complete guide for setting up and using WhatsApp Business Cloud API with your Laravel Bulk SMS application.

## 🚀 Features Implemented

✅ **Text Messages** - Send plain text WhatsApp messages  
✅ **Template Messages** - Use approved WhatsApp templates  
✅ **Interactive Messages** - Send buttons and list messages  
✅ **Media Support** - Send images, videos, documents, audio  
✅ **Inbound Messages** - Receive messages from customers  
✅ **Status Updates** - Track sent, delivered, and read status  
✅ **Template Sync** - Automatically sync templates from Meta  
✅ **Webhook Integration** - Real-time message delivery  

---

## 📋 Prerequisites

Before you begin, you need:

1. **Meta Business Account** - [Create one here](https://business.facebook.com/)
2. **WhatsApp Business App** - Set up in Meta for Developers
3. **Verified Business Number** - A phone number for your WhatsApp Business
4. **System User Token** - For permanent API access

---

## 🔧 Setup Instructions

### Step 1: Create WhatsApp Business App

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Click **My Apps** → **Create App**
3. Select **Business** as app type
4. Fill in your app details
5. Click **Add Product** → Select **WhatsApp**

### Step 2: Get Your Credentials

#### Phone Number ID
1. In your WhatsApp app dashboard
2. Go to **WhatsApp** → **Getting Started**
3. Copy the **Phone Number ID** (15-digit number)

#### Access Token (Permanent)
1. Go to **System Users** in Business Settings
2. Create a new System User (or use existing)
3. Assign the WhatsApp app to this user
4. Generate a **permanent token** with these permissions:
   - `whatsapp_business_management`
   - `whatsapp_business_messaging`
5. Copy and save the token securely

#### Business Account ID
1. In WhatsApp dashboard, go to **Settings**
2. Copy the **WhatsApp Business Account ID**

### Step 3: Configure in Your Application

1. Navigate to **WhatsApp** section in your dashboard
2. Click **Configure WhatsApp**
3. Enter your credentials:
   - **Phone Number ID**: Your 15-digit phone number ID
   - **Access Token**: Your permanent system user token
   - **Business Account ID**: Your WhatsApp Business Account ID
   - **Webhook Verify Token**: Create a secure random string
4. Click **Save Configuration**

### Step 4: Set Up Webhooks

1. In your WhatsApp App settings, go to **Configuration**
2. Click **Edit** in Webhook section
3. Set **Callback URL** to:
   ```
   https://yourdomain.com/webhook/whatsapp
   ```
4. Set **Verify Token** to the same token you configured in Step 3
5. Subscribe to these webhook fields:
   - ✅ messages
   - ✅ message_status (optional, for delivery tracking)
6. Click **Verify and Save**

### Step 5: Test Your Integration

1. Go back to **WhatsApp** dashboard
2. Click **Test Connection** - should show ✅ Connected
3. Try sending a test message
4. Verify message appears in WhatsApp

---

## 💬 Sending Messages

### Send Text Message

```php
use App\Services\Messaging\DTO\OutboundMessage;
use App\Services\Messaging\MessageDispatcher;

$dispatcher = app(MessageDispatcher::class);

$message = new OutboundMessage(
    clientId: $client->id,
    channel: 'whatsapp',
    recipient: '254712345678',  // Without + sign
    body: 'Hello from WhatsApp!'
);

$result = $dispatcher->dispatch($message);
```

### Send Template Message

```php
$message = new OutboundMessage(
    clientId: $client->id,
    channel: 'whatsapp',
    recipient: '254712345678',
    body: 'Your order {{1}} is ready',
    metadata: [
        'template_name' => 'order_ready',
        'language_code' => 'en',
        'template_components' => [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => '#12345']
                ]
            ]
        ]
    ]
);

$result = $dispatcher->dispatch($message);
```

### Send Interactive Button Message

```php
$message = new OutboundMessage(
    clientId: $client->id,
    channel: 'whatsapp',
    recipient: '254712345678',
    body: 'Would you like to proceed?',
    metadata: [
        'interactive_type' => 'button',
        'header' => 'Confirmation Required',
        'footer' => 'Reply within 24 hours',
        'buttons' => [
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'btn_yes',
                    'title' => 'Yes, Proceed'
                ]
            ],
            [
                'type' => 'reply',
                'reply' => [
                    'id' => 'btn_no',
                    'title' => 'No, Cancel'
                ]
            ]
        ]
    ]
);

$result = $dispatcher->dispatch($message);
```

### Send List Message

```php
$message = new OutboundMessage(
    clientId: $client->id,
    channel: 'whatsapp',
    recipient: '254712345678',
    body: 'Please select a department:',
    metadata: [
        'interactive_type' => 'list',
        'header' => 'Contact Us',
        'action_button' => 'Select Department',
        'sections' => [
            [
                'title' => 'Departments',
                'rows' => [
                    [
                        'id' => 'dept_sales',
                        'title' => 'Sales',
                        'description' => 'Product inquiries'
                    ],
                    [
                        'id' => 'dept_support',
                        'title' => 'Support',
                        'description' => 'Technical help'
                    ]
                ]
            ]
        ]
    ]
);

$result = $dispatcher->dispatch($message);
```

### Send Media Message (Image)

```php
$message = new OutboundMessage(
    clientId: $client->id,
    channel: 'whatsapp',
    recipient: '254712345678',
    body: 'Check out our new product!',  // Caption
    metadata: [
        'media_type' => 'image',
        'media_url' => 'https://example.com/product.jpg',
        // OR use media_id after uploading to WhatsApp
        // 'media_id' => '123456789'
    ]
);

$result = $dispatcher->dispatch($message);
```

---

## 📥 Receiving Messages

Incoming messages are automatically processed by the webhook and stored in your database.

### Access Inbound Messages

1. **Via Inbox**: Navigate to **Inbox** to see all conversations
2. **Via API**: Query the `messages` table where `direction = 'inbound'`

### Message Types Supported

- ✅ Text messages
- ✅ Images (with captions)
- ✅ Videos (with captions)
- ✅ Audio messages
- ✅ Documents
- ✅ Location
- ✅ Contact cards
- ✅ Button replies
- ✅ List replies

---

## 📋 Template Management

### Sync Templates from WhatsApp

1. Create templates in [WhatsApp Manager](https://business.facebook.com/wa/manage/message-templates/)
2. Wait for Meta approval (usually 24-48 hours)
3. In your dashboard, go to **WhatsApp**
4. Click **Sync Templates from WhatsApp**
5. Approved templates will appear in the list

### Template Requirements

- Must be pre-approved by Meta
- Can include variables like `{{1}}`, `{{2}}`
- Categories: Marketing, Utility, Authentication
- Follow WhatsApp's [template guidelines](https://developers.facebook.com/docs/whatsapp/message-templates/guidelines)

---

## 🔔 Webhook Events

The webhook receives these events:

### Message Received
```json
{
  "entry": [{
    "changes": [{
      "field": "messages",
      "value": {
        "messages": [{
          "from": "254712345678",
          "id": "wamid.xxx",
          "timestamp": "1234567890",
          "type": "text",
          "text": {
            "body": "Hello"
          }
        }]
      }
    }]
  }]
}
```

### Status Update
```json
{
  "entry": [{
    "changes": [{
      "field": "messages",
      "value": {
        "statuses": [{
          "id": "wamid.xxx",
          "status": "delivered",
          "timestamp": "1234567890"
        }]
      }
    }]
  }]
}
```

### Handled Status Types

- `sent` - Message sent to WhatsApp
- `delivered` - Message delivered to recipient
- `read` - Message read by recipient
- `failed` - Message delivery failed

---

## 🎯 Best Practices

### 1. Message Templates
- Always use approved templates for marketing messages
- Test templates before large campaigns
- Keep variable placeholders consistent

### 2. Rate Limits
- Start with low volume, gradually increase
- WhatsApp has tier-based messaging limits
- Monitor your quality rating in WhatsApp Manager

### 3. 24-Hour Window
- Free-form messages only within 24-hour window
- After 24 hours, must use approved templates
- Interactive messages count as free-form

### 4. Phone Number Format
- Always include country code
- Remove `+` sign when sending via API
- Example: `254712345678` (not `+254712345678`)

### 5. Media Files
- Maximum size: 16MB for most media
- Supported formats:
  - Images: JPEG, PNG
  - Videos: MP4, 3GPP
  - Audio: AAC, MP3, OGG
  - Documents: PDF, DOC, XLSX, etc.

---

## 🐛 Troubleshooting

### Connection Test Fails

**Problem**: "Connection failed" when testing  
**Solution**:
- Verify Phone Number ID is correct
- Check Access Token has required permissions
- Ensure token hasn't expired
- Verify API version is supported

### Messages Not Sending

**Problem**: Messages fail with error  
**Solution**:
- Check recipient number format (no + sign)
- Verify WhatsApp Business number is active
- Check template is approved (for template messages)
- Review message content for policy violations

### Webhooks Not Working

**Problem**: Not receiving inbound messages  
**Solution**:
- Verify webhook URL is publicly accessible (no localhost)
- Check verify token matches configuration
- Ensure HTTPS is enabled (required by Meta)
- Check webhook subscriptions in Meta dashboard
- Review Laravel logs for errors

### Template Sync Issues

**Problem**: Templates not syncing  
**Solution**:
- Verify Business Account ID is configured
- Check templates are approved in WhatsApp Manager
- Ensure access token has `whatsapp_business_management` permission
- Wait a few minutes after template approval

---

## 📊 Message Status Tracking

Track message delivery in the `messages` table:

```sql
SELECT 
    id,
    recipient,
    body,
    status,
    sent_at,
    delivered_at,
    read_at
FROM messages
WHERE channel = 'whatsapp'
ORDER BY created_at DESC;
```

### Status Flow

1. **sending** → Message being sent
2. **sent** → Delivered to WhatsApp
3. **delivered** → Delivered to recipient device
4. **read** → Read by recipient
5. **failed** → Delivery failed

---

## 🔐 Security Considerations

1. **Token Security**
   - Store access tokens securely (encrypted in database)
   - Never commit tokens to version control
   - Rotate tokens periodically

2. **Webhook Verification**
   - Always verify webhook requests
   - Use secure verify tokens
   - Validate request signatures (if implemented)

3. **Data Privacy**
   - Comply with WhatsApp's Business Policy
   - Implement proper consent mechanisms
   - Handle user data according to GDPR/local laws

---

## 📚 API Reference

### Controllers

- `WhatsAppController` - Main WhatsApp management
- `WhatsAppWebhookController` - Webhook handling

### Routes

```php
// Management
GET  /whatsapp                    - Dashboard
GET  /whatsapp/configure          - Configuration form
POST /whatsapp/configure          - Save configuration
POST /whatsapp/test-connection    - Test API connection
POST /whatsapp/send               - Send message
POST /whatsapp/send-interactive   - Send interactive message
POST /whatsapp/upload-media       - Upload media
POST /whatsapp/fetch-templates    - Sync templates

// Webhooks (public)
GET  /webhook/whatsapp            - Webhook verification
POST /webhook/whatsapp            - Webhook handler
```

### Models

- `Channel` - Store WhatsApp configuration
- `Template` - WhatsApp message templates
- `Message` - All messages (inbound/outbound)
- `Conversation` - Customer conversations

---

## 🆘 Support Resources

- [WhatsApp Cloud API Docs](https://developers.facebook.com/docs/whatsapp/cloud-api)
- [Message Templates Guide](https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates)
- [Interactive Messages](https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages#interactive-messages)
- [Webhook Setup](https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks)
- [WhatsApp Manager](https://business.facebook.com/wa/manage/)

---

## ✨ What's Next?

Consider implementing:

- **Media Download** - Download media from received messages
- **Catalog Messages** - Send product catalogs
- **Quick Replies** - Saved response templates
- **Auto-Responders** - Automatic replies based on keywords
- **Analytics Dashboard** - WhatsApp-specific analytics
- **Bulk Messaging** - Campaign support for WhatsApp

---

## 📝 Environment Variables

Add these to your `.env` file:

```env
# WhatsApp Cloud API
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_permanent_access_token
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_secure_random_token
WHATSAPP_API_VERSION=v21.0
```

---

**🎉 Congratulations!** Your WhatsApp integration is now complete. Start sending messages and engaging with your customers on WhatsApp!

For questions or issues, please check the troubleshooting section or consult the official WhatsApp Cloud API documentation.

