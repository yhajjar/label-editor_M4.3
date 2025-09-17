# Label Editor Plugin - API Usage Guide

This guide provides comprehensive examples for using the Label Editor Plugin API, with a focus on the new label creation functionality.

## Quick Start

### 1. Get Course Sections
Before creating labels, you'll want to see what sections are available in a course:

```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_get_sections" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2"
```

**Response Example**:
```json
[
    {
        "id": 15,
        "section": 0,
        "name": "General",
        "summary": "",
        "visible": true,
        "labelcount": 1,
        "totalmodules": 3
    },
    {
        "id": 16,
        "section": 1,
        "name": "Week 1",
        "summary": "<p>Introduction to the course</p>",
        "visible": true,
        "labelcount": 0,
        "totalmodules": 2
    }
]
```

### 2. Create a Label in an Existing Section
Create a basic label in an existing section (section 1 in this example):

```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=1" \
     -d "name=Welcome" \
     -d "content=<h3>Welcome to Week 1</h3><p>Let's get started!</p>" \
     -d "visible=1"
```

**Note**: The label will be added to the existing section alongside any activities already present. Use the `position` parameter to control where in the section the label appears.

**Response Example**:
```json
{
    "success": true,
    "cmid": 45,
    "labelid": 12,
    "name": "Welcome",
    "sectionnum": 1,
    "sectionid": 16,
    "visible": true,
    "message": "Label created successfully.",
    "timestamp": 1642680000
}
```

## Advanced Usage Examples

### Creating Rich HTML Labels

#### Example 1: Course Introduction Label
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=0" \
     -d "name=Course Introduction" \
     -d "content=<div style=\"background-color: #e3f2fd; padding: 15px; border-radius: 5px;\"><h2 style=\"color: #1976d2;\">📚 Welcome to Advanced Web Development</h2><p><strong>Instructor:</strong> Dr. Smith</p><p><strong>Duration:</strong> 12 weeks</p><p>This course will cover modern web development techniques including HTML5, CSS3, JavaScript ES6+, and popular frameworks.</p></div>"
```

#### Example 2: Important Notice Label
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=1" \
     -d "name=Important Notice" \
     -d "content=<div style=\"background-color: #fff3e0; border-left: 4px solid #ff9800; padding: 10px;\"><h4 style=\"color: #e65100; margin-top: 0;\">⚠️ Important Notice</h4><p>Please complete all readings before attending the live session on Friday.</p><ul><li>Chapter 1: Introduction to HTML5</li><li>Chapter 2: CSS Grid and Flexbox</li></ul></div>"
```

#### Example 3: Section Divider Label
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=2" \
     -d "name=Learning Objectives" \
     -d "content=<hr style=\"border: 2px solid #4caf50; margin: 20px 0;\"><h3 style=\"text-align: center; color: #2e7d32;\">🎯 Learning Objectives</h3><div style=\"display: flex; justify-content: space-around; flex-wrap: wrap;\"><div style=\"flex: 1; min-width: 200px; margin: 10px; padding: 15px; background-color: #f1f8e9; border-radius: 8px;\"><h4>Knowledge</h4><p>Understand core concepts</p></div><div style=\"flex: 1; min-width: 200px; margin: 10px; padding: 15px; background-color: #f1f8e9; border-radius: 8px;\"><h4>Skills</h4><p>Apply practical techniques</p></div><div style=\"flex: 1; min-width: 200px; margin: 10px; padding: 15px; background-color: #f1f8e9; border-radius: 8px;\"><h4>Application</h4><p>Build real-world projects</p></div></div>"
```

### Positioning Labels

#### Create Label at Specific Position
```bash
# Create a label at position 2 in the section (third item)
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=1" \
     -d "name=Mid-Section Notice" \
     -d "content=<p style=\"text-align: center; font-style: italic;\">📍 You are halfway through this section</p>" \
     -d "position=2"
```

#### Create Hidden Label (for future use)
```bash
curl -X POST "https://your-moodle-site.com/webservice/rest/server.php" \
     -d "wstoken=YOUR_TOKEN" \
     -d "wsfunction=local_labeleditor_create_label" \
     -d "moodlewsrestformat=json" \
     -d "courseid=2" \
     -d "sectionnum=3" \
     -d "name=Future Content" \
     -d "content=<p>This content will be revealed later in the course.</p>" \
     -d "visible=0"
```

## n8n Integration Examples

### Basic n8n Workflow for Label Creation

#### Node 1: HTTP Request - Get Sections
```json
{
  "method": "POST",
  "url": "https://your-moodle-site.com/webservice/rest/server.php",
  "body": {
    "wstoken": "{{$credentials.moodle.token}}",
    "wsfunction": "local_labeleditor_get_sections",
    "moodlewsrestformat": "json",
    "courseid": "{{$json.courseid}}"
  }
}
```

#### Node 2: Code Node - Process Sections
```javascript
// Find the target section
const targetSectionNum = $input.first().json.target_section;
const sections = $input.first().json.sections;
const targetSection = sections.find(s => s.section === targetSectionNum);

return [{
  json: {
    courseid: $input.first().json.courseid,
    sectionnum: targetSectionNum,
    sectionid: targetSection.id,
    current_label_count: targetSection.labelcount
  }
}];
```

#### Node 3: HTTP Request - Create Label
```json
{
  "method": "POST",
  "url": "https://your-moodle-site.com/webservice/rest/server.php",
  "body": {
    "wstoken": "{{$credentials.moodle.token}}",
    "wsfunction": "local_labeleditor_create_label",
    "moodlewsrestformat": "json",
    "courseid": "{{$json.courseid}}",
    "sectionnum": "{{$json.sectionnum}}",
    "name": "{{$json.label_title}}",
    "content": "{{$json.html_content}}",
    "visible": true
  }
}
```

### Advanced n8n Workflow with Conditional Logic

#### Node 1: Webhook Trigger
Receives data like:
```json
{
  "courseid": 2,
  "section_type": "introduction",
  "content_data": {
    "title": "Week 1 Introduction",
    "description": "Welcome to the first week",
    "objectives": ["Learn HTML", "Understand CSS"]
  }
}
```

#### Node 2: Switch Node - Content Type
Routes based on `section_type`:
- `introduction` → Create welcome label
- `objectives` → Create objectives label  
- `notice` → Create notice label

#### Node 3a: Set Node - Introduction Template
```javascript
return [{
  json: {
    name: $json.content_data.title,
    content: `
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin: 10px 0;">
        <h2 style="margin-top: 0; color: white;">🚀 ${$json.content_data.title}</h2>
        <p style="font-size: 16px; line-height: 1.6;">${$json.content_data.description}</p>
        <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; margin-top: 15px;">
          <strong>Ready to begin? Let's dive in! 💪</strong>
        </div>
      </div>
    `
  }
}];
```

#### Node 3b: Set Node - Objectives Template
```javascript
const objectives = $json.content_data.objectives;
const objectivesList = objectives.map(obj => `<li>${obj}</li>`).join('');

return [{
  json: {
    name: "Learning Objectives",
    content: `
      <div style="border: 2px solid #4caf50; border-radius: 8px; padding: 15px; background-color: #f8fff8;">
        <h3 style="color: #2e7d32; margin-top: 0;">🎯 Learning Objectives</h3>
        <p>By the end of this section, you will be able to:</p>
        <ul style="color: #2e7d32;">
          ${objectivesList}
        </ul>
      </div>
    `
  }
}];
```

## Python Integration Examples

### Simple Label Creation Script
```python
import requests
import json

class MoodleLabelCreator:
    def __init__(self, moodle_url, token):
        self.moodle_url = moodle_url
        self.token = token
        self.api_url = f"{moodle_url}/webservice/rest/server.php"
    
    def get_sections(self, course_id):
        """Get all sections in a course"""
        data = {
            'wstoken': self.token,
            'wsfunction': 'local_labeleditor_get_sections',
            'moodlewsrestformat': 'json',
            'courseid': course_id
        }
        
        response = requests.post(self.api_url, data=data)
        return response.json()
    
    def create_label(self, course_id, section_num, name, content, visible=True, position=0):
        """Create a new label in a course section"""
        data = {
            'wstoken': self.token,
            'wsfunction': 'local_labeleditor_create_label',
            'moodlewsrestformat': 'json',
            'courseid': course_id,
            'sectionnum': section_num,
            'name': name,
            'content': content,
            'visible': visible,
            'position': position
        }
        
        response = requests.post(self.api_url, data=data)
        return response.json()
    
    def create_welcome_label(self, course_id, section_num, course_name, instructor):
        """Create a standardized welcome label"""
        content = f"""
        <div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
                    color: white; padding: 25px; border-radius: 12px; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0; color: white; font-size: 24px;">
                🎓 Welcome to {course_name}
            </h2>
            <p style="font-size: 16px; line-height: 1.6; margin-bottom: 15px;">
                Hello and welcome! I'm excited to have you in this course.
            </p>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; 
                        border-radius: 8px; margin-top: 20px;">
                <p style="margin: 0; font-weight: bold;">
                    👨‍🏫 Instructor: {instructor}
                </p>
            </div>
        </div>
        """
        
        return self.create_label(course_id, section_num, "Course Welcome", content)

# Usage example
creator = MoodleLabelCreator("https://your-moodle-site.com", "YOUR_TOKEN")

# Get sections first
sections = creator.get_sections(2)
print("Available sections:", json.dumps(sections, indent=2))

# Create welcome label
result = creator.create_welcome_label(2, 0, "Advanced Web Development", "Dr. Smith")
print("Label created:", json.dumps(result, indent=2))
```

### Batch Label Creation Script
```python
def create_course_structure(creator, course_id, structure):
    """Create multiple labels based on a course structure"""
    results = []
    
    for section_data in structure:
        section_num = section_data['section']
        
        for label_data in section_data['labels']:
            result = creator.create_label(
                course_id=course_id,
                section_num=section_num,
                name=label_data['name'],
                content=label_data['content'],
                visible=label_data.get('visible', True),
                position=label_data.get('position', 0)
            )
            results.append(result)
    
    return results

# Example course structure
course_structure = [
    {
        "section": 0,
        "labels": [
            {
                "name": "Course Introduction",
                "content": "<h2>Welcome to the Course</h2><p>This is the general section.</p>"
            }
        ]
    },
    {
        "section": 1,
        "labels": [
            {
                "name": "Week 1 Overview",
                "content": "<h3>Week 1: Getting Started</h3><p>Introduction to basic concepts.</p>"
            },
            {
                "name": "Learning Objectives",
                "content": "<h4>This week you will learn:</h4><ul><li>Concept A</li><li>Concept B</li></ul>"
            }
        ]
    }
]

# Create all labels
results = create_course_structure(creator, 2, course_structure)
```

## Error Handling

### Common Error Responses

#### Invalid Token
```json
{
    "exception": "moodle_exception",
    "errorcode": "invalidtoken",
    "message": "Invalid token - token not found"
}
```

#### Permission Denied
```json
{
    "exception": "required_capability_exception",
    "errorcode": "nopermissions",
    "message": "Sorry, but you do not currently have permissions to do that (Edit label content via API)."
}
```

#### Invalid Course/Section
```json
{
    "exception": "dml_missing_record_exception",
    "errorcode": "invalidrecord",
    "message": "Can't find data record in database table course_sections."
}
```

### Error Handling in Python
```python
def safe_create_label(creator, course_id, section_num, name, content):
    """Create label with error handling"""
    try:
        result = creator.create_label(course_id, section_num, name, content)
        
        if 'exception' in result:
            print(f"Error: {result['message']}")
            return None
        
        if result.get('success'):
            print(f"✅ Label '{name}' created successfully (ID: {result['cmid']})")
            return result
        else:
            print(f"❌ Failed to create label '{name}'")
            return None
            
    except requests.exceptions.RequestException as e:
        print(f"🌐 Network error: {e}")
        return None
    except Exception as e:
        print(f"💥 Unexpected error: {e}")
        return None
```

## Best Practices

### 1. Content Guidelines
- Use semantic HTML for better accessibility
- Include proper heading hierarchy (h1, h2, h3, etc.)
- Use inline CSS for styling (external CSS may not load)
- Test content in different screen sizes
- Include alt text for images

### 2. Performance Considerations
- Batch API calls when creating multiple labels
- Use appropriate section positioning
- Avoid overly complex HTML structures
- Optimize images before including in content

### 3. Security Best Practices
- Validate and sanitize all input content
- Use HTTPS for all API calls
- Store tokens securely
- Implement proper error handling
- Log API usage for monitoring

### 4. Maintenance Tips
- Use consistent naming conventions
- Document your label creation workflows
- Test in development environment first
- Keep backup of important label content
- Monitor API rate limits

This guide provides a comprehensive foundation for using the Label Editor Plugin's API effectively. Adapt these examples to your specific use cases and requirements.
