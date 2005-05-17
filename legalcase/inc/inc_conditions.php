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

// Execute this file only once
if (defined('_INC_CONDITIONS')) return;
define('_INC_CONDITIONS', '1');

//global $condition_types;
$GLOBALS['condition_types'] = array(1 => 'IS EQUAL TO',
				2 => 'IS LESS THAN',
				3 => 'IS GREATER THAN',
				4 => 'CONTAINS',
				5 => 'STARTS WITH',
				6 => 'ENDS WITH');

// Displays select condition form field
// $name - field name, $sel - selected option
function select_condition($name,$sel=0) {
	global $condition_types;

	$html = "<select name='$name' class='sel_frm'>\n";

	foreach($condition_types as $key => $val) {
		$html .= "<option " . (($key == $sel) ? 'selected ' : '') . "value=$key>$val</option>\n";
	}
	$html .= "</select>\n";

	return $html;
}

// Used by rep_det.php/run_rep.php to print filters for report
// * is_runtime determines whether it's time to enter the values for run_rep.php
function show_report_filters($id_report, $is_runtime = false) {
	// Get general report info
	$q = "SELECT * FROM lcm_report WHERE id_report = " . intval($id_report);
	$res = lcm_query($q);
	$rep_info = lcm_fetch_array($res);

	if (! $rep_info)
		lcm_panic("Report does not exist: $id_report");

	// List filters attached to this report
	$query = "SELECT *
		FROM lcm_rep_filter as v, lcm_fields as f
		WHERE id_report = " . $id_report . "
		AND f.id_field = v.id_field";

	$result = lcm_query($query);

	if (lcm_num_rows($result)) {
		if ($is_runtime) {
			// submit all at once (else submit on a per-filter basis)
			echo '<form action="run_rep.php" name="frm_filters" method="get">' . "\n";
			echo '<input name="rep" value="' . $id_report . '" type="hidden" />' . "\n";

			if (isset($_REQUEST['export']))
				echo '<input name="export" value="' . $_REQUEST['export'] . '" type="hidden" />' . "\n";
		}
	
		echo "<table border='0' class='tbl_usr_dtl' width='99%'>\n";

		while ($filter = lcm_fetch_array($result)) {
			if (! $is_runtime) {
				echo "<form action='upd_rep_field.php' name='frm_line_additem' method='get'>\n";
				echo "<input name='update' value='filter' type='hidden' />\n";
				echo "<input name='rep' value='$id_report' type='hidden' />\n";
				echo "<input name='id_filter' value='" . $filter['id_filter'] . "' type='hidden' />\n";
			}

			echo "<tr>\n";
			echo "<td>" . _Th($filter['description']) . "</td>\n";

			// Type of filter
			echo "<td>";

			$all_filters = array(
					'number' => array('none', 'num_eq', 'num_lt', 'num_le', 'num_gt', 'num_ge'),
					'date' => array('none', 'date_eq', 'date_in', 'date_lt', 'date_le', 'date_gt', 'date_ge'),
					'text' => array('none', 'text_eq')
					);

			if ($all_filters[$filter['filter']]) {
				// At runtime, if a filter has been selected, do not allow select
				if ($filter['type'] && $is_runtime) {
					echo _T('rep_filter_' . $filter['type']);
				} else {
					echo "<select name='filter_type'>\n";

					foreach ($all_filters[$filter['filter']] as $f) {
						$sel = ($filter['type'] == $f ? ' selected="selected"' : '');
						echo "<option value='" . $f . "'" . $sel . ">" . _T('rep_filter_' . $f) . "</option>\n";
					}

					echo "</select>\n";
				}
			} else {
				// XXX Should happen only if a filter was removed in a future version, e.g. rarely
				// or between development releases.
				echo "Unknown filter";
			}
			echo "</td>\n";

			// Value for filter
			echo "<td>";

			switch ($filter['type']) {
				case 'num_eq':
					if ($filter['field_name'] == 'id_author') {
						$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'filter_value');
						
						// XXX make this a function
						$q = "SELECT * FROM lcm_author WHERE status IN ('admin', 'normal', 'external')";
						$result_author = lcm_query($q);

						echo "<select name='$name'>\n";
						echo "<option value=''>-- select from list--</option>\n"; // TRAD

						while ($author = lcm_fetch_array($result_author)) {
							// Check for already submitted value
							$sel = (($filter['value'] == $author['id_author'] || $_REQUEST['filter_val' . $filter['id_filter']] == $author['id_author'])
								? ' selected="selected"' : '');
							echo "<option value='" . $author['id_author'] . "'" . $sel . ">" . $author['id_author'] . " : " . get_person_name($author) . "</option>\n";
						}

						echo "</select>\n";
						break;
					}
				case 'num_lt':
				case 'num_gt':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'filter_value');
					echo '<input style="width: 99%;" type="text" name="' . $name . '" value="' . $filter['value'] . '" />';
					break;

				case 'date_eq':
				case 'date_lt':
				case 'date_le':
				case 'date_gt':
				case 'date_ge':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'date');
					echo get_date_inputs($name, $filter['value']); // FIXME
					break;
				case 'date_in':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'date');
					echo get_date_inputs($name . '_start', $filter['value']);
					echo "<br />\n";
					echo get_date_inputs($name . '_end', $filter['value']);
					break;
				case 'text_eq':
					$name = ($is_runtime ? "filter_val" . $filter['id_filter'] : 'filter_value');

					if ($filter['enum_type']) {
						$enum = explode(":", $filter['enum_type']);

						if ($enum[0] == 'keyword') {
							if ($enum[1] == 'system_kwg') {
								$all_kw = get_keywords_in_group_name($enum[2]);

								echo '<select name="' . $name . '">' . "\n";
								echo '<option value="">' . "-- select from list--" . "</option>\n"; // TRAD

								foreach ($all_kw as $kw) {
									$sel = (($filter['value'] == $kw['name'] || $_REQUEST['filter_val' .  $filter['id_filter']] == $kw['name']) ? ' selected="selected" ' : '');
									echo '<option value="' . $kw['name'] . '"' . $sel . '>' . _Tkw($enum[2], $kw['name']) . "</option>\n";
								}

								echo "</select>\n";
							}
						} elseif ($enum[0] == 'list') {
							$items = split(",", $enum[1]);

							echo '<select name="' . $name . '">' . "\n";
							echo '<option value="">' . "-- select from list--" . "</option>\n"; // TRAD

							foreach ($items as $i) {
								$sel = (($filter['value'] == $i || $_REQUEST['filter_val' .  $filter['id_filter']] == $i) ? ' selected="selected" ' : '');
								echo '<option value="' . $i . '"' . $sel . '>' . $i . "</option>\n";
							}

							echo "</select>\n";
						}
					} else {
						echo '<input style="width: 99%;" type="text" name="' . $name . '" value="' . $filter['value'] . '" />';
					}

					break;
				default:
					echo "<!-- no type -->\n";
			}

			echo "</td>\n";

			if (! $is_runtime) {
				// Button to validate
				echo "<td>";
				echo "<button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button>\n";
				echo "</td>\n";

				// Link for "Remove"
				echo "<td><a class='content_link' href='upd_rep_field.php?rep=" . $id_report . "&amp;"
					. "remove=filter" . "&amp;" . "id_filter=" . $filter['id_filter'] . "'>" . "X" . "</a></td>\n";
			}

			echo "</tr>\n";

			if (! $is_runtime)
				echo "</form>\n";
		}

		echo "</table>\n";
	}

	if ($is_runtime) {
		echo "<p><button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button></p>\n";
		echo "</form>\n";
		return;
	}

	// List all available fields in selected tables for report
	$query = "SELECT *
		FROM lcm_fields
		WHERE ";

	$sources = array();

	if ($rep_info['line_src_name'])
		array_push($sources, "'lcm_" . $rep_info['line_src_name'] .  "'");

	// [ML] This is never set.
	// if ($rep_info['col_src_name'])
	//	array_push($sources, "'" /* lcm_" . */ . $rep_info['col_src_name'] . "'");

	// Fetch all tables available as rep colums
	$q_tmp = "SELECT DISTINCT table_name 
				FROM lcm_rep_col as rp, lcm_fields as f
				WHERE rp.id_field = f.id_field
				  AND rp.id_report = " . $id_report;
	
	$result_tmp = lcm_query($q_tmp);

	while ($row = lcm_fetch_array($result_tmp))
		array_push($sources, "'" . $row['table_name'] . "'");

	// List only filters if table were selected as sources (line/col)
	if (count($sources)) {
		$query .= " table_name IN ( " . implode(" , ", $sources) . " ) AND ";
		$query .= " filter != 'none'";
		$query .= " ORDER BY table_name ";

		echo "<!-- QUERY: $query -->\n";

		$result = lcm_query($query);

		if (lcm_num_rows($result)) {
			echo "<form action='upd_rep_field.php' name='frm_line_additem' method='get'>\n";
			echo "<input name='rep' value='" . $rep_info['id_report'] . "' type='hidden' />\n";
			echo "<input name='add' value='filter' type='hidden' />\n";

			echo "<p class='normal_text'>" . _Ti('rep_input_filter_add');
			echo "<select name='id_field'>\n";

			while ($row = lcm_fetch_array($result)) {
				echo "<option value='" . $row['id_field'] . "'>" . _Ti('rep_info_table_' . $row['table_name']) . _Th($row['description']) . "</option>\n";
			}

			echo "</select>\n";
			echo "<button class='simple_form_btn' name='validate_filter_addfield'>" . _T('button_validate') . "</button>\n";
			echo "</p>\n";
			echo "</form>\n";
		}
	} else {
		echo "<p>To apply filters, first select the source tables for report line and columns.</p>"; // TRAD
	}
}

?>
