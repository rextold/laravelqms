# How to Fix "Access Denied" Error on Counter Panel

## Problem
You're logged in as a counter user but getting "Access Denied" when visiting the counter panel.

## Root Cause
The counter's `organization_id` in the database doesn't match the organization code in the URL.

**Example:**
- URL: `https://qms.sogodlgu.gov.ph/default/counter/panel`
- Counter's org: "mysocial" (organization_id = 2)
- Mismatch! The counter is assigned to "mysocial" org but trying to access "default" org

## Solution

### Step 1: Check Your Counter's Organization
1. **Visit this debug URL** (while logged in as the counter):
   ```
   https://qms.sogodlgu.gov.ph/debug/counter-org
   ```

2. **You'll see something like:**
   ```json
   {
     "user_email": "counter@example.com",
     "user_organization": {
       "id": 1,
       "code": "default",
       "name": "MyOrganization"
     },
     "correct_url": "/default/counter/panel"
   }
   ```

### Step 2: Use the Correct URL
Based on the debug output, visit the correct URL:
```
https://qms.sogodlgu.gov.ph{correct_url}
```

### Step 3 (If Organization is NULL)
If you see:
```json
{
  "user_organization_id": null,
  "user_organization": null,
  "message": "Your account has NO organization assigned (NULL)"
}
```

**Use this auto-redirect URL:**
```
https://qms.sogodlgu.gov.ph/counter/panel-auto
```

This will automatically redirect you to the first organization's counter panel.

---

## For Administrators

### To Assign an Organization to a Counter
Run in database:
```sql
-- Find all counters without an organization
SELECT id, email, counter_number FROM users 
WHERE role = 'counter' AND organization_id IS NULL;

-- Assign them to the default organization
UPDATE users 
SET organization_id = (SELECT id FROM organizations WHERE organization_code = 'default')
WHERE role = 'counter' AND organization_id IS NULL;
```

### To Move a Counter to a Different Organization
```sql
-- Find the organization ID first
SELECT id, organization_code FROM organizations WHERE organization_code = 'myorg';

-- Then update the counter
UPDATE users 
SET organization_id = [org_id] 
WHERE id = [counter_id];
```

---

## Quick Access URLs

| URL | Purpose |
|-----|---------|
| `/debug/counter-org` | Check your organization assignment |
| `/counter/panel-auto` | Auto-redirect to your correct panel |
| `/login` | Login again |

