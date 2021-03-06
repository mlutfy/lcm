<?php

/*
	This file is part of the Legal Case Management System (LCM).
	(C) 2004-2006 Free Software Foundation, Inc.

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

if (! $_COOKIE['lcm_session']) {
	echo "nologin";
	return;
}

include('inc/inc.php');

// NOTE: common mistake(s):
// - To return xml with &nbsp;, it causes a syntax error. liveUpdated.js makes
//   an alert with the resulting code, which will look valid, but Firefox will 
//   show in its javascript console where the xml error is located.

header('Content-Type: text/xml');
echo '<?xml version="1.0"?>';

echo "<body>";

if (_request('find_name_client')) {
	include_lcm('inc_obj_client');
	echo "<div id=\"autocomplete-client-popup\">";

	$cpt = 0;
	$search = _request('find_name_client');

	$query = "SELECT *
				FROM lcm_client
				WHERE name_last LIKE '%$search%'
					OR name_first LIKE '%$search%'
					OR CONCAT(name_first, ' ', name_middle, ' ', name_last) LIKE '%$search%'
					OR CONCAT(name_first, ' ', name_last) LIKE '%$search%'";

	$result = lcm_query($query);

	echo "<ul>";

	while (($row = lcm_fetch_array($result))) {
		echo "<li>" . $row['id_client'] . ": " . get_person_name($row) . "</li>\n";
		$cpt++;
	}

	if (! $cpt)
		echo "<li>0: No results</li>"; // TRAD

	echo "</ul>\n";
	echo "</div>\n";
} elseif (_request('find_name_org')) {
	include_lcm('inc_obj_org');
	echo "<div id=\"autocomplete-org-popup\">";

	$cpt = 0;
	$search = _request('find_name_org');

	$query = "SELECT *
				FROM lcm_org
				WHERE name LIKE '%$search%'
					OR tax_number LIKE '%$search%'
					OR court_reg LIKE '%$search%'
					OR stat_number LIKE '%$search%'";

	$result = lcm_query($query);

	echo "<ul>";

	while (($row = lcm_fetch_array($result))) {
		echo "<li>" . $row['id_org'] . ": " . $row['name'] . "</li>\n";
		$cpt++;
	}

	if (! $cpt)
		echo "<li>0: No results</li>"; // TRAD

	echo "</ul>\n";
	echo "</div>\n";

} elseif (_request('find_name_case')) {
	include_lcm('inc_obj_case');
	echo "<div id=\"autocomplete-case-popup\">";

	$cpt = 0;
	$search = _request('find_name_case');

	// $search = 

	// TODO: also search keywords
	$query = "SELECT *
				FROM lcm_case
				WHERE title LIKE '%$search%'";

	$result = lcm_query($query);

	echo "<ul>";

	while (($row = lcm_fetch_array($result))) {
		echo "<li>" . $row['id_case'] . ": " . $row['title'] . "</li>\n";
		$cpt++;
	}

	if (! $cpt)
		echo "<li>0: No results</li>"; // TRAD

	echo "</ul>\n";
	echo "</div>\n";
} elseif (intval(_request('id_client', 0)) > 0) {
	include_lcm('inc_obj_client');
	$client = new LcmClientInfoUI(intval(_request('id_client', 0)));
	$client->printGeneral(false);
	$client->printCases();
	$client->printAttach();
} elseif (intval(_request('id_org', 0)) > 0) {
	include_lcm('inc_obj_org');
	$org = new LcmOrgInfoUI(intval(_request('id_org', 0)));
	$org->printGeneral(false);
	$org->printCases();
	$org->printAttach();
} elseif (($action = _request('action'))) {
	if ($action == 'get_kwg_in') {
		include_lcm('inc_keywords');
		include_lcm('inc_access');

		// Return a list of sub-keyword-groups 
		// Or, if the kwg selected has "apply = many", list the sub-kwg and their keywords
		// Note: currently, it does only for "apply = many".

		// Example: The user selected "matter type = Family", so we will
		// return the "Type 1" + "Type 2" comboboxes.

		echo '<div id="' . _request('div') . '">';

		if (_request('group_name')) {
			$kwg = get_kwg_from_name(_request('group_name', '__ASSERT__'));
			$id_group = $kwg['id_group'];
			$type_obj = _request('type_obj', 'case');
			$id_obj = _request('id_obj', 0);
			$id_obj_sec = _request('id_obj_sec', 0);
			$sub_kwgs = get_subgroups_in_group_id($id_group);

			if (count($sub_kwgs)) {
				if ($kwg['quantity'] == 'one') {
					// Show which sub-kwg to select from
					$obj_id_ajax = 'kw_' . create_random_password(15, time());

					echo '<select id="nop_kwg_' . $obj_id_ajax . '" '
						. 'name="nop_kwg_' . $type_obj . '_value[]" '
						. "onchange=\"getKeywordInfo('get_kws_in', this.value, '$type_obj', $id_obj, $id_obj_sec, '$obj_id_ajax')\""
						. '>';
					echo '<option value="">' . '' . "</option>\n";

					foreach ($sub_kwgs as $foo_kwg)
						echo '<option value="' . $foo_kwg['name'] . '">' . _T(remove_number_prefix($foo_kwg['title'])) . "</option>\n";

					echo "</select>\n";
					echo '<div id="' . $obj_id_ajax . '"></div>' . "\n";
				} else {
					// Show all sub-kwgs as a seperate combobox
					foreach ($sub_kwgs as $sg) {
						$obj_id_ajax = 'kw_' . create_random_password(15, time());
						$sel_id = 'new_keyword_' . $obj_id_ajax;
						$sel_name = 'new_keyword_' . $type_obj . '_value[]';

						echo '<input type="hidden" name="new_kwg_' . $type_obj . '_id[]" value="' . $sg['id_group'] . '" />';
						echo '<label for="' . $sel_id . '">' . _T(remove_number_prefix($sg['title'])) . "</label>";

						$kw_in_subkwgs = get_keywords_in_group_name($sg['name']);

						echo "<select id=\"$sel_id\" name=\"$sel_name\">";
						echo '<option value="">' . '' . "</option>\n";

						foreach ($kw_in_subkwgs as $foo_kw)
							echo '<option value="' . $foo_kw['id_keyword'] . '">' . _T(remove_number_prefix($foo_kw['title'])) . "</option>\n";

						echo "</select>\n";
						echo '<div id="' . $obj_id_ajax . '"></div>' . "\n";
					}
				}
			}
		}

		echo "</div>\n";
	} elseif ($action == 'get_kws_in') {
		// Searching keywords to add to a case (experimental)
		include_lcm('inc_keywords');
		include_lcm('inc_access');

		echo '<div id="' . _request('div') . '">';

		$id_obj = _request('id_obj', 0);
		$type_obj = _request('type_obj', '__ASSERT__');
		$group_name = _request('group_name');

		if ($group_name) {
			$kwg = get_kwg_from_name($group_name);
			$id_group = $kwg['id_group'];

			$kw_for_kwg = get_keywords_in_group_id($id_group);
			if (count($kw_for_kwg)) {
				$obj_id_ajax = 'kw_' . create_random_password(15, time());

				echo '<input type="hidden" name="new_kwg_' . $type_obj . '_id[]" value="' . $id_group . '" />' . "\n";
				echo '<select id="new_keyword_' . $type_obj . $cpt_kw . '" '
					. 'name="new_keyword_' . $type_obj . '_value[]" '
					. "onchange=\"getKeywordInfo('get_kwg_in','$group_name','$type_obj',$id_obj,0, '$obj_id_ajax')\"" // XXX
					. '>';
				echo '<option value="">' . '' . "</option>\n";

				$show_kw_value = false;

				foreach ($kw_for_kwg as $kw) {
					if ($kw['hasvalue'] == 'Y')
						$show_kw_value = true;

					// For default value, use the form_data (if present), else use suggested keyword
					if (isset($_SESSION['form_data']['new_keyword_' . $type_obj . '_value'][$cpt_kw])
							&& $_SESSION['form_data']['new_keyword_' . $type_obj . '_value'][$cpt_kw] == $kw['id_keyword']) 
					{
						$sel = ' selected="selected" ';
					} elseif ($kwg['suggest'] == $kw['name']) {
						$sel = ' selected="selected" ';
					} else {
						$sel = '';
					}

					// $sel = ($kwg['suggest'] == $kw['name'] ? ' selected="selected" ' : '');
					echo '<option ' . $sel . ' value="' . $kw['id_keyword'] . '">' 
						. _T(remove_number_prefix($kw['title']))
						. "</option>\n";
				}

				echo "</select>\n";

				if ($show_kw_value) {
					$tmp_value = '';
					if (isset($_SESSION['form_data']['new_kw_entryval_' . $type_obj . $cpt_kw]))
						$tmp_value = $_SESSION['form_data']['new_kw_entryval_' . $type_obj . $cpt_kw];

					echo "<br />\n";
					echo '<input type="text" name="new_kw_entryval_' . $type_obj . $cpt_kw . '" ' . 'value="' . $tmp_value . '" />' . "\n";
				}

				echo '<div id="' . $obj_id_ajax . '"></div>' . "\n";
			}
		}

		echo "</div>\n";

	} elseif ($action == 'changefont') {
		// should already be changed becaused we included inc.php
	}
} elseif (intval(_request('id_case', 0)) > 0) {
	include_lcm('inc_obj_case');
	echo '<div id="case_data">';

	// Must remove &nbsp; otherwise requestXML cannot parse (?!)
	ob_start();

	$case = new LcmCaseInfoUI(intval(_request('id_case', 0)));
	$case->printGeneral(false, false);
	$case->printFollowups();

	$foo = ob_get_contents();
	ob_end_clean();

	echo preg_replace("/\&nbsp;/", " ", $foo);

	echo "</div>\n";
} else {
	echo "Unknown action.";
}

echo "</body>\n";

?>
