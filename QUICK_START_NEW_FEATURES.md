# 🚀 Quick Start Guide - New Features

## ⚡ Quick Setup (5 Minutes)

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Test the Features

#### Test Tags
1. Visit: `/tags`
2. Click "Create Tag"
3. Create a tag called "VIP" with any color
4. Go to `/contacts`
5. Click on a contact and assign the "VIP" tag

#### Test Notifications
1. Visit: `/notifications/settings`
2. Enable "Low Balance Alerts"
3. Set threshold to a high number (e.g., 10000)
4. Run: `php artisan balance:check-low`
5. Click the bell icon (top right) - you should see a notification!

#### Test Scheduled Messages
1. **Option A - Via API:**
```bash
# Create a scheduled campaign via database
php artisan tinker
```
```php
$campaign = App\Models\Campaign::create([
    'client_id' => 1,
    'name' => 'Test Scheduled Campaign',
    'message' => 'This is a test scheduled message',
    'sender_id' => 'PRADY_TECH',
    'channel' => 'sms',
    'recipients' => ['254728883160'],
    'total_recipients' => 1,
    'status' => 'draft',
    'is_scheduled' => true,
    'scheduled_at' => now()->addMinutes(2), // 2 minutes from now
]);
```
```bash
# Process it
php artisan campaigns:process-scheduled
```

**Option B - Via UI** (requires form update - see Optional Enhancements below)

---

## 📱 Using the Features

### Tags

**Create a Tag:**
1. Sidebar → **Tags**
2. **Create Tag** button
3. Enter name, color, description
4. Save

**Assign Tags to Contacts:**
1. Go to contact detail page
2. OR use the API:
```php
$contact = App\Models\Contact::find(1);
$contact->addTag($tagId);
```

**Send to Tagged Contacts (via API):**
```php
// Get all contacts with "VIP" tag
$vipTag = App\Models\Tag::where('name', 'VIP')->first();
$contacts = $vipTag->contacts()->pluck('contact')->toArray();

// Create campaign
$campaign = App\Models\Campaign::create([
    'client_id' => 1,
    'name' => 'VIP Promotion',
    'message' => 'Exclusive offer for VIP customers!',
    'sender_id' => 'PRADY_TECH',
    'channel' => 'sms',
    'recipients' => $contacts,
    'total_recipients' => count($contacts),
    'status' => 'draft',
]);
```

### Notifications

**Configure Settings:**
1. Sidebar → **Notifications**
2. Toggle alerts on/off
3. Set thresholds
4. Choose channels (Email, SMS, In-App)
5. Save

**View Notifications:**
- Click **bell icon** (top right)
- Red badge shows unread count
- Click notification to mark as read
- Click "Mark all read" to clear all

### Scheduled Messages

**Schedule a Campaign (Programmatically):**
```php
$campaign = App\Models\Campaign::create([
    'client_id' => 1,
    'name' => 'Monthly Reminder',
    'message' => 'Your payment is due on the 1st',
    'sender_id' => 'PRADY_TECH',
    'channel' => 'sms',
    'recipients' => ['254728883160'],
    'total_recipients' => 1,
    'status' => 'draft',
    'is_scheduled' => true,
    'scheduled_at' => '2025-11-01 09:00:00', // Future date
    'recurrence' => 'monthly', // Optional: daily, weekly, monthly
]);
```

**Process Scheduled Campaigns:**
```bash
php artisan campaigns:process-scheduled
```

**Set up Cron (Production):**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Common Scenarios

### Scenario 1: Send Birthday Messages

```php
// 1. Create birthday tags
foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $month) {
    App\Models\Tag::create([
        'client_id' => 1,
        'name' => "Birthday-$month",
        'slug' => "birthday-$month",
        'color' => '#3490dc',
    ]);
}

// 2. Assign contacts to their birthday month tag
$contact = App\Models\Contact::find(1);
$contact->addTag($januaryTagId);

// 3. Create recurring campaign for each month
$januaryTag = App\Models\Tag::where('name', 'Birthday-Jan')->first();
$campaign = App\Models\Campaign::create([
    'client_id' => 1,
    'name' => 'January Birthdays',
    'message' => 'Happy Birthday! 🎉',
    'sender_id' => 'PRADY_TECH',
    'channel' => 'sms',
    'recipients' => $januaryTag->contacts()->pluck('contact')->toArray(),
    'total_recipients' => $januaryTag->contacts_count,
    'is_scheduled' => true,
    'scheduled_at' => '2026-01-01 08:00:00',
    'recurrence' => 'yearly',
]);
```

### Scenario 2: Weekly Payment Reminders

```php
// Create "Debtors" tag
$debtorsTag = App\Models\Tag::create([
    'client_id' => 1,
    'name' => 'Debtors',
    'color' => '#ef4444',
]);

// Add contacts who owe money
$debtors = [1, 2, 3]; // Contact IDs
foreach($debtors as $contactId) {
    $contact = App\Models\Contact::find($contactId);
    $contact->addTag($debtorsTag->id);
}

// Create weekly reminder
$campaign = App\Models\Campaign::create([
    'client_id' => 1,
    'name' => 'Weekly Payment Reminder',
    'message' => 'Hi! This is a friendly reminder about your pending payment. Please clear by end of week.',
    'sender_id' => 'PRADY_TECH',
    'channel' => 'sms',
    'recipients' => $debtorsTag->contacts()->pluck('contact')->toArray(),
    'total_recipients' => $debtorsTag->contacts_count,
    'is_scheduled' => true,
    'scheduled_at' => now()->next('Monday')->setTime(9, 0),
    'recurrence' => 'weekly',
]);
```

### Scenario 3: Low Balance Monitoring

```php
// Set up notification settings
$settings = App\Models\NotificationSetting::firstOrCreate([
    'client_id' => 1,
]);

$settings->update([
    'low_balance_enabled' => true,
    'low_balance_threshold' => 500.00, // Alert when below KES 500
    'notify_via_email' => true,
    'notify_via_browser' => true,
]);

// Test it
php artisan balance:check-low
```

---

## 🔧 Optional UI Enhancements

The core functionality is complete, but you can add these UI enhancements:

### 1. Scheduling UI in Campaign Form

**Update: `resources/views/campaigns/create.blade.php`**

Add after message field:
```html
<div class="mb-3">
    <label class="form-label">Schedule (Optional)</label>
    <div class="form-check">
        <input type="checkbox" name="is_scheduled" id="is_scheduled" class="form-check-input">
        <label for="is_scheduled" class="form-check-label">Schedule for later</label>
    </div>
</div>

<div id="scheduleFields" style="display: none;">
    <div class="mb-3">
        <label class="form-label">Send Date & Time</label>
        <input type="datetime-local" name="scheduled_at" class="form-control">
    </div>
    
    <div class="mb-3">
        <label class="form-label">Recurrence (Optional)</label>
        <select name="recurrence" class="form-select">
            <option value="">One-time only</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>
</div>

<script>
document.getElementById('is_scheduled').addEventListener('change', function() {
    document.getElementById('scheduleFields').style.display = 
        this.checked ? 'block' : 'none';
});
</script>
```

### 2. Tag Filter in Contacts Page

**Update: `resources/views/contacts/index.blade.php`**

Add to filters:
```html
<div class="col-md-2">
    <label class="form-label">Tag</label>
    <select name="tag" class="form-select">
        <option value="">All Tags</option>
        @foreach(\App\Models\Tag::where('client_id', session('client_id', 1))->get() as $tag)
            <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                {{ $tag->name }}
            </option>
        @endforeach
    </select>
</div>
```

**Update ContactController:**
```php
public function index(Request $request) {
    // ... existing code ...
    
    // Filter by tag
    if ($request->filled('tag')) {
        $query->withTag($request->tag);
    }
    
    // ... rest of code ...
}
```

### 3. Tag Selector in Campaign Form

**Add to campaign create form:**
```html
<div class="mb-3">
    <label class="form-label">Select Recipients</label>
    <div class="form-check">
        <input type="radio" name="recipient_type" value="manual" id="manual" class="form-check-input" checked>
        <label for="manual" class="form-check-label">Manual Entry</label>
    </div>
    <div class="form-check">
        <input type="radio" name="recipient_type" value="tags" id="by_tags" class="form-check-input">
        <label for="by_tags" class="form-check-label">Select by Tags</label>
    </div>
</div>

<div id="tagSelector" style="display: none;">
    <label class="form-label">Select Tags</label>
    @foreach(\App\Models\Tag::where('client_id', session('client_id', 1))->get() as $tag)
        <div class="form-check">
            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-check-input">
            <label class="form-check-label">
                <span class="badge" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                ({{ $tag->contacts_count }} contacts)
            </label>
        </div>
    @endforeach
</div>

<script>
document.querySelectorAll('[name="recipient_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('tagSelector').style.display = 
            this.value === 'tags' ? 'block' : 'none';
    });
});
</script>
```

---

## 📊 Testing Commands

```bash
# Test scheduled campaigns
php artisan campaigns:process-scheduled

# Test low balance alerts
php artisan balance:check-low

# View all scheduled campaigns
php artisan tinker
> App\Models\Campaign::scheduled()->get(['id', 'name', 'scheduled_at']);

# View all tags
> App\Models\Tag::with('contacts')->get();

# View notifications
> App\Models\User::find(1)->notifications;
```

---

## ✅ Verification Checklist

- [ ] Migrations ran successfully
- [ ] Tags page loads (`/tags`)
- [ ] Can create a tag
- [ ] Notification settings page loads (`/notifications/settings`)
- [ ] Can update notification settings
- [ ] Bell icon shows in header
- [ ] Can create scheduled campaign via Tinker
- [ ] `campaigns:process-scheduled` command runs
- [ ] `balance:check-low` command runs
- [ ] Tags appear in sidebar
- [ ] Tag count badge shows

---

## 🎉 You're Ready!

All three features are now live:
- ✅ **Scheduled Messages** - Schedule campaigns for future dates
- ✅ **Contact Tags** - Organize and segment contacts
- ✅ **Smart Notifications** - Get alerts for important events

**Next Steps:**
1. Run migrations
2. Test each feature
3. Configure notification settings
4. Set up cron jobs (production)
5. (Optional) Add UI enhancements listed above

**Need Help?** Check `QUICK_WINS_IMPLEMENTATION_COMPLETE.md` for full documentation.


