# Moodle 4.5 Label Editor Plugin

A Moodle local plugin that provides web service functions for editing label module content via API. This plugin is fully compatible with Moodle 4.5 and follows all current security and coding standards.

## Features

- **Create Labels**: Create new labels in specific course sections with positioning control
- **Update Label Content**: Modify label HTML content and names via web service API
- **Find Labels**: Search and retrieve labels within courses with filtering options
- **Get Sections**: Retrieve course section information with label counts
- **Moodle 4.5 Compatible**: Fully tested and compatible with Moodle 4.5
- **Security Compliant**: Follows Moodle security best practices and capability checks
- **GDPR Compliant**: Includes privacy provider for data protection compliance
- **Web Service Ready**: Pre-configured web service endpoints for easy integration

## Requirements

- Moodle 4.5.0 or higher
- PHP 8.1 or higher
- Web services enabled in Moodle
- REST protocol enabled

## Installation

### Step 1: Plugin Installation
1. Download or clone this plugin to your Moodle installation:
   ```bash
   cd /path/to/moodle/local/
   git clone https://github.com/yhajjar/label-editor.git labeleditor
   ```
   
2. Navigate to **Site Administration > Notifications** in Moodle
3. Complete the plugin installation process
4. Verify the plugin appears in **Site Administration > Plugins > Local plugins**

### Step 2: Web Service Configuration

#### Enable Web Services
1. Go to **Site Administration > Advanced features**
2. Enable **Web services**
3. Save changes

#### Enable REST Protocol
1. Navigate to **Site Administration > Plugins > Web services > Manage protocols**
2. Enable **REST protocol**

#### Configure the Service
1. Go to **Site Administration > Plugins > Web services > External services**
2. Find "Label Editor Service" and click **Edit**
3. Ensure it's enabled
4. Add authorized users who can use this service

#### Create Service Token
1. Navigate to **Site Administration > Plugins > Web services > Manage tokens**
2. Create a new token for your API user
3. Select "Label Editor Service" as the service
4. Save the token (you'll need this for API calls)

### Step 3: User and Role Configuration

#### Create API User (Recommended)
1. Create a dedicated user account for API access
2. Assign appropriate role with label editing capabilities

#### Configure Capabilities
Ensure the API user has the following capabilities:
- `webservice/rest:use` - Use REST web service
- `local/labeleditor:edit` - Edit label content via API
- `local/labeleditor:view` - View and search labels via API
- `moodle/course:view` - View courses

## API Endpoints

### Update Label Content
**Endpoint**: `POST /webservice/rest/server.php`

**Parameters**:
- `wstoken`: Your web service token
- `wsfunction`: `local_labeleditor_update_label`
- `moodlewsrestformat`: `json`
- `cmid`: Course module ID of the label
- `content`: New HTML content for the label
- `name` (optional): New name for the label

**Example**:
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_update_label" \
     -d "moodlewsrestformat=json" \
     -d "cmid=123" \
     -d "content=<p>Updated label content</p>" \
     -d "name=Updated Label Name"
```

### Find Labels in Course
**Endpoint**: `POST /webservice/rest/server.php`

**Parameters**:
- `wstoken`: Your web service token
- `wsfunction`: `local_labeleditor_find_labels`
- `moodlewsrestformat`: `json`
- `courseid`: Course ID to search in
- `namefilter` (optional): Filter labels by name
- `sectionid` (optional): Specific section ID

**Example**:
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_find_labels" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "namefilter=introduction"
```

### Create Label in Section
**Endpoint**: `POST /webservice/rest/server.php`

**Description**: Creates a new label in any existing course section. The label will be added to the specified section alongside any existing activities.

**Parameters**:
- `wstoken`: Your web service token
- `wsfunction`: `local_labeleditor_create_label`
- `moodlewsrestformat`: `json`
- `courseid`: Course ID
- `sectionnum`: Section number (0, 1, 2, etc.) - the section must already exist
- `name`: Label name/title
- `content`: HTML content for the label
- `visible` (optional): Label visibility (1=visible, 0=hidden, default: 1)
- `position` (optional): Position in section (0=end, 1=first, 2=second, etc., default: 0)

**Example - Add label to existing section:**
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=1" \
     -d "name=Welcome Message" \
     -d "content=<h3>Welcome to Module 1</h3><p>This section covers the fundamentals.</p>" \
     -d "visible=1"
```

**Example - Add label at specific position in section:**
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=2" \
     -d "name=Section Introduction" \
     -d "content=<p>This label will appear as the first item in the section</p>" \
     -d "position=1" \
     -d "visible=1"
```

### Get Course Sections
**Endpoint**: `POST /webservice/rest/server.php`

**Parameters**:
- `wstoken`: Your web service token
- `wsfunction`: `local_labeleditor_get_sections`
- `moodlewsrestformat`: `json`
- `courseid`: Course ID

**Example**:
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_get_sections" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2"
```

## Testing the Installation

Test the plugin with a simple API call:

```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_find_labels" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2"
```

## Integration Examples

### n8n Integration
This plugin is designed to work seamlessly with n8n workflows. Use the HTTP Request node with the following configuration:

- **Method**: POST
- **URL**: `https://your-moodle-site.com/webservice/rest/server.php`
- **Body**: Form data with the required parameters

### Python Integration
```python
import requests

def update_label(token, moodle_url, cmid, content, name=None):
    data = {
        'wstoken': token,
        'wsfunction': 'local_labeleditor_update_label',
        'moodlewsrestformat': 'json',
        'cmid': cmid,
        'content': content
    }
    
    if name:
        data['name'] = name
    
    response = requests.post(f"{moodle_url}/webservice/rest/server.php", data=data)
    return response.json()
```

## Security Considerations

- Always use HTTPS for API calls
- Store tokens securely and rotate them regularly
- Use dedicated API users with minimal required permissions
- Monitor API usage through Moodle logs
- Validate and sanitize all input data

## Troubleshooting

### Common Issues

1. **"Invalid token" error**
   - Verify the token is correct and active
   - Check that the user has the required capabilities
   - Ensure the service is enabled

2. **"Function not found" error**
   - Verify the plugin is installed correctly
   - Check that web services are enabled
   - Ensure the function is added to the service

3. **Permission denied**
   - Check user capabilities
   - Verify context permissions for the specific course/module

### Debug Mode
Enable web service debugging in Moodle:
1. Go to **Site Administration > Development > Debugging**
2. Set debug level to "DEVELOPER"
3. Check error logs for detailed information

## Support

For issues and questions:
- Check Moodle logs for detailed error messages
- Verify all installation steps were completed
- Test with a simple API call first
- Review capability assignments

## License

This plugin is licensed under the GNU GPL v3 or later.

## Changelog

### Version 1.0.0 (2025-07-20)
- Initial release
- Moodle 4.5 compatibility
- Full web service API implementation
- GDPR compliance with privacy provider
- Comprehensive security implementation
