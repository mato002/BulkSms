# 📊 CSV Upload Guide - BulkSms CRM

## 📁 Sample Contact Import File

### **Format:**
```csv
Name,Phone,Department
```

### **Example CSV File:**
```csv
Name,Phone,Department
John Doe,254712345678,Sales
Jane Smith,254723456789,Marketing
Peter Mwangi,254734567890,Customer Service
Mary Wanjiru,254745678901,Finance
David Kamau,254756789012,IT Department
```

---

## 📝 **CSV Format Requirements:**

### **Column 1: Name** (Required)
- Contact's full name
- Example: `John Doe`, `Mary Wanjiru`

### **Column 2: Phone** (Required)
- Phone number in any format:
  - ✅ `254712345678` (recommended)
  - ✅ `0712345678`
  - ✅ `712345678`
  - ✅ `+254712345678`
- System will auto-format to: `254XXXXXXXXX`

### **Column 3: Department** (Optional)
- Any text field
- Example: `Sales`, `Marketing`, `IT`

---

## 📤 **How to Upload:**

### **Step 1: Prepare Your CSV File**
1. Open Excel or Google Sheets
2. Create 3 columns: `Name`, `Phone`, `Department`
3. Add your contacts
4. Save as CSV format (`.csv`)

### **Step 2: Upload in BulkSms**
1. Login to BulkSms CRM
2. Go to **Contacts** page
3. Click **Import CSV** button
4. Select your CSV file
5. Click **Upload**

### **Step 3: Verify Upload**
- Check success message showing number of imported contacts
- Browse contacts list to verify

---

## ✅ **Sample CSV Files:**

I've created `sample_contacts_upload.csv` in your BulkSms folder with 10 sample contacts.

**Location:** `C:\xampp\htdocs\BulkSms\sample_contacts_upload.csv`

---

## 💡 **Tips:**

### **Valid Phone Formats:**
All these formats work:
```
254712345678
0712345678
712345678
+254712345678
254-712-345-678
```

### **Common Issues:**

❌ **Problem:** Numbers starting with country code not 254
- **Solution:** Update to Kenyan format (254...)

❌ **Problem:** Missing header row
- **Solution:** First row must be: `Name,Phone,Department`

❌ **Problem:** Excel adds quotes
- **Solution:** Save as CSV UTF-8, not Excel CSV

---

## 🧪 **Test Data:**

Use these test contacts:

```csv
Name,Phone,Department
Test User 1,254728883160,Testing
Test User 2,254712345678,Development
Test User 3,254723456789,QA
```

---

## 📋 **Quick Reference:**

| Field | Required | Format | Example |
|-------|----------|--------|---------|
| Name | ✅ Yes | Text | John Doe |
| Phone | ✅ Yes | 254... or 07... | 254712345678 |
| Department | ❌ No | Text | Sales |

**File:** `sample_contacts_upload.csv` is ready to use!

---

## 🚀 **After Upload:**

Contacts will be available:
- ✅ In Contacts list
- ✅ For campaigns
- ✅ For bulk messaging
- ✅ In recipient dropdowns

**Ready to import contacts!** 📱



