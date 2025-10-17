# Dashboard Onfon Balance Display - Quick Guide

## What's New? 🎉

The dashboard now displays your **Onfon wallet balance** alongside your local balance, making it easy to monitor both balances in one place!

## Where to Find It

On your dashboard, you'll now see **4 balance cards** (if you have balance tracking enabled):

1. **Wallet Balance** (Green) - Your local balance
2. **Onfon Balance** (Blue) - Your Onfon Media wallet balance ⭐ NEW!
3. **Price Per Unit** (Purple) - Cost per SMS
4. **Total Spent** (Orange) - All-time spending

## How to Sync Your Onfon Balance

### First Time Setup

If you see "Not synced" on the Onfon Balance card:

1. Click the **🔄 sync button** (circular arrow icon) on the Onfon Balance card
2. Wait a few seconds while the system fetches your balance from Onfon
3. Your balance will be displayed immediately!

### Regular Syncing

The Onfon balance shows your last synced amount. To get the latest balance:

1. Click the **🔄 sync button** anytime
2. The balance updates in real-time
3. You'll see a notification showing:
   - Success message
   - Balance difference (if changed)
   - Last sync time

## Features

### Real-Time Updates
- Click sync to get your current Onfon balance instantly
- No page refresh needed
- Shows loading animation while syncing

### Smart Notifications
- Success message when sync completes
- Shows balance change amount (e.g., "+KSh 100.00" or "-KSh 50.00")
- Error messages if sync fails (e.g., credentials not configured)

### Visual Feedback
- Shows "Not synced" if never synced
- Displays last sync time (e.g., "2 minutes ago", "1 hour ago")
- Sync button animates on hover

## Common Scenarios

### Scenario 1: First Time User
**Problem:** Shows "Not synced"
**Solution:** Click the sync button to fetch your balance for the first time

### Scenario 2: Need Latest Balance
**Problem:** Want to check current Onfon balance
**Solution:** Click the sync button anytime to get real-time balance

### Scenario 3: Sync Failed
**Problem:** Error message appears
**Solution:** 
- Ensure Onfon credentials are configured (contact admin)
- Check your internet connection
- Try again in a few moments

## Technical Details

### What Happens When You Sync?

1. System connects to Onfon Media API
2. Fetches your current wallet balance
3. Updates the dashboard display
4. Records the sync time
5. Shows you the results

### Automatic Sync (Admin Feature)

Admins can enable auto-sync for your account:
- Syncs every 15 minutes automatically
- Keeps balance always up-to-date
- No manual action needed

## Tips & Best Practices

1. **Sync Before Campaigns:** Always sync before sending bulk messages to ensure sufficient balance
2. **Regular Checks:** Sync daily to monitor your spending
3. **Monitor Changes:** Watch for unexpected balance drops (shown in notifications)
4. **Compare Balances:** Use both Local and Onfon balances to track usage

## Troubleshooting

### Issue: "Onfon credentials not configured"
**Fix:** Contact your system administrator to set up Onfon credentials for your account

### Issue: "Network error. Please try again"
**Fix:** Check your internet connection and retry

### Issue: Balance shows 0.00
**Fix:** Verify you have funds in your Onfon wallet, or contact Onfon support

### Issue: Sync button not responding
**Fix:** Refresh the page and try again

## API Routes (For Developers)

The sync feature uses these endpoints:

```bash
# Sync balance (POST)
/wallet/onfon/sync

# Get balance (GET)
/wallet/onfon/balance
```

## Next Steps

1. ✅ Click the sync button to see your Onfon balance
2. ✅ Bookmark the dashboard for quick access
3. ✅ Sync before sending campaigns
4. ✅ Monitor both balances regularly

## Need Help?

- **For Balance Issues:** Contact Onfon Media support
- **For Sync Issues:** Contact your system administrator
- **For Technical Issues:** Check the error message for details

---

**Last Updated:** October 10, 2025  
**Feature Version:** 1.0.0

**Quick Tip:** You can now manage your Onfon balance directly from the dashboard without leaving the page! 🚀

