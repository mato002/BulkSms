# Quick Setup Guide - Senders & Units System 🚀

## ✅ What's New?

### 1. **Company Name** instead of "Sender ID"
- More professional terminology
- `company_name` field in database
- Backward compatible with `sender_id`

### 2. **Unit-Based Pricing**
- Balance shown in **Units** (user-friendly)
- Stored in **KSH** (backend)
- Each client can have different **price per unit**

### 3. **New Senders Page** (`/senders`)
- View your company info
- Check balance (units & KSH)
- Unit converter calculator

---

## 🔧 Quick Setup

### **Step 1: Access Senders Page**
```
URL: http://your-domain.com/senders

Or click: "My Company" in sidebar menu
```

### **Step 2: Understanding Your Balance**

**What You See:**
```
Balance (Units): 1,000 units
Balance (KSH):   1,500 KSH  
Price Per Unit:  KSH 1.50
```

**What It Means:**
- You can send 1,000 messages
- Each message costs 1.50 KSH
- Total value: 1,500 KSH

---

## 💰 Pricing Examples

### **Example 1: Standard Pricing**
```
Price per unit: 1.00 KSH
Balance: 1,000 KSH = 1,000 units
→ Can send 1,000 messages
```

### **Example 2: Bulk Discount**
```
Price per unit: 0.80 KSH (bulk rate)
Balance: 1,000 KSH = 1,250 units
→ Can send 1,250 messages
```

### **Example 3: Premium Pricing**
```
Price per unit: 1.50 KSH
Balance: 1,500 KSH = 1,000 units
→ Can send 1,000 messages
```

---

## 🧮 Unit Converter

**Built-in calculator on senders page:**

### Convert Units to KSH:
```
Input:  100 units
Price:  1.50 KSH/unit
Output: 150 KSH
```

### Convert KSH to Units:
```
Input:  150 KSH
Price:  1.50 KSH/unit
Output: 100 units
```

---

## 📊 Admin: Managing Senders

### **Create New Sender:**
1. Go to `/admin/senders/create`
2. Enter:
   - Company Name: `YOURCOMPANY`
   - Contact Person: `John Doe`
   - Contact Info: `john@example.com`
   - Initial Balance: `1000` (KSH)
   - **Price Per Unit:** `1.50` (KSH)
3. Create user account (optional)
4. Save

### **Update Balance:**
1. Go to sender details
2. Click "Update Balance"
3. Choose:
   - **Type:** Units or KSH
   - **Action:** Add, Deduct, or Set
   - **Amount:** Enter value
4. System auto-converts and updates

**Examples:**
```
Add 100 units (at 1.50 KSH/unit):
→ Adds 150 KSH to balance

Set to 500 KSH:
→ Balance becomes 333.33 units (500 ÷ 1.50)
```

---

## 🔄 How It Works (Technical)

### **Storage:**
```
Database stores: Balance in KSH (always)
```

### **Display:**
```
User sees: Balance in Units (calculated)
```

### **Calculation:**
```php
Balance (Units) = Balance (KSH) ÷ Price Per Unit

Example:
1,500 KSH ÷ 1.50 = 1,000 units
```

### **When Sending Message:**
```php
Cost in KSH = Units × Price Per Unit

Example:
50 messages × 1.50 = 75 KSH deducted
```

---

## 🎯 Key Features

### **For Users:**
✅ See balance in units (easy to understand)  
✅ Know exact price per message  
✅ Built-in converter calculator  
✅ Company branding (company name vs sender ID)  

### **For Admin:**
✅ Set different pricing for each client  
✅ Manage balance in units or KSH  
✅ Flexible pricing model  
✅ Track all senders from one dashboard  

---

## 📱 Menu Location

**New Menu Item:**
```
Dashboard
Contacts
Templates
Campaigns
Messages
Inbox
Analytics
→ My Company    ← NEW!
WhatsApp
Settings
```

---

## 🔗 Quick Links

- **View Your Company:** `/senders`
- **Admin Dashboard:** `/admin/senders` (admin only)
- **Create Sender:** `/admin/senders/create` (admin only)

---

## 💡 Pro Tips

### **Tip 1: Pricing Strategy**
```
High volume clients: 0.80 KSH/unit (discount)
Standard clients:    1.00 KSH/unit
Premium service:     1.50 KSH/unit
```

### **Tip 2: Top-ups**
```
Offer packages:
- 1,000 units = Pay for 1,000 KSH (at 1.00 rate)
- 1,000 units = Pay for 800 KSH (at 0.80 rate) ← bulk discount
```

### **Tip 3: Balance Display**
```
Always show both:
- Units (for user clarity)
- KSH (for accounting)
```

---

## 🐛 Troubleshooting

### **Q: Units not showing correctly?**
**A:** Check `price_per_unit` is set (default: 1.00)

### **Q: Balance calculation wrong?**
**A:** Units = Balance (KSH) ÷ Price Per Unit
- Example: 300 KSH ÷ 1.50 = 200 units

### **Q: Can't see senders page?**
**A:** Check you're logged in and route `/senders` is accessible

---

## ✨ Summary

🎉 **You now have:**
- Unit-based pricing system
- Company name branding  
- Balance in units (user-friendly)
- Flexible per-client pricing
- Built-in converter calculator
- Separate senders management page

**Everything is backward compatible!** Existing code continues to work while new features are available. 🚀

