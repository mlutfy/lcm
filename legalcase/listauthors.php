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
*/

include('inc/inc.php');
include_lcm('inc_acc');
include_lcm('inc_filters');

$q = "SELECT id_author,name_first,name_middle,name_last,status
		FROM lcm_author
		WHERE (1";

// Add search criteria if any
if (strlen($find_author_string)>1) {
	$q .= " AND ((name_first LIKE '%$find_author_string%')"
		. " OR (name_middle LIKE '%$find_author_string%')"
		. " OR (name_last LIKE '%$find_author_string%'))";
	lcm_page_start("Authors, containing '$find_author_string':");
} else {
	lcm_page_start("List of authors");
}

$q .= ")";

// Do the query
$result = lcm_query($q);

// Get the number of rows in the result
$number_of_rows = lcm_num_rows($result);

// Check for correct start position of the list
if ($list_pos>=$number_of_rows) $list_pos = 0;

// Position to the page info start
if ($list_pos>0)
	if (!lcm_data_seek($result,$list_pos))
		die("Error seeking position $list_pos in the result");

// Search form
?>
<form name="frm_find_author" class="search_form" action="listauthors.php" method="post">
	Find author:&nbsp;<input type="text" name="find_author_string" size="10" class="search_form_txt"<?php

//	if (isset($find_author_string)) echo " value='$find_author_string'";
	echo " value='$find_author_string'";

?> />&nbsp;<input type="submit" name="submit" value="Search" class="search_form_btn" />
</form>

<table border='0' width='99%' align='center' class='tbl_usr_dtl'>
<tr><th class='heading'>Name</th><th class='heading'>Status</th><th class='heading'>Action</th></tr>
<?php
// Process the output of the query
for ($i = 0 ; (($i<$prefs['page_rows']) && ($row = lcm_fetch_array($result))) ; $i++) {
	// Show author name
	echo "<tr><td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
//	if ( ) echo '<a href=".php?author=' . $row['id_author'] . '" class="content_link">';
	echo highlight_matches(clean_output($row['name_first'] . ' ' . $row['name_middle'] . ' '
		. $row['name_last']),$find_author_string);
//	if ( ) echo '</a>';
	echo "</td>\n<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	echo clean_output($row['status']);
	echo "</td>\n<td class='tbl_cont_" . ($i % 2 ? "dark" : "light") . "'>";
	if ($GLOBALS['author_session']['status'] = 'admin')
		echo '<a href="edit_author.php?author=' . $row['id_author'] . '" class="content_link">Edit</a>';
	echo "</td></tr>\n";
}

?>
</table>

<a href="edit_author.php?author=0" class="content_link">Add author</a>

<?php

// Show link to previous page
if ($list_pos>0) {
	echo '<a href="listauthors.php';
	if ($list_pos>$prefs['page_rows']) echo '?list_pos=' . ($list_pos - $prefs['page_rows']);
	if (strlen($find_author_string)>1) echo "&amp;find_author_string=" . rawurlencode($find_author_string);
	echo '" class="content_link">< Prev</a> ';
}

// Show page numbers with direct links
$list_pages = ceil($number_of_rows / $prefs['page_rows']);
if ($list_pages>1) {
	for ($i=0 ; $i<$list_pages ; $i++) {
		if ($i==floor($list_pos / $prefs['page_rows'])) echo ($i+1) . ' ';
		else {
			echo '<a href="listauthors.php?list_pos=' . ($i*$prefs['page_rows']);
			if (strlen($find_author_string)>1) echo "&amp;find_author_string=" . rawurlencode($find_author_string);
			echo '" class="content_link">' . ($i+1) . '</a> ';
		}
	}
}

// Show link to next page
$next_pos = $list_pos + $prefs['page_rows'];
if ($next_pos<$number_of_rows) {
	echo "<a href=\"listauthors.php?list_pos=$next_pos";
	if (strlen($find_author_string)>1) echo "&amp;find_author_string=" . rawurlencode($find_author_string);
	echo '" class="content_link">Next ></a>';
}

lcm_page_end();
?>
