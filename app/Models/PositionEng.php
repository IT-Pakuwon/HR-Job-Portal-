<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionEng extends Model
{
    protected $connection = 'mysql4';
    protected $table = "position";

    protected $fillable = [
        'position_username',
        'position_name',
        'company_id',
        'Administrator',
        'Schedule_Management',
        'Schedule_History',
        'PM_Schedule',
        'Autotext1',
        'Report_Search',
        'Unapprove_Report',
        'Report_Form',
        'Asset_Setup',
        'PM_Outstanding',
        'Warranty_Status',
        'Asset_Maps',
        'Disposal_Asset',
        'Activity_By_Section',
        'Activity_By_Technician',
        'Activity_Log',
        'Approve_List',
        'Timeline_Report',
        'Work_Category_Profile',
        'Section_WO_Activity',
        'Tracking_WO_Complete',
        'Asset_Condition',
        'KPI_Report_Technician',
        'KPI_Report_Supervisor',
        'Executive_Summary_Report',
        'Project_Report',
        'Notice',
        'Contact',
        'Department',
        'Employee',
        'Employee_Timetable',
        'Work_Category',
        'Asset_Category',
        'Attendance_Location',
        'Beacon_Management',
        'Asset_Map',
        'Permission',
        'Preferences',
        'Energy',
        'active_status',
        'Last_update_By',


    ];
}
