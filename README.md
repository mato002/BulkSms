# Bulk SMS CRM System

A comprehensive multi-channel messaging CRM built with Laravel, supporting SMS (Onfon), WhatsApp, and Email.

## Features

### Core Messaging
- **Multi-Channel Support**: SMS, WhatsApp, Email
- **Provider Integration**: Onfon SMS API (with extensible driver architecture)
- **Unified Send API**: Single endpoint for all channels
- **Delivery Reports**: Webhook support for status updates
- **Message Tracking**: Full audit trail with statuses and timestamps

### CRM Capabilities
- **Contact Management**: Create, import CSV, organize by department
- **Templates**: Reusable message templates with variable substitution
- **Campaigns**: Bulk messaging to multiple recipients
- **Dashboard**: Real-time stats and recent activity

### API
- **REST API**: Token-based authentication
- **Rate Limiting**: Per-client throttling
- **Webhooks**: Delivery reports and inbound messages

## Installation

### Requirements
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.4+
- Composer
- XAMPP (or equivalent LAMP/WAMP stack)

### Setup Steps

1. **Clone and Install Dependencies**
```bash
cd C:\xampp\htdocs\bulk-sms-laravel
composer install
```

2. **Configure Environment**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Update `.env` with MySQL Credentials**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bulk_sms_laravel
DB_USERNAME=root
DB_PASSWORD=
```

4. **Run Migrations**
```bash
php artisan migrate --force
```

5. **Seed Initial Data**
```bash
php artisan db:seed --class=ClientsSeeder
php artisan db:seed --class=ChannelsSeeder
```

6. **Start Development Server**
```bash
php artisan serve --host 127.0.0.1 --port 8000
```

Visit: http://127.0.0.1:8000

## Configuration

### Onfon SMS Setup

Update your channel credentials in the database:

```php
DB::table('channels')->where('client_id', 1)->where('name', 'sms')->update([
    'credentials' => json_encode([
        'api_key' => 'YOUR_ONFON_API_KEY',
        'client_id' => 'YOUR_ONFON_CLIENT_ID',
        'access_key_header' => 'YOUR_ACCESS_KEY_HEADER',
        'default_sender' => 'YOUR_SENDER_ID',
    ])
]);
```

### Webhook Configuration

Configure your Onfon account to send delivery reports to:
```
https://yourdomain.com/api/webhooks/onfon/dlr
```

## Usage

### Web UI

1. **Dashboard**: View stats and recent messages at `/`
2. **Contacts**: Manage contacts, import CSV at `/contacts`
3. **Templates**: Create reusable templates at `/templates`
4. **Campaigns**: Create and send bulk campaigns at `/campaigns`
5. **Messages**: View all sent messages with filters at `/messages`

### API Endpoints

#### Send Message (Unified)
```http
POST /api/{company_id}/messages/send
Headers:
  X-API-KEY: your-client-api-key
  Content-Type: application/json

Body:
{
  "client_id": 1,
  "channel": "sms",
  "recipient": "254728883160",
  "sender": "PRADY_TECH",
  "body": "Your message here"
}
```

#### Get Messages
```http
GET /api/{company_id}/sms/history
Headers:
  X-API-KEY: your-client-api-key
```

### CSV Import Format

**Contacts CSV:**
```csv
Name,Phone,Department
John Doe,254712345678,Sales
Jane Smith,254723456789,Marketing
```

Upload via: Contacts page → Import CSV button

## Architecture

### Messaging Layer
- **Contracts**: `MessageSender` interface
- **Drivers**: 
  - `OnfonSmsSender` - Onfon HTTP API
  - `CloudWhatsAppSender` - WhatsApp Cloud API (stub)
  - `SmtpEmailSender` - SMTP/Email provider (stub)
- **Dispatcher**: Routes messages to appropriate channel/provider
- **DTO**: `OutboundMessage` for type-safe message creation

### Database Schema

**Key Tables:**
- `clients`: Multi-tenant client accounts
- `contacts`: Contact directory per client
- `templates`: Message templates
- `channels`: Provider credentials per client
- `messages`: All sent messages with full audit trail
- `campaigns`: Bulk send campaigns

## Providers

### Onfon SMS

**API Documentation**: https://www.docs.onfonmedia.co.ke/rest/

**Credentials Required:**
- API Key
- Client ID (UUID for your tenant)
- Access Key Header
- Approved Sender ID

**Features:**
- Bulk sending via `SendBulkSMS` endpoint
- Delivery reports via webhook
- Balance checking
- Template support

### WhatsApp (Coming Soon)
- WhatsApp Cloud API integration
- Template message support
- Interactive messages

### Email (Coming Soon)
- SMTP provider support
- SendGrid/Postmark integration
- HTML templates

## Troubleshooting

### SSL Certificate Errors

If you see "SSL certificate problem":

1. Download CA bundle:
```powershell
mkdir C:\cacert
curl.exe -L https://curl.se/ca/cacert.pem -o C:\cacert\cacert.pem
```

2. Configure Composer:
```powershell
composer config -g cafile C:\cacert\cacert.pem
```

### MySQL Connection Refused

Ensure MySQL is running in XAMPP Control Panel.

### Onfon 405 Errors

- Verify your Onfon credentials (api_key, client_id, access_key_header)
- Check that SenderId is approved in your Onfon portal
- Ensure IP whitelisting is configured

## API Authentication

Client API keys are stored in the `clients` table. Get your key:

```sql
SELECT api_key FROM clients WHERE id = 1;
```

Use it in the `X-API-KEY` header for all authenticated API requests.

## Development

### Adding New Providers

1. Create driver in `app/Services/Messaging/Drivers/`
2. Implement `MessageSender` interface
3. Bind in `AppServiceProvider`
4. Add credentials to `channels` table

### Running Tests (Future)
```bash
php artisan test
```

## Security

- API keys for authentication
- Rate limiting via Laravel throttle
- CSRF protection on web routes
- Encrypted channel credentials (future enhancement)

## License

Proprietary. All rights reserved.

## Support

For issues or questions, contact your system administrator.
