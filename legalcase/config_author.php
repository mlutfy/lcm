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
include_lcm('inc_filters');

function read_author_data($id_author) {
	$q = "SELECT * FROM lcm_author WHERE id_author=" . $id_author;
	$result = lcm_query($q);
	if (!($usr = lcm_fetch_array($result))) die(_T('error_no_such_user'));

	return $usr;
}

function show_author_form() {
	global $author_session;
	global $prefs;

	// Referer not always set (bookmark, reload, etc.)
	$http_ref = (isset($GLOBALS['HTTP_REFERER']) ? $GLOBALS['HTTP_REFERER'] : '');

?>
<form name="upd_user_profile" method="post" action="config_author.php">
	<input type="hidden" name="author_ui_modified" value="yes"/>
	<input type="hidden" name="referer" value="<?php echo $http_ref; ?>" />

	<table width="99%" border="0" align="center" cellpadding="5" cellspacing="0" class="tbl_usr_dtl">
		<tr>
			<td colspan="2" align="center" valign="middle" class="heading"><h4><?php echo _T('authorconf_subtitle_interface'); ?></h4></td>
		</tr>
<?php
	if ($GLOBALS['all_langs']) {
		echo "
			<tr>
				<td align=\"right\" valign=\"top\">" . _T('authorconf_input_language') . "</td>
				<td align=\"left\" valign=\"top\">
					<input type='hidden' name='old_language' value='" .
					$GLOBALS['lcm_lang']  /* [ML] A cookie might cause problems in 1% of cases $author_session['lang'] */ . "'/>\n";

		echo menu_languages('sel_language');
		echo "
				</td>
			</tr>\n";
	}
?>
	    <tr>
	    	<td align="right" valign="top" width="50%"><?php echo _T('authorconf_input_screen') ?></td>
			<td align="left" valign="top">
				<input type="hidden" name="old_screen" id="old_screen" value="<?php echo $prefs['screen'] ?>" />
				<select name="sel_screen" class="sel_frm">
<?php
	$screen_modes = array("wide","narrow");
	foreach ($screen_modes as $scrm) {
		$selected_mode = ($scrm == $prefs['screen'] ? " selected='selected'" : '');
		echo "<option value='" . $scrm . "'" . $selected_mode . ">"
			. _T('authorconf_input_screen_' . $scrm)
			. "</option>\n";
	}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top"><?php echo _T('authorconf_input_theme'); ?></td>
			<td align="left" valign="top">
				<input type="hidden" name="old_theme" id="old_theme" value="<?php echo $prefs['theme'] ?>" />
				<select name="sel_theme" class="sel_frm" id="sel_theme">
<?php
	$themes = get_theme_list();
	foreach ($themes as $t) {
		// If a theme has no translation, show only the file name
		$name = _T('authorconf_input_theme_' . $t);
		if ($name == 'authorconf_input_theme_' . $t)
			$name = $t;

		$selected = ($t == $prefs['theme'] ? " selected='selected'" : '');
		echo "<option value='" . $t . "'" . $selected . ">" . $name . "</option>\n";
	}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top"><?php echo _T('authorconf_input_font_size'); ?></td>
			<td align="left" valign="top">

				<input type="hidden" name="old_font_size" id="old_font_size" value="<?php echo $prefs['old_font_size'] ?>" />
				<!-- <input name="inc_fnt" type="button" class="search_form_btn" id="inc_fnt" value="A -" />
                &nbsp; <input name="dec_fnt" type="button" class="search_form_btn" id="dec_fnt" value="A +" / >
				(not working yet) -->
				<select name="font_size" class="sel_frm" onchange="setActiveStyleSheet(document.upd_user_profile.font_size.options[document.upd_user_profile.font_size.options.selectedIndex].value)">

				<?php
					$fonts = array('small_font', 'medium_font', 'large_font');

					// font_size gets default value in inc_auth.php
					foreach ($fonts as $f) {
						$sel = ($f == $prefs['font_size'] ? 'selected="selected" ' : '');
						echo '<option ' . $sel . 'value="' . $f . '">' . _T('authorconf_input_' . $f) . '</option>' . "\n";
					}
				?>

				</select>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top"><?php echo _T('authorconf_input_results_per_page'); ?></td>
			<td align="left" valign="top">
				<input name="page_rows" type="text" class="search_form_txt" id="page_rows" size="3" value="<?php
					// page_rows gets default value in inc_auth.php
					echo $prefs['page_rows']; ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center" valign="middle" class="heading"><h4><?php echo _T('authorconf_subtitle_advanced'); ?></h4></td>
		</tr>
	    <tr>
	    	<td align="right" valign="top" width="50%"><?php echo _T('authorconf_input_ui_level') ?></td>
			<td align="left" valign="top">
				<input type="hidden" name="old_mode" id="old_mode" value="<?php echo $prefs['mode'] ?>" />
				<select name="sel_mode" class="sel_frm">
<?php	// [AG] Exactly these names have to be used in the code to avoid changing in every place where the preference is checked
	$interface_modes = array("simple", "extended"); 
	foreach ($interface_modes as $ifm) {
		$selected_mode = ($ifm == $prefs['mode'] ? " selected='selected'" : '');
		echo "<option value='" . $ifm . "'" . $selected_mode . ">"
			. _T('authorconf_input_ui_level_' . $ifm)
			. "</option>\n";
	}
?>
				</select>
			</td>
		</tr>
	    <tr>
	    	<td align="right" valign="top" width="50%"><?php echo _T('authorconf_input_time_intervals') ?></td>
			<td align="left" valign="top">
				<input type="hidden" name="old_time_intervals" id="old_time_intervals" value="<?php echo $prefs['time_intervals'] ?>" />
				<select name="sel_time_intervals" class="sel_frm">
<?php
	$time_intervals = array("absolute", "relative");
	foreach ($time_intervals as $ti) {
		$selected_ti = ($ti == $prefs['time_intervals'] ? " selected='selected'" : '');
		echo "<option value='" . $ti . "'" . $selected_ti . ">"
			. _T('authorconf_input_time_interval_' . $ti)
			. "</option>\n";
	}
?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center" valign="middle">
				<input type="submit" name="submit" type="submit" class="search_form_btn" id="submit" value="<?php echo _T('authorconf_button_update_preferences'); ?>" /></td>
		</tr>
	</table>
</form>

<?php

}

function apply_author_ui_change() {
	global $author_session;
	global $lcm_session;
	global $prefs;

	// From the form
	global $sel_language, $old_language;
	global $sel_theme, $old_theme;
	global $sel_screen, $old_screen;
	global $sel_mode, $old_mode;

	// Show modifications made one finished
	$log = array();

	//
	// Change the user's language (done in inc.php, we only log the result)
	//

	if ($sel_language <> $old_language) {
		array_push($log, "Language set to " .
			translate_language_name($sel_language) . ", was " .
			translate_language_name($old_language) . ".");
	}

	//
	// Change the user's UI colors (done in inc.php, we only log the result)
	//

	if ($sel_theme == $prefs['theme'] && $sel_theme <> $old_theme)
		array_push($log, "Theme set to " . $sel_theme . ", was " . $old_theme . ".");

	//
	// Change the type of the screen - wide or narrow
	//

	if ($sel_screen == $prefs['sel_screen'] && $sel_screen <> $old_screen)
		array_push($log, "Screen mode set to " . $sel_screen . ", was " . $old_screen . ".");

	//
	// Change the font size
	//

	if ($font_size == $prefs['font_size'] && $font_size <> $old_font_size)
		array_push($log, "Screen mode set to " . $font_size . ", was " . $old_font_size . ".");

	//
	// Change the interface mode
	//

	if ($sel_mode == $prefs['mode'] && $sel_mode <> $old_mode)
		array_push($log, "User interface mode set to $sel_mode, was $old_mode.");

	//
	// Change the time intervals
	//

	if ($sel_time_intervals == $prefs['time_intervals'] && $sel_time_intervals <> $old_time_intervals)
		array_push($log, "Time intervals set to $sel_time_intervals, was $old_time_intervals.");
}

function show_changes() {
	global $log;
	//
	// Show changes on screen
	//
	if (! empty($log)) {
		echo "<div align='left' style='border: 1px solid #00ff00; padding: 5px;'>\n";
		echo "<div>Changes made:</div>\n";
		echo "<ul>";

		foreach ($log as $line)
			echo "<li>" . $line . "</li>\n";

		echo "</ul>\n";
		echo "</div>\n";
	}
}

if (isset($_POST['author_ui_modified']))
	apply_author_ui_change();

// Referer may be set by the form, but also by lcm_cookie.php which
// is called before config_author.php via inc.php (ahem..)
if (isset($_REQUEST['referer'])) {
	$target = new Link($_REQUEST['referer']);
	header('Location: ' . $target->getUrl());
	exit;
}

if (isset($_POST['author_ui_modified']))
	show_changes();

lcm_page_start(_T('title_authorconf'));

show_author_form();
lcm_page_end();

?>
