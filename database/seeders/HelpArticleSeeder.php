<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HelpArticle;

class HelpArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = [
            // ==================== FAQ ====================
            [
                'title' => 'How do I view my assigned tasks?',
                'category' => 'faq',
                'icon' => 'fa-tasks',
                'order' => 1,
                'content' => 'To view your assigned tasks:

1. Navigate to "My Tasks" from the sidebar menu
2. You will see a list of all maintenance tasks assigned to you
3. Tasks are organized by status: Pending, In Progress, and Completed
4. Click on any task to view full details
5. Use filters to sort by status, priority, or date

You can also see recent tasks on your dashboard for quick access.',
            ],
            [
                'title' => 'How do I update the status of my task?',
                'category' => 'faq',
                'icon' => 'fa-edit',
                'order' => 2,
                'content' => 'To update your task status:

1. Go to "My Tasks" and click on the task you want to update
2. On the task detail page, you will see the current status
3. Click the "Update Status" button
4. Select the new status from the dropdown:
   - Pending: Task is waiting to be started
   - In Progress: You are currently working on this task
   - Completed: Task has been finished
5. Optionally add notes about your progress
6. Click "Save" to update the status

Note: Once marked as completed, you may need supervisor approval.',
            ],
            [
                'title' => 'How do I create a work report?',
                'category' => 'faq',
                'icon' => 'fa-file-alt',
                'order' => 3,
                'content' => 'Creating a work report:

1. Navigate to "My Work Reports" in the sidebar
2. Click the "Create New Report" button
3. Select the maintenance job/task you completed
4. Fill in the required information:
   - Work performed description
   - Hours spent
   - Parts used (if any)
   - Issues encountered
   - Photos or attachments (optional)
5. Review your report for accuracy
6. Click "Submit Report" to send for approval

Your supervisor will review and approve the report.',
            ],
            [
                'title' => 'What should I do if I encounter an urgent issue?',
                'category' => 'faq',
                'icon' => 'fa-exclamation-triangle',
                'order' => 4,
                'content' => 'If you encounter an urgent issue during maintenance:

1. Immediately stop work if the issue poses a safety risk
2. Go to "Urgent Alerts" in the sidebar
3. Click "Create Urgent Alert"
4. Select the priority level (Critical, Urgent, or High)
5. Provide detailed information about the issue
6. Add photos if possible
7. Submit the alert

Your supervisor will be notified immediately and will respond based on the priority level. For critical safety issues, also contact your supervisor directly.',
            ],
            [
                'title' => 'How can I change my password?',
                'category' => 'faq',
                'icon' => 'fa-key',
                'order' => 5,
                'content' => 'To change your password:

1. Click on "My Profile" in the sidebar
2. Scroll down to the "Change Password" section
3. Enter your current password
4. Enter your new password
5. Confirm your new password
6. Click "Update Password"

Password requirements:
- Minimum 8 characters
- Must include at least one uppercase letter
- Must include at least one number
- Must include at least one special character

If you forgot your password, contact your administrator.',
            ],

            // ==================== SOP ====================
            [
                'title' => 'SOP: Daily Equipment Inspection',
                'category' => 'sop',
                'icon' => 'fa-clipboard-check',
                'order' => 1,
                'content' => 'STANDARD OPERATING PROCEDURE
Daily Equipment Inspection

OBJECTIVE:
To ensure all warehouse equipment is in safe operating condition before daily use.

SCOPE:
All maintenance technicians must perform this inspection at the start of their shift.

PROCEDURE:

1. PREPARATION (5 minutes)
   - Review equipment logs from previous shift
   - Gather inspection checklist and tools
   - Wear appropriate PPE (safety glasses, gloves, steel-toe boots)

2. VISUAL INSPECTION (15 minutes)
   - Check for any visible damage or wear
   - Inspect hydraulic lines for leaks
   - Examine electrical connections
   - Look for loose bolts or fasteners
   - Check fluid levels (oil, coolant, hydraulic fluid)

3. FUNCTIONAL TEST (10 minutes)
   - Test emergency stop buttons
   - Verify all safety guards are in place
   - Check warning lights and alarms
   - Test basic movements and operations
   - Listen for unusual noises or vibrations

4. DOCUMENTATION (5 minutes)
   - Record all findings in the maintenance log
   - Report any issues immediately to supervisor
   - Tag equipment as "OUT OF SERVICE" if unsafe
   - Update status in the maintenance system

5. FOLLOW-UP
   - Schedule repairs for any identified issues
   - Create work orders as needed
   - Notify operations team of equipment status

SAFETY NOTES:
- Never operate equipment that fails inspection
- Report all safety concerns immediately
- Do not remove safety guards or devices

REFERENCES:
- Equipment Operation Manual
- Safety Guidelines Section 3.2
- Maintenance Log Template',
            ],
            [
                'title' => 'SOP: Preventive Maintenance Checklist',
                'category' => 'sop',
                'icon' => 'fa-tools',
                'order' => 2,
                'content' => 'STANDARD OPERATING PROCEDURE
Preventive Maintenance Checklist

OBJECTIVE:
To maintain equipment reliability and prevent unexpected breakdowns through systematic preventive maintenance.

FREQUENCY:
Monthly or as specified in equipment manual

PROCEDURE:

1. PRE-MAINTENANCE PREPARATION
   - Review equipment service history
   - Gather required tools and spare parts
   - Lockout/Tagout equipment following safety procedures
   - Wear appropriate PPE
   - Inform operations team of maintenance window

2. LUBRICATION (30 minutes)
   - Apply grease to all grease points
   - Check and top up oil levels
   - Inspect for oil leaks
   - Verify proper lubrication of moving parts
   - Document lubricant types and quantities used

3. MECHANICAL INSPECTION (45 minutes)
   - Check belt tension and condition
   - Inspect chains for wear and proper tension
   - Examine bearings for noise or heat
   - Check alignment of moving components
   - Tighten all bolts and fasteners
   - Inspect couplings and connections

4. ELECTRICAL SYSTEM (30 minutes)
   - Inspect wiring for damage or wear
   - Check connections for tightness
   - Test motor insulation resistance
   - Verify proper grounding
   - Clean electrical panels and components
   - Test safety interlocks and emergency stops

5. HYDRAULIC/PNEUMATIC SYSTEMS (30 minutes)
   - Inspect hoses for cracks or leaks
   - Check fitting tightness
   - Test pressure settings
   - Inspect cylinders for leaks
   - Clean or replace filters
   - Check accumulator pressure

6. CLEANING AND HOUSEKEEPING (20 minutes)
   - Remove debris and buildup
   - Clean cooling fans and vents
   - Wipe down exterior surfaces
   - Organize tools and spare parts
   - Clean work area

7. DOCUMENTATION (15 minutes)
   - Complete maintenance checklist
   - Update equipment service log
   - Record parts replaced
   - Note any abnormal findings
   - Create work orders for future repairs
   - Update system status

8. POST-MAINTENANCE TESTING
   - Remove lockout/tagout devices
   - Perform functional test
   - Verify all safety features
   - Return equipment to service
   - Brief operations team

CRITICAL SAFETY REMINDERS:
- Always follow lockout/tagout procedures
- Never bypass safety devices
- Use proper tools for each task
- Report all safety concerns immediately

PARTS TYPICALLY REQUIRED:
- Lubricants (grease, oil)
- Filters (air, oil, hydraulic)
- Belts and chains (as needed)
- Fasteners and hardware

APPROVAL:
All preventive maintenance must be signed off by the technician and verified by the supervisor.',
            ],
            [
                'title' => 'SOP: Emergency Shutdown Procedure',
                'category' => 'sop',
                'icon' => 'fa-power-off',
                'order' => 3,
                'content' => 'STANDARD OPERATING PROCEDURE
Emergency Shutdown Procedure

OBJECTIVE:
To safely shut down equipment in emergency situations to protect personnel and equipment.

WHEN TO USE:
- Equipment malfunction
- Safety hazard detected
- Fire or smoke
- Unusual noises or vibrations
- Leaking hazardous materials
- Any situation that poses immediate danger

PROCEDURE:

1. IMMEDIATE ACTION (0-30 seconds)
   - Press the EMERGENCY STOP button
   - Alert nearby personnel to evacuate if necessary
   - Call for help if someone is injured
   - If safe to do so, isolate power source

2. SECURE THE AREA (30 seconds - 2 minutes)
   - Establish a safety perimeter
   - Post warning signs or barriers
   - Prevent unauthorized access
   - Account for all personnel

3. ASSESSMENT (2-5 minutes)
   - Identify the nature of the emergency
   - Determine if additional help is needed
   - Call emergency services if required (fire, medical, etc.)
   - Notify supervisor immediately

4. INITIAL RESPONSE (5-15 minutes)
   - If safe: Turn off main power switch
   - Close relevant valves (gas, water, hydraulic, etc.)
   - Activate fire suppression if needed
   - Contain any spills using appropriate materials
   - Document the situation with photos if safe

5. REPORTING (15-30 minutes)
   - Contact maintenance supervisor
   - File incident report in the system
   - Document:
     * Time of incident
     * Equipment involved
     * Nature of emergency
     * Actions taken
     * Personnel involved
     * Witness statements
   - Tag equipment as "OUT OF SERVICE - DO NOT OPERATE"

6. INVESTIGATION
   - Do not attempt to restart equipment
   - Preserve evidence for investigation
   - Cooperate with safety team
   - Participate in root cause analysis

7. FOLLOW-UP
   - Attend safety debriefing
   - Implement corrective actions
   - Equipment remains locked out until:
     * Proper investigation is completed
     * Repairs are made and verified
     * Supervisor approval is obtained

CRITICAL SAFETY RULES:
- Personal safety comes first - evacuate if in doubt
- Never restart equipment without supervisor approval
- Do not remove emergency tags or locks
- Report all incidents, no matter how minor

EMERGENCY CONTACTS:
- Emergency Services: 911
- Maintenance Supervisor: [Contact in system]
- Safety Manager: [Contact in system]
- Facility Manager: [Contact in system]

POST-INCIDENT REQUIREMENTS:
- Complete detailed incident report
- Attend safety review meeting
- Follow all corrective actions
- Receive retraining if required',
            ],
            [
                'title' => 'SOP: Spare Parts Inventory Check',
                'category' => 'sop',
                'icon' => 'fa-box',
                'order' => 4,
                'content' => 'STANDARD OPERATING PROCEDURE
Spare Parts Inventory Check

OBJECTIVE:
To maintain adequate spare parts inventory and ensure critical components are available when needed.

FREQUENCY:
Weekly or as assigned

PROCEDURE:

1. PREPARATION (10 minutes)
   - Access inventory system
   - Print current inventory list
   - Gather counting tools (scanner, clipboard)
   - Review recent usage reports

2. PHYSICAL COUNT (60 minutes)
   - Verify quantity of each part
   - Check part condition (no damage or rust)
   - Verify part numbers match system records
   - Note any discrepancies
   - Identify slow-moving or obsolete items

3. CRITICAL PARTS VERIFICATION (20 minutes)
   - Verify critical spare parts are in stock:
     * Bearings
     * Seals and gaskets
     * Filters (oil, air, hydraulic)
     * Belts and chains
     * Electrical components
     * Hydraulic hoses and fittings
   - Check minimum stock levels
   - Identify items below reorder point

4. ORGANIZATION AND STORAGE (30 minutes)
   - Ensure parts are properly labeled
   - Verify storage location matches system
   - Check storage conditions (temperature, humidity)
   - Organize parts by category
   - Ensure FIFO (First In, First Out) rotation

5. SYSTEM UPDATE (20 minutes)
   - Update inventory quantities in system
   - Adjust for any discrepancies
   - Create purchase requests for low stock items
   - Flag damaged or obsolete parts
   - Document any issues found

6. REPORTING (10 minutes)
   - Generate inventory status report
   - Submit to supervisor
   - Highlight critical shortages
   - Recommend actions for obsolete items

INVENTORY MANAGEMENT TIPS:
- Keep critical parts at 2x minimum usage
- Rotate stock to prevent deterioration
- Store parts in proper conditions
- Label everything clearly
- Keep system records updated

COMMON ISSUES TO WATCH FOR:
- Parts stored in wrong location
- Damaged packaging
- Expired shelf-life items
- Unauthorized removal
- Missing labels or part numbers

DOCUMENTATION:
- Inventory Count Sheet
- Discrepancy Report
- Purchase Request Form
- Storage Location Map',
            ],

            // ==================== TUTORIALS ====================
            [
                'title' => 'Tutorial: Getting Started with the System',
                'category' => 'tutorial',
                'icon' => 'fa-play-circle',
                'order' => 1,
                'content' => 'TUTORIAL: Getting Started with the Maintenance System

Welcome to the Warehouse Maintenance Management System! This tutorial will help you get started.

STEP 1: LOGGING IN
- Open your web browser
- Navigate to the system URL
- Enter your username and password
- Click "Login"
- You will be directed to your dashboard

STEP 2: UNDERSTANDING YOUR DASHBOARD
Your dashboard shows:
- Active urgent alerts (if any)
- Pending tasks count
- Tasks in progress
- Completed tasks this month
- Recent tasks list
- Recent work reports
- Your performance metrics

STEP 3: NAVIGATING THE SIDEBAR MENU
The left sidebar contains:
- Dashboard: Your home page
- Urgent Alerts: Critical issues requiring immediate attention
- My Tasks: All maintenance tasks assigned to you
- My Work Reports: Submit and view your work reports
- Help & Support: Access FAQs, SOPs, and tutorials (you are here!)
- My Profile: Update your personal information

STEP 4: VIEWING YOUR TASKS
1. Click "My Tasks" in the sidebar
2. You will see all assigned tasks
3. Use filters to sort by status
4. Click any task to see full details

STEP 5: UPDATING TASK STATUS
1. Open a task from "My Tasks"
2. Click "Update Status" button
3. Select new status (Pending, In Progress, Completed)
4. Add optional notes
5. Click "Save"

STEP 6: CREATING A WORK REPORT
1. Click "My Work Reports"
2. Click "Create New Report"
3. Select the maintenance job
4. Fill in all required fields
5. Upload photos if needed
6. Click "Submit Report"

STEP 7: RESPONDING TO URGENT ALERTS
1. Check "Urgent Alerts" regularly
2. Open any alert assigned to you
3. Update status as you respond
4. Document your actions
5. Close alert when resolved

TIPS FOR SUCCESS:
- Check your dashboard daily
- Update task status regularly
- Submit work reports promptly
- Respond to urgent alerts quickly
- Keep accurate records
- Ask for help when needed

NEXT STEPS:
- Explore the system features
- Read relevant SOPs
- Complete your first task
- Submit your first work report

If you need help, contact your supervisor or check the FAQ section.',
            ],
            [
                'title' => 'Tutorial: Creating Quality Work Reports',
                'category' => 'tutorial',
                'icon' => 'fa-file-invoice',
                'order' => 2,
                'content' => 'TUTORIAL: Creating Quality Work Reports

A good work report is detailed, accurate, and helps track maintenance history. Follow this guide to create excellent reports.

WHY WORK REPORTS MATTER:
- Document what was done
- Track parts and time used
- Create maintenance history
- Support warranty claims
- Help plan future maintenance
- Provide accountability

BEFORE YOU START:
- Have all information ready
- Take photos of work performed
- Collect part numbers used
- Note actual time spent
- Document any issues

STEP-BY-STEP GUIDE:

1. SELECT THE CORRECT JOB
- Choose the specific maintenance task
- Verify machine/equipment name
- Confirm work order number

2. DESCRIBE WORK PERFORMED
Good description includes:
- What you did (specific actions)
- Why you did it (reason/issue)
- How you did it (methods used)
- Results observed

Example of GOOD description:
"Replaced worn conveyor belt (Part #CB-2045). Belt showed severe cracking and fraying on edges. Removed old belt, cleaned pulleys, installed new belt, adjusted tension to 50 lbs per spec. Tested for 30 minutes - operating smoothly with no slipping."

Example of POOR description:
"Fixed belt"

3. RECORD TIME ACCURATELY
- Note start and end time
- Include breaks if over 1 hour
- Be honest about time spent
- Include travel time if applicable

4. LIST ALL PARTS USED
For each part:
- Part number
- Part description
- Quantity used
- Location taken from

Example:
- Part #CB-2045, Conveyor Belt 20ft, Qty: 1, Location: Shelf A-12
- Part #FL-350, Hydraulic Fluid 1qt, Qty: 2, Location: Fluid Storage

5. DOCUMENT ISSUES AND FINDINGS
Report:
- Additional problems discovered
- Root cause if identified
- Recommendations for future
- Safety concerns
- Parts needed for next service

6. ATTACH PHOTOS
Take photos of:
- Problem area (before)
- Work in progress
- Completed work (after)
- Part numbers of items used
- Any concerns found

Photo tips:
- Use good lighting
- Take multiple angles
- Include reference (ruler, hand)
- Capture important details clearly

7. ADD RECOMMENDATIONS
Suggest:
- Follow-up actions needed
- Parts to stock
- Schedule next service
- Improvements possible

8. REVIEW BEFORE SUBMITTING
Check:
- All required fields completed
- Spelling and grammar
- Accuracy of information
- Photos attached
- Parts list complete

9. SUBMIT REPORT
- Click "Submit Report"
- Report goes to supervisor for approval
- You receive confirmation
- Report added to equipment history

COMMON MISTAKES TO AVOID:
- Vague descriptions
- Missing parts information
- Inaccurate time reporting
- No photos
- Submitting incomplete reports
- Not mentioning additional issues found

REPORT APPROVAL PROCESS:
1. You submit report
2. Supervisor reviews
3. May request changes/clarification
4. Approved or returned for revision
5. Approved reports locked and archived

BEST PRACTICES:
- Submit reports same day
- Be thorough but concise
- Use technical terms correctly
- Include all relevant details
- Be honest and accurate
- Proofread before submitting

Remember: Your work reports become permanent records and may be reviewed years later. Make them count!',
            ],
            [
                'title' => 'Tutorial: Using Filters and Search',
                'category' => 'tutorial',
                'icon' => 'fa-filter',
                'order' => 3,
                'content' => 'TUTORIAL: Using Filters and Search Features

Learn how to quickly find tasks, reports, and information using the system\'s search and filter tools.

FILTERING TASKS:

1. Basic Status Filter
   - Go to "My Tasks"
   - Click on status tabs: All, Pending, In Progress, Completed
   - Tasks instantly filter by selected status

2. Date Range Filter
   - Click "Filter" button
   - Select start date
   - Select end date
   - Click "Apply"
   - View tasks within date range

3. Priority Filter
   - Use priority dropdown
   - Select: All, High, Medium, Low
   - Tasks filter by priority level

4. Machine/Equipment Filter
   - Use machine dropdown
   - Select specific machine
   - See only tasks for that machine

SEARCHING FOR TASKS:

1. Quick Search
   - Use search box at top
   - Type keywords (machine name, description)
   - Results appear as you type
   - Click result to open

2. Advanced Search
   - Click "Advanced Search"
   - Fill in multiple criteria:
     * Status
     * Date range
     * Machine
     * Priority
     * Assigned by
   - Click "Search"
   - View filtered results

FILTERING WORK REPORTS:

1. By Status
   - Draft: Reports not yet submitted
   - Submitted: Awaiting approval
   - Approved: Completed reports
   - Revision Needed: Requires changes

2. By Date
   - This Week
   - This Month
   - Last Month
   - Custom Range

3. By Machine
   - Select specific equipment
   - View all reports for that machine
   - Track maintenance history

SEARCH TIPS:

1. Use Specific Keywords
   - Machine names: "Conveyor 3"
   - Part numbers: "CB-2045"
   - Problem types: "leak", "noise", "vibration"

2. Use Partial Words
   - "conv" finds "conveyor"
   - "hydr" finds "hydraulic"
   - System suggests matches

3. Combine Filters
   - Status + Date Range
   - Machine + Priority
   - Multiple criteria for precise results

4. Save Common Searches
   - Use "Save Filter" button
   - Name your filter
   - Quick access next time

SEARCHING HELP ARTICLES:

1. From Help & Support page
2. Use search bar at top
3. Enter keywords or questions
4. Browse results by category
5. Click article to read

Search Examples:
- "how to create report"
- "preventive maintenance"
- "emergency shutdown"
- "change password"

SORTING RESULTS:

Click column headers to sort:
- Date (newest/oldest)
- Priority (high to low)
- Status (alphabetical)
- Machine name (A-Z)

EXPORTING DATA:

1. Apply desired filters
2. Click "Export" button
3. Choose format (PDF, Excel)
4. Download file
5. Use for reporting or analysis

KEYBOARD SHORTCUTS:

- Ctrl+F: Quick search
- Esc: Clear filters
- Enter: Apply filters
- Tab: Move between fields

COMMON SEARCH SCENARIOS:

Find today\'s tasks:
- Filter by status: Pending or In Progress
- Date: Today

Find overdue tasks:
- Filter by date: Before today
- Status: Not completed

Find specific machine history:
- Select machine from dropdown
- View all tasks and reports

Track your monthly work:
- Date: This month
- View all your tasks and reports

TROUBLESHOOTING:

No results found?
- Check spelling
- Try broader search terms
- Remove some filters
- Check date range

Too many results?
- Add more filters
- Use more specific keywords
- Narrow date range

System slow?
- Clear old filters
- Reduce date range
- Use more specific search terms

MOBILE TIPS:

- Swipe to see filter options
- Tap filter icon to access
- Use voice search if available
- Results optimized for mobile

Mastering search and filters will save you time and help you work more efficiently!',
            ],

            // ==================== DOCUMENTATION ====================
            [
                'title' => 'System Overview and Features',
                'category' => 'documentation',
                'icon' => 'fa-book-open',
                'order' => 1,
                'content' => 'SYSTEM OVERVIEW AND FEATURES
Warehouse Maintenance Management System

INTRODUCTION:
This system is designed to streamline maintenance operations, track work performance, and ensure equipment reliability in your warehouse facility.

KEY FEATURES:

1. TASK MANAGEMENT
- View all assigned maintenance tasks
- Update task status in real-time
- Track task history and completion
- Receive notifications for new tasks
- Filter and search tasks easily

2. WORK REPORTING
- Create detailed work reports
- Upload photos and attachments
- Track time and parts used
- Submit for supervisor approval
- Access historical reports

3. URGENT ALERTS
- Create critical alerts
- Set priority levels
- Fast-track urgent issues
- Immediate supervisor notification
- Track alert resolution

4. DASHBOARD
- Real-time status overview
- Performance metrics
- Quick access to recent items
- Important alerts and notifications
- Activity summary

5. HELP & SUPPORT
- Searchable FAQ database
- Standard Operating Procedures
- Video tutorials
- System documentation
- Quick reference guides

USER ROLES:

MAINTENANCE TECHNICIAN (Your Role):
- View assigned tasks
- Update task status
- Create work reports
- Create urgent alerts
- Access help resources
- Manage personal profile

SUPERVISOR:
- All technician capabilities
- Assign tasks to technicians
- Approve work reports
- Monitor team performance
- Generate reports

ADMINISTRATOR:
- All system capabilities
- User management
- System configuration
- Data management
- System maintenance

SYSTEM MODULES:

1. Dashboard Module
- Overview of all activities
- Quick statistics
- Recent tasks and reports
- Performance indicators

2. Task Management Module
- Task list and details
- Status updates
- Task history
- Filtering and search

3. Work Report Module
- Report creation
- Photo uploads
- Parts tracking
- Time logging
- Approval workflow

4. Urgent Alert Module
- Alert creation
- Priority setting
- Status tracking
- Resolution documentation

5. Help & Support Module
- FAQ database
- SOP library
- Tutorial videos
- Search functionality
- Category browsing

TECHNICAL SPECIFICATIONS:

SUPPORTED BROWSERS:
- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari

MOBILE SUPPORT:
- Responsive design
- Touch-friendly interface
- Camera integration for photos
- Works on smartphones and tablets

FILE UPLOAD SUPPORT:
- Images: JPG, PNG, GIF
- Documents: PDF
- Maximum file size: 10MB
- Multiple files per report

SYSTEM REQUIREMENTS:
- Internet connection
- Modern web browser
- Minimum screen resolution: 1024x768
- Camera (for photo uploads)

DATA SECURITY:

1. User Authentication
- Secure login system
- Password encryption
- Session management
- Auto-logout after inactivity

2. Data Protection
- Regular backups
- Encrypted communications
- Access controls
- Audit trails

3. Privacy
- Personal data protection
- Role-based access
- Confidential information security

SYSTEM AVAILABILITY:
- 24/7 access
- Regular maintenance windows
- Automatic updates
- 99.9% uptime target

SUPPORT:
- In-app help system
- Email support
- Phone support (emergency)
- Training materials

FUTURE ENHANCEMENTS:
- Mobile app (iOS/Android)
- Barcode scanning
- Predictive maintenance
- Advanced analytics
- Integration with other systems

For technical support or questions, contact your system administrator.',
            ],
        ];

        foreach ($articles as $article) {
            HelpArticle::create($article);
        }

        $this->command->info('Help articles seeded successfully!');
    }
}
