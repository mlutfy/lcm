<?php

/*
	This file is part of the Legal Case Management System (LCM).
	(C) 2004-2005 Free Software Foundation, Inc.

	This program is free software; you can redistribute it and/or modify it
	under the terms of the GNU General Public License as published by the
	Free Software Foundation; either version 2 of the License, or (at your
	option) any later version.

	This program is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
	or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License along
	with this program; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA  02111-1307, USA

	$Id$
*/

include('inc/inc.php');

$app = intval($_GET['app']);

$q = "SELECT lcm_app.*,lcm_author.name_first,lcm_author.name_middle,lcm_author.name_last,lcm_case.title AS case_title
	FROM lcm_app, lcm_author_app, lcm_author
	LEFT JOIN lcm_case ON (lcm_case.id_case = lcm_app.id_case)
	WHERE (lcm_app.id_app=$app
		AND lcm_author_app.id_app=$app
		AND lcm_author_app.id_author=" . $GLOBALS['author_session']['id_author'] . "
		AND lcm_app.id_author=lcm_author.id_author)";
$result = lcm_query($q);

if ($row = lcm_fetch_array($result)) {
	lcm_page_start(_T('title_app_view') . ' ' . $row['title']); // TRAD

	echo '<fieldset class="info_box">' . "\n";
	echo '<p class="normal_text">' . "\n";
	
	echo _Ti('app_input_title') . $row['title'] . "<br />\n";
	echo _Ti('app_input_type') . $row['type'] . "<br />\n";
	echo _Ti('app_input_description') . nl2br($row['description']) . "<br />\n";

	echo "<br />\n";
	echo _Ti('time_input_date_start') . format_date($row['start_time'], 'short');
	$year_for_cal = "&annee=" . annee($row['start_time'])  // year
		. "&mois=" . mois($row['start_time'])  // month
		. "&jour=" . journum($row['start_time']); // day

	echo ' ' . http_href_img("calendar.php?type=jour" . $year_for_cal, 'cal-today', '', _T('app_info_see_cal_for_day_tooltip'));
	echo "<br />\n";


	$end_time = vider_date($row['end_time']);
	$reminder = vider_date($row['reminder']);
	if ($prefs['time_intervals'] == 'absolute') {
		echo _Ti('time_input_date_end') . format_date($row['end_time'], 'short') . "<br />\n";
		echo _Ti('app_input_reminder') . format_date($row['reminder'], 'short') . "<br />\n";
	} else {
		$duration = ($end_time ? strtotime($row['end_time']) - strtotime($row['start_time']) : 0);
		echo _Ti('app_input_time_length') . format_time_interval($duration,($prefs['time_intervals_notation'] == 'hours_only')) . "<br />\n";
		$reminder_offset = ($reminder ? strtotime($row['start_time']) - strtotime($row['reminder']) : 0);
		echo _Ti('app_input_reminder')
			. format_time_interval($reminder_offset,($prefs['time_intervals_notation'] == 'hours_only'))
			. " " . _T('time_info_before_start') . "<br />\n";
	}

	echo "<br />\n";
	echo _Ti('app_input_created_by') . get_person_name($row) . "<br />\n";

	if ($row['case_title'])
		echo _Ti('app_input_related_to_case') 
			. '<a href="case_det.php?case=' .  $row['id_case'] . '" class="content_link">' . $row['case_title']
			. "</a><br />\n";

	// Show appointment participants
	$q = "SELECT lcm_author_app.*,lcm_author.name_first,lcm_author.name_middle,lcm_author.name_last
		FROM lcm_author_app, lcm_author
		WHERE (id_app=" . $row['id_app'] . "
			AND lcm_author_app.id_author=lcm_author.id_author)";
	$res_author = lcm_query($q);
	if (lcm_num_rows($res_author)>0) {
		echo "Participants: "; // TRAD
		$participants = array();

		while ($author = lcm_fetch_array($res_author)) {
			$participants[] = get_person_name($author);
		}

		echo join(', ',$participants);
		echo "<br />\n";
	}
	
	// Show appointment clients
	$q = "SELECT lcm_app_client_org.*,lcm_client.name_first,lcm_client.name_middle,lcm_client.name_last,lcm_org.name
		FROM lcm_app_client_org, lcm_client
		LEFT JOIN  lcm_org ON lcm_app_client_org.id_org=lcm_org.id_org
		WHERE (id_app=" . $row['id_app'] . "
			AND lcm_app_client_org.id_client=lcm_client.id_client)";
	$res_client = lcm_query($q);

	if (lcm_num_rows($res_client)>0) {
		echo _Ti('app_input_clients');
		$clients = array();
		while ($client = lcm_fetch_array($res_client))
			$clients[] = get_person_name($client)
				. ( ($client['id_org'] > 0) ? " of " . $client['name'] : ''); // TRAD
		echo join(', ',$clients);
		echo "<br />\n";
	}

	// Show edit appointment button
	if ($row['id_author'] == $GLOBALS['author_session']['id_author'])
		echo '<br /><a href="edit_app.php?app=' . $row['id_app'] . '" class="create_new_lnk">' . _T('app_button_edit') . "</a><br />\n";

	if ($row['id_case'] > 0) {
//		echo '<br />';
		// Show parent followup
		$q = "SELECT lcm_app_fu.id_followup,lcm_followup.description FROM lcm_app_fu,lcm_followup
			WHERE lcm_app_fu.id_app=" . $row['id_app'] . "
				AND lcm_app_fu.id_followup=lcm_followup.id_followup
				AND lcm_app_fu.relation='parent'";
		$res_fu = lcm_query($q);
		if (lcm_num_rows($res_fu) > 0) {
			// Show parent followup title
			$fu = lcm_fetch_array($res_fu);
			$title_length = (($prefs['screen'] == "wide") ? 48 : 115);
			if (strlen(lcm_utf8_decode($fu['description'])) < $title_length)
				$short_description = $fu['description'];
			else
				$short_description = substr($fu['description'],0,$title_length) . '...';
			echo '<br />Consequent to:' . ' <a href="fu_det.php?followup=' . $fu['id_followup'] . '">' . $short_description . "</a><br />\n"; // TRAD
		}
		// Show child followup
		$q = "SELECT lcm_app_fu.id_followup,lcm_followup.description FROM lcm_app_fu,lcm_followup
			WHERE lcm_app_fu.id_app=" . $row['id_app'] . "
				AND lcm_app_fu.id_followup=lcm_followup.id_followup
				AND lcm_app_fu.relation='child'";
		$res_fu = lcm_query($q);
		if (lcm_num_rows($res_fu) > 0) {
			// Show child followup title
			$fu = lcm_fetch_array($res_fu);
			$title_length = (($prefs['screen'] == "wide") ? 48 : 115);
			if (strlen(lcm_utf8_decode($fu['description'])) < $title_length)
				$short_description = $fu['description'];
			else
				$short_description = substr($fu['description'],0,$title_length) . '...';
			echo '<br />Resulting followup:' . ' <a href="fu_det.php?followup=' . $fu['id_followup'] . '">' . $short_description; // TRAD
		} else {
			// Show create followup from appointment
			echo '<br /><a href="edit_fu.php?case=' . $row['id_case'] . '&amp;app=' . $row['id_app']
				. '" class="create_new_lnk">Create new followup from this appointment';	// TRAD
		}
		echo "</a><br />\n";

		// Show link back to the case details
		echo '<br /><a href="case_det.php?case=' . $row['id_case'] . '&amp;tab=appointments" class="back_lnk">' . 'To case appointments' . "</a><br />\n"; // TRAD
	}

	echo "<br /></p>";
	echo "</fieldset>\n";

	lcm_page_end();
} else die("There is no such appointment!");

?>
