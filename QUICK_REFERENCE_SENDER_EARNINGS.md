# Quick Reference: Sender Earnings Tracking

## 🚀 Quick Access

### Option 1: From Sidebar
```
Sidebar → Messages → Click "All Messages & Earnings" button (top right)
```

### Option 2: Direct URL
```
/messages-all
```

## 📊 What You'll See

### Top Section: Statistics (5 Cards)
```
┌─────────────┬─────────────┬─────────────┬─────────────┬─────────────┐
│ Total Msgs  │   Sent      │   Failed    │  Pending    │  Earnings   │
│   1,234     │    1,100    │     50      │    84       │ KSH 5,500   │
└─────────────┴─────────────┴─────────────┴─────────────┴─────────────┘
```

### Middle Section: Sender Performance Cards
```
┌─────────────────────────────────────────────────────────┐
│  SENDER-NAME                          KSH 2,500.00      │
├─────────────┬─────────────┬─────────────┬──────────────┤
│ Total: 500  │ Success: 480│ Failed: 20  │ Rate: 96.0%  │
├─────────────────────────────────────────────────────────┤
│            [🔍 View Messages]                           │
└─────────────────────────────────────────────────────────┘
```

### Filter Section
```
Search: [________________]  Sender: [All Senders ▼]
Channel: [All Channels ▼]  Status: [All Status ▼]
From: [________]           To: [________]
[Apply Filters]  [Clear All]
```

### Bottom Section: Messages Table
```
ID    Sender   Recipient    Channel  Message    Status    Cost       Date
#123  MyBrand  +254712...   SMS      Welcome... ✅ Sent   KSH 1.00   Oct 20
#124  MyBrand  +254723...   SMS      Thank...   ✅ Sent   KSH 1.00   Oct 20
```

## 🎯 Common Tasks

### Task 1: View Today's Earnings
```
1. Go to /messages-all
2. Set "Date From" = Today
3. Set "Date To" = Today
4. Look at "Total Earnings" card
```

### Task 2: Track Specific Sender
```
1. Go to /messages-all
2. Select sender from dropdown
3. Click "Apply Filters"
4. View statistics and messages
```

### Task 3: Compare Senders
```
1. Go to /messages-all
2. Scroll to "Sender Performance & Earnings"
3. Compare cards side-by-side
```

### Task 4: Find Failed Messages
```
1. Go to /messages-all
2. Set Status = "Failed"
3. Optional: Select specific sender
4. Click "Apply Filters"
```

### Task 5: Monthly Revenue Report
```
1. Go to /messages-all
2. Date From = 1st of month
3. Date To = Last day of month
4. Note "Total Earnings" value
```

## 💡 Pro Tips

### Tip 1: Quick Sender Filter
Instead of using the filter dropdown, click **"View Messages"** on any Sender Performance Card.

### Tip 2: Combine Filters
Use multiple filters together:
- Sender = "MyBrand" + Status = "Failed" → Find all failures for MyBrand
- Channel = "SMS" + Date Range → SMS earnings for specific period

### Tip 3: Search Everything
The search box searches across:
- Recipient phone/email
- Message body
- Sender name

### Tip 4: Earnings Only Show for Success
Cost column shows amounts only for "sent" or "delivered" messages.

### Tip 5: Reset Filters
Click **"Clear All"** to remove all filters and see everything.

## 📈 Key Metrics Explained

### Total Messages
All messages matching your current filters.

### Sent
Messages with status = "sent" or "delivered".

### Failed
Messages with status = "failed".

### Pending
Messages with status = "queued" or "sending".

### Total Earnings
Sum of costs from all successful (sent/delivered) messages.
**Note**: This respects your filters!

### Success Rate (on cards)
```
Success Rate = (Successful Messages / Total Messages) × 100
```

## 🎨 Visual Cues

### Status Colors
- 🟢 **Green** = Sent/Delivered (Success)
- 🔴 **Red** = Failed
- 🟡 **Yellow** = Queued/Pending
- 🔵 **Blue** = Sending

### Channel Icons
- 📱 **SMS** = Phone icon
- 💬 **WhatsApp** = WhatsApp icon
- ✉️ **Email** = Envelope icon

### Earnings Display
- **Gold background** = Total Earnings card (highlighted)
- **Green text** = Individual sender earnings
- **Green badge** = Cost per message

## ⚡ Shortcuts

| Action | How To |
|--------|--------|
| View all messages | Go to `/messages-all` |
| Filter by sender | Click sender dropdown or click "View Messages" on card |
| See today's stats | Set date filter to today |
| Find failures | Set status filter to "Failed" |
| Reset view | Click "Clear All" button |
| Go back to conversations | Click "Conversations View" button (top right) |

## 🔧 Troubleshooting

### Problem: No senders in dropdown
**Solution**: No messages sent yet with sender field populated.

### Problem: Earnings show 0
**Solution**: Either no successful messages or cost not set in messages.

### Problem: No data showing
**Solution**: Clear filters or check date range.

### Problem: Can't find a sender
**Solution**: Check spelling (case-sensitive) or use search box.

## 📚 More Help

- Full Guide: `SENDER_EARNINGS_TRACKING_GUIDE.md`
- Implementation Details: `IMPLEMENTATION_SUMMARY_SENDER_TRACKING.md`
- System Docs: `SYSTEM_ARCHITECTURE_GUIDE.md`

---

**Quick Start**: Go to Messages → Click "All Messages & Earnings" → Start tracking! 🚀

