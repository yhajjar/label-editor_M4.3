# Label Editor Plugin - Troubleshooting Guide

This guide addresses common issues and their solutions when using the Label Editor Plugin API.

## Common API Issues

### 1. Boolean Parameter Error

**Error Message:**
```json
{
    "exception": "core\\exception\\invalid_parameter_exception",
    "errorcode": "invalidparameter",
    "message": "Invalid parameter value detected",
    "debuginfo": "visible => Invalid parameter value detected: Invalid external api parameter: the value is \"true\", the server was expecting \"bool\" type"
}
```

**Cause:** Moodle's external API expects boolean parameters as integers, not string values.

**Solution:** Use integer values for boolean parameters:
- Use `visible=1` for true (visible)
- Use `visible=0` for false (hidden)

**Incorrect:**
```bash
curl -d "visible=true"   # ❌ Wrong
curl -d "visible=false"  # ❌ Wrong
```

**Correct:**
```bash
curl -d "visible=1"      # ✅ Correct
curl -d "visible=0"      # ✅ Correct
```

### 2. Section Number vs Section ID Confusion

**Error:** Label not created in expected section or "section not found" error.

**Cause:** Confusing section ID with section number.

**Understanding the Difference:**
- **Section Number**: The logical section (0, 1, 2, 3, etc.) - this is what you use in the API
- **Section ID**: The database ID of the section record - this is returned by get_sections but not used for creation

**Example from get_sections response:**
```json
{
    "id": 2457,        // ← This is the Section ID (don't use for creation)
    "section": 2,      // ← This is the Section Number (use this for creation)
    "name": "Week 2"
}
```

**Correct Usage:**
```bash
# Use section number (2), not section ID (2457)
curl -d "sectionnum=2"  # ✅ Correct
```

### 3. Permission Denied Errors

**Error Message:**
```json
{
    "exception": "required_capability_exception",
    "errorcode": "nopermissions",
    "message": "Sorry, but you do not currently have permissions to do that (Edit label content via API)."
}
```

**Solutions:**

#### Check User Capabilities
Ensure your API user has these capabilities:
- `local/labeleditor:edit` - Edit labels via API
- `local/labeleditor:view` - View labels via API
- `moodle/course:manageactivities` - Manage course activities
- `webservice/rest:use` - Use REST web services

#### Verify Service Configuration
1. Go to **Site Administration > Plugins > Web services > External services**
2. Find "Label Editor Service"
3. Ensure it's **enabled**
4. Add your user to **Authorised users**

#### Check Token Configuration
1. Go to **Site Administration > Plugins > Web services > Manage tokens**
2. Verify your token is:
   - **Valid** and not expired
   - Associated with the correct **service**
   - Assigned to a user with proper **capabilities**

### 4. Invalid Token Errors

**Error Message:**
```json
{
    "exception": "moodle_exception",
    "errorcode": "invalidtoken",
    "message": "Invalid token - token not found"
}
```

**Solutions:**
1. **Verify token format**: Ensure you're using the complete token string
2. **Check token status**: Go to token management and verify it's active
3. **Regenerate token**: Create a new token if the current one is corrupted
4. **Service association**: Ensure token is linked to "Label Editor Service"

### 5. Course/Section Not Found Errors

**Error Message:**
```json
{
    "exception": "dml_missing_record_exception",
    "errorcode": "invalidrecord",
    "message": "Can't find data record in database table course_sections."
}
```

**Solutions:**
1. **Verify course ID**: Use `local_labeleditor_get_sections` to confirm course exists
2. **Check section number**: Ensure the section number exists in the course
3. **Course access**: Verify your user has access to the course

### 6. Label Creates New Section Instead of Adding to Existing Section

**Problem:** API creates a new section with a high number (like 2457) instead of adding the label to the existing section.

**Cause:** This was a bug in versions prior to 1.1.2 where the API incorrectly used section ID instead of section number.

**Solution:** 
1. **Update to version 1.1.2 or later** - this bug has been fixed
2. **Clean up unwanted sections** created by the bug (manually in Moodle)
3. **Re-test** your API calls after updating

**Example of the issue:**
- You request `sectionnum=2` (section number 2)
- But a new section numbered `2457` gets created (using the section ID)
- **Fixed in v1.1.2**: Now correctly adds to existing section 2

### 7. Position Parameter Causes "Could not delete module" Error

**Error Message:**
```
Could not delete module from existing section
{
    "exception": "core\\exception\\moodle_exception",
    "errorcode": "error_creating_label",
    "message": "Error creating label: {$a}",
    "debuginfo": "Invalid course module ID"
}
```

**Cause:** This was a bug in versions prior to 1.1.3 where the positioning logic used an incorrect API call.

**Solution:** 
1. **Update to version 1.1.3 or later** - this positioning bug has been fixed
2. **The label is still created** even if positioning fails (check with find_labels)
3. **Positioning now works correctly** using proper sequence management

**Note:** In versions 1.1.3+, if positioning fails, the label is still created successfully but may appear at the end of the section instead of the specified position.

## Testing Your Setup

### Step 1: Test Basic Connectivity
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=core_webservice_get_site_info" \
     -d "moodlewsrestformat=json"
```

**Expected Result:** Site information without errors.

### Step 2: Test Section Retrieval
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_get_sections" \
     -d "moodlewsrestformat=json" \
     -d "courseid=YOUR_COURSE_ID"
```

**Expected Result:** Array of course sections with their details.

### Step 3: Test Label Creation
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=YOUR_COURSE_ID" \
     -d "sectionnum=0" \
     -d "name=Test Label" \
     -d "content=<p>This is a test label</p>" \
     -d "visible=1"
```

**Expected Result:** Success response with label details.

## Debugging Tips

### Enable Debug Mode
1. Go to **Site Administration > Development > Debugging**
2. Set **Debug messages** to "DEVELOPER"
3. Enable **Display debug messages**
4. Check error logs for detailed information

### Check Web Service Logs
1. Go to **Site Administration > Reports > Logs**
2. Filter by:
   - **Activity**: Web service
   - **User**: Your API user
3. Look for error details in the log entries

### Validate Parameters
Before making API calls, validate your parameters:

```bash
# Check if course exists
curl -d "wsfunction=core_course_get_courses_by_field" \
     -d "field=id" \
     -d "value=YOUR_COURSE_ID"

# Check user capabilities
curl -d "wsfunction=core_user_get_users_by_field" \
     -d "field=id" \
     -d "values[0]=YOUR_USER_ID"
```

## Common Fixes

### Fix 1: Reinstall Plugin
If functions are not recognized:
```bash
# In Moodle root directory
php admin/cli/uninstall_plugins.php --plugins=local_labeleditor --run
# Then reinstall the plugin
```

### Fix 2: Clear Caches
```bash
# Clear all caches
php admin/cli/purge_caches.php

# Or via web interface:
# Site Administration > Development > Purge all caches
```

### Fix 3: Reset Web Services
1. **Disable and re-enable** web services
2. **Regenerate** service tokens
3. **Reconfigure** external services

## API Parameter Reference

### create_label Parameters
| Parameter | Type | Required | Format | Example |
|-----------|------|----------|---------|---------|
| courseid | int | Yes | Integer | `courseid=379` |
| sectionnum | int | Yes | Integer (0,1,2...) | `sectionnum=2` |
| name | string | Yes | Text | `name=Welcome Message` |
| content | string | Yes | HTML | `content=<p>Hello</p>` |
| visible | int | No | 0 or 1 | `visible=1` |
| position | int | No | Integer | `position=0` |

### get_sections Parameters
| Parameter | Type | Required | Format | Example |
|-----------|------|----------|---------|---------|
| courseid | int | Yes | Integer | `courseid=379` |

## Contact Support

If you continue experiencing issues:

1. **Check Moodle version compatibility** (requires 4.5+)
2. **Verify PHP version** (requires 8.1+)
3. **Review server error logs**
4. **Test with minimal parameters first**
5. **Use debugging mode for detailed error information**

## Quick Reference Commands

### Working Example (Copy & Paste)
```bash
# Replace these values with your actual data:
MOODLE_URL="https://your-moodle-site.com"
TOKEN="your_actual_token_here"
COURSE_ID="your_course_id"

# Get sections first
curl -X POST "${MOODLE_URL}/webservice/rest/server.php" \
     -d "wstoken=${TOKEN}" \
     -d "wsfunction=local_labeleditor_get_sections" \
     -d "moodlewsrestformat=json" \
     -d "courseid=${COURSE_ID}"

# Create a label (use section number from above response)
curl -X POST "${MOODLE_URL}/webservice/rest/server.php" \
     -d "wstoken=${TOKEN}" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=${COURSE_ID}" \
     -d "sectionnum=0" \
     -d "name=Test Label" \
     -d "content=<h3>Welcome!</h3><p>This is a test label.</p>" \
     -d "visible=1"
```

This troubleshooting guide should help you resolve the most common issues when using the Label Editor Plugin API.
