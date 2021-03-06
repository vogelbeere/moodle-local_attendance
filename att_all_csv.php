<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * attendance
 *
 * @package    local_attendance
 * @copyright  2017, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */


require_once('../../config.php');
require_once('./db_update.php');
require_once('./att_all_form.php');


require_login();
$context = context_system::instance();
require_capability('local/attendance:admin', $context);

$home = new moodle_url('/');
$url = $home . 'local/attendance/index.php';


$PAGE->set_pagelayout('standard');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('attendance_all', 'local_attendance') . ' (CSV)');

$message = '';

$mform = new att_all_form(null, array());

if ($mform->is_cancelled()) {
    redirect($url);
} 
else if ($mform_data = $mform->get_data()) {
	$attendances = get_att_all($mform_data->date_from, $mform_data->date_to); // Get all for selected dates
	//$attendances = get_att_all(); //debug
	if (empty($attendances)) {
		$message = get_string('no_attendance', 'local_attendance'). ': ' . date('d-m-Y', $mform_data->date_from) . ' and '. date('d-m-Y', $mform_data->date_to);
	} else {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=attendance_' . date('d-m-Y', $mform_data->date_from) . '_to_' . date('d-m-Y', $mform_data->date_to) . '.csv');
		
		$fp = fopen('php://output', 'w');
		fputcsv($fp, array('session date','calendar event id', 'course id', 'course name', 'student number','first name', 'last name','acronym','description'));

		foreach ($attendances as $attendance) {
			// session_date, ass.caleventid, c.idnumber as course_id, c.shortname as course_name, u.username as student_number, u.firstname, u.lastname, ast.acronym, ast.description
			$fields = array();
			$fields[0] = date('d-m-Y h:m', $attendance->sessdate);
			$fields[1] = $attendance->caleventid;
			$fields[2] = $attendance->idnumber;
			$fields[3] = $attendance->shortname;
			$fields[4] = $attendance->username;
			$fields[5] = $attendance->firstname;
			$fields[6] = $attendance->lastname;
			$fields[7] = $attendance->acronym;
			$fields[8] = $attendance->description;
			
			
			fputcsv($fp, $fields);
		}
		fclose($fp);
		
		exit();
	}
}
echo $OUTPUT->header();
	 
if ($message) {
    notice($message, $url);    
}
else {
    $mform->display();
}
//echo $mform_data->date_from . ' '. $mform_data->date_to;
echo $OUTPUT->footer();

exit();

?>	
