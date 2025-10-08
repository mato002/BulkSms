# Sender/Tenant Management System

## Overview

This system provides a comprehensive multi-tenant sender management platform where administrators can manage multiple senders/tenants, each with their own API keys, balances, and settings.

## Features

### ✨ Core Features

1. **Multi-Tenant Architecture**
   - Each sender operates independently with isolated data
   - Dedicated API keys for each tenant
   - Individual balance management
   - User accounts linked to specific senders

2. **Sender Management**
   - Create new senders with auto-generated API keys
   - View detailed sender information and statistics
   - Edit sender details and settings
   - Activate/deactivate senders
   - Delete senders (with safeguards)

3. **API Key Management**
   - Automatic API key generation (format: `sk_` + 32 random characters)
   - View and copy API keys
   - Regenerate API keys when needed
   - Secure key storage

4. **Balance Management**
   - Set initial balance for new senders
   - Add, deduct, or set balance
   - Real-time balance tracking
   - Balance validation for operations

5. **User Account Integration**
   - Option to create user accounts during sender creation
   - Link multiple users to a sender
   - Role-based access (admin/user)

## Access

### Admin Access Required

Only users with the `admin` role can access the sender management system.

**Navigation:** Go to **Admin → Manage Senders** in the sidebar

**Direct URL:** `/admin/senders`

## How to Use

### 1. Creating a New Sender

1. Navigate to **Admin → Manage Senders**
2. Click **"Add New Sender"** button
3. Fill in the required information:
   - **Sender Name**: Company or organization name
   - **Sender ID**: Unique identifier (max 11 characters, will be uppercase)
   - **Contact**: Email or phone number
   - **Initial Balance**: Starting balance (optional, default: 0)
   - **Status**: Active/Inactive

4. **Optional:** Create a user account for this sender
   - Check "Create a user account for this sender"
   - Fill in user details (name, email, password)

5. Click **"Create Sender"**
6. Save the generated API key securely (it will be displayed once)

### 2. Viewing Sender Details

1. Click on any sender in the list
2. View comprehensive information:
   - Basic details (name, sender ID, contact)
   - Current balance with update form
   - API key with copy and regenerate options
   - Statistics (messages, campaigns, contacts)
   - Recent messages
   - Associated users

### 3. Editing a Sender

1. Go to sender details page
2. Click **"Edit"** button
3. Modify the information as needed:
   - Name, Sender ID, Contact
   - Balance
   - Status (Active/Inactive)
4. Manage API key:
   - Copy current key
   - Regenerate if compromised
5. Click **"Update Sender"**

### 4. Managing Balance

**From Sender Details Page:**

1. Find the balance card (purple gradient)
2. Enter the amount
3. Select action:
   - **Add**: Increase balance
   - **Deduct**: Decrease balance
   - **Set**: Set exact balance
4. Click **"Update Balance"**

### 5. API Key Operations

**Copy API Key:**
- Click the copy button next to the API key
- Or use the copy button in the table

**Regenerate API Key:**
- Click **"Regenerate"** button
- Confirm the action (old key will be invalidated)
- Save the new key securely

### 6. Activating/Deactivating Senders

**From Index Page:**
- Click the activate/deactivate icon in the actions column

**From Edit Page:**
- Change the status dropdown
- Click "Update Sender"

**Note:** Inactive senders cannot use the API

### 7. Deleting a Sender

⚠️ **Warning:** This action cannot be undone

1. Go to the sender's edit page
2. Scroll to the **"Danger Zone"** section
3. Click **"Delete Sender"**
4. Confirm the deletion

**Note:** The default client (ID: 1) cannot be deleted

## API Integration

### Authentication

All API requests require the sender's API key in the header:

```bash
X-API-Key: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Or as a query parameter:

```bash
?api_key=sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Example API Request

```bash
curl -X POST https://your-domain.com/api/v1/messages/send \
  -H "X-API-Key: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient": "+254700000000",
    "message": "Hello from API",
    "channel": "sms"
  }'
```

### API Endpoints Available

- **POST** `/api/v1/messages/send` - Send a message
- **GET** `/api/v1/client/profile` - Get sender profile
- **GET** `/api/v1/client/balance` - Get current balance
- **GET** `/api/v1/client/statistics` - Get statistics
- **POST** `/api/v1/contacts` - Create contact
- **GET** `/api/v1/contacts` - List contacts
- And more...

## Features & Statistics

### Dashboard Statistics

The admin dashboard shows:
- **Total Senders**: All registered senders
- **Active Senders**: Currently active senders
- **Total Balance**: Sum of all sender balances
- **Total Messages**: Messages sent across all senders

### Per-Sender Statistics

Each sender's detail page shows:
- Total messages sent
- Delivered messages
- Failed messages
- Total campaigns
- Total contacts
- Recent message history

### Search & Filter

**Search by:**
- Sender name
- Contact information
- Sender ID
- API key

**Filter by:**
- Status (Active/Inactive/All)

## Security Features

1. **Admin-Only Access**
   - All routes protected by authentication
   - Additional admin role check
   - Unauthorized users get 403 error

2. **API Key Security**
   - Unique random generation
   - Secure storage
   - Easy regeneration if compromised

3. **Data Isolation**
   - Each sender's data is isolated
   - Users can only access their sender's data
   - Admin can view all senders

4. **Audit Trail**
   - All actions are logged
   - Timestamps on all records
   - User tracking for actions

## Database Structure

### Clients Table

```sql
- id (primary key)
- name (string)
- contact (string)
- sender_id (string, unique, max 11 chars)
- balance (decimal)
- api_key (string, unique)
- status (boolean)
- settings (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### Relationships

- **clients → users** (one-to-many)
- **clients → contacts** (one-to-many)
- **clients → campaigns** (one-to-many)
- **clients → messages** (one-to-many)
- **clients → sms** (one-to-many)

## Routes Reference

```php
// Web Routes (Admin Only)
GET     /admin/senders                      - List all senders
GET     /admin/senders/create               - Show create form
POST    /admin/senders                      - Store new sender
GET     /admin/senders/{id}                 - Show sender details
GET     /admin/senders/{id}/edit            - Show edit form
PUT     /admin/senders/{id}                 - Update sender
DELETE  /admin/senders/{id}                 - Delete sender
POST    /admin/senders/{id}/regenerate-api-key  - Regenerate API key
POST    /admin/senders/{id}/update-balance  - Update balance
PATCH   /admin/senders/{id}/toggle-status   - Toggle active/inactive
```

## Best Practices

1. **API Key Management**
   - Never expose API keys in client-side code
   - Store securely on the server
   - Regenerate immediately if compromised
   - Use environment variables in production

2. **Balance Management**
   - Monitor balances regularly
   - Set up low balance alerts
   - Track usage patterns
   - Implement auto-top-up if needed

3. **Sender Creation**
   - Use meaningful sender IDs
   - Keep contact information updated
   - Create user accounts for self-service
   - Document sender purpose/use case

4. **Security**
   - Regularly audit active senders
   - Deactivate unused senders
   - Review access logs
   - Update credentials periodically

## Troubleshooting

### Issue: "Unauthorized access" error

**Solution:** Ensure the logged-in user has the `admin` role:
```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

### Issue: API key not working

**Possible causes:**
1. Sender is inactive - activate the sender
2. API key was regenerated - use the new key
3. Invalid format - ensure header is `X-API-Key` or param is `api_key`

### Issue: Balance not updating

**Solution:**
1. Check sender has sufficient balance
2. Verify balance update permissions
3. Check for validation errors
4. Review application logs

### Issue: Cannot delete sender

**Possible causes:**
1. Trying to delete default client (ID: 1)
2. Foreign key constraints
3. Insufficient permissions

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── AdminController.php          # Main controller
│   └── Middleware/
│       └── ApiAuth.php                   # API authentication
├── Models/
│   ├── Client.php                        # Client/Sender model
│   └── User.php                          # User model
resources/
└── views/
    └── admin/
        └── senders/
            ├── index.blade.php            # List view
            ├── create.blade.php           # Create form
            ├── edit.blade.php             # Edit form
            └── show.blade.php             # Details view
routes/
└── web.php                                # Web routes
```

## Support

For issues or questions:
1. Check the application logs: `storage/logs/laravel.log`
2. Review this documentation
3. Contact system administrator
4. Check API documentation

## Future Enhancements

Potential features to consider:
- Multi-currency support
- Automated balance top-up
- Usage analytics dashboard
- Email notifications for low balance
- API usage rate limiting per sender
- Webhook management per sender
- White-label portal for senders
- Sub-user management
- Advanced reporting

---

**Last Updated:** {{ date('Y-m-d') }}
**Version:** 1.0.0

