<?
/*

    This file is part of Trad-Lang.

    Trad-Lang is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Trad-Lang is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Trad-Lang; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    Copyright 2003 
        Florent Jugla <florent.jugla@eledo.com>, 
        Philippe Rivière <fil@rezo.net>

*/
?>
<html>
<head><style>
<!--
	td {
		font-family : verdana,helvetica,sans-serif;
		font-size : xx-small;
	}
	
	.ligne {
		background-color: #eeeeee;
	}
-->
</style>
</head>
<body bgcolor="#ffffff" text="#000000" link="#600051" vlink="#5832B7" alink="#ff9900"  topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" cellpadding='2' cellspacing='1' border='0'>
<?php

$t = time() + microtime();

$ref = "ts";
$replang = "ecrire/lang";


function get_modules()
{
  $dir_modules = "./traduction";

  $ret = array();

  $handle = opendir($dir_modules);
  while (($fichier = readdir($handle)) != '')
    {
      // Eviter ".", "..", ".htaccess", etc.
      if ($fichier[0] == '.') continue;
      if ($fichier == 'CVS') continue;

      $nom_fichier = $dir_modules."/".$fichier;
      if (is_file($nom_fichier))
        {
          if (!ereg("^module_(.+)\.php$", $fichier, $extlg))
            continue;
          include($nom_fichier);
          $ret[$extlg[1]]["fichier"]=$nom_fichier;
          $ret[$extlg[1]]["nom"] = $nom_module;
          $ret[$extlg[1]]["module"] = substr($lang_prefix, 0, strlen($lang_prefix)-1);
        }
    }
  closedir($handle);

  return $ret;
}

//$modules = array ('spip', 'local', 'public', 'listes', 'ts');
$mods = get_modules();
$modules = array();
foreach($mods as $mod)
     $modules[] = $mod["module"];

echo "<tr><td></td><td>".$ref."</td>";
foreach ($modules as $module)
	if ($module <> $ref)
		echo "<td>$module</td>";
echo "</tr>\n";

include("ecrire/inc_version.php3");
include("ecrire/inc_lang.php3");

// le cache des stats
$fichier_cache_bilan = $replang."/cache_bilan.txt";
$ecrire_cache_bilan = false;
unset ($cache_bilan_f);
if (@file_exists($fichier_cache_bilan)) {
	$cache_f = file($fichier_cache_bilan);
	foreach ($cache_f as $ligne) {
		$ligne = explode(" ",$ligne);
		$cache_date[$ligne[0]] = $ligne[1];
		$cache_val[$ligne[0]] = $ligne[2];
	}
} else
	$cache = false;

// $oui = _T('item_oui');
$oui=' x ';

unset($i18n_spip_fr);

function get_idx_lang()
{
  if (is_array($GLOBALS['idx_lang']))
    return $GLOBALS['idx_lang'];
  else
    return $GLOBALS[$GLOBALS['idx_lang']];
}


foreach ($modules as $module) {
	$GLOBALS['idx_lang'] = "i18n_".$module."_fr";
	include($replang."/".$module."_fr.php3");
	${"i18n_".$module."_ref"} = get_idx_lang();
	$nbcomplet[$module] = sizeof(${"i18n_".$module."_ref"});
}

/// Recuperer liste des fichiers de langue
$pro = array();
	$myDir = opendir($replang);
	while($entryName = readdir($myDir)) {
		if ($entryName<>'..' AND is_file($replang."/".$entryName)) {
			if (ereg("^".$ref."\_([^\.]*)\.php3?$", $entryName, $match)) {
				$ext = $match[1];
				$langues_toutes[] = "$ext";

				/*
				if (file_exists("ecrire/AIDE/$ext/aide"))
					$aide = $oui;
				else if (ereg("^([a-z]+)_", $ext, $regs) AND file_exists("ecrire/AIDE/".$regs[1]."/aide"))
					$aide = $regs[1];
				else
					$aide = '   ';

				if (`grep $ref_$ext\.php3 $replang/CVS/Entries`)
					$cvs = $oui;
				else
					$cvs = '  ';
				*/

				foreach ($modules as $module) {
					$num = 0;
					if (file_exists($file = $replang."/".$module."_".$ext.".php3")) {
						if ((!$cache_date[$module."_".$ext]) OR (filemtime($file) > $cache_date[$module."_".$ext])) {
							$GLOBALS['idx_lang'] = "i18n_".$module."_".$ext;
							include($file);

							$conflit = 0;

							$i18n_spip_lc = get_idx_lang();
							while(list($code,$txt) = each($i18n_spip_lc)) {
								if (!${"i18n_".$module."_ref"}[$code])
									$conflit ++;
								else if (!ereg('<NEW>', $txt)) $num++;
							}
							$ecrire_cache_bilan = true;
						} else
							$num = $cache_val[$module."_$ext"];
					}

					if ($num>0) $nb[$module] = $num; else $nb[$module] = '';
				}

				$langue = traduire_nom_langue($ext);
				if ($nb[$ref] == $nbcomplet[$ref]) $pr = "<tr class='ligne'><td align='left'><b><font color='red'>$langue</font></b></td><td><b><font color='red'>".$nb[$ref]."</font></b></td>";
				else if($nb[$ref]>1000) $pr = "<tr class='ligne'><td align='left'><b>$langue</b></td><td><b>".$nb[$ref]."</b></td>"; 
				else if ($nb[$ref]>42) $pr = "<tr class='ligne'><td align='left'>$langue</td><td>".$nb[$ref]."</td>";
				else $pr = "<tr class='ligne'><td align='left'><i>$langue</i></td><td><i>".$nb[$ref]."</i></td>";

				foreach ($modules as $module)
					if ($module <> $ref)
						if ($nb[$module] == $nbcomplet[$module])
							$pr .= "<td><font color='red'>".$nb[$module]."</font></td>";
						else
							$pr .= "<td>".$nb[$module]."</td>";

				$pr .= "</tr>\n";

				$pro[$pr] = -($ext=='fr') - $nb[$ref];	// index pour trier selon $ref, le francais gagne 1 point pour etre en tete

				foreach ($modules as $module)
					$cache_bilan_f .= $module."_".$ext." ".time()." ".$nb[$module]."\n";

				unset ($i18n_spip_lc);
			}
		}
	}
closedir($myDir);

asort ($pro);
reset ($pro);

while(list($txt,) = each($pro))
	echo $txt;

if ($ecrire_cache_bilan) {
	$f = fopen($fichier_cache_bilan, "w");
	fwrite ($f, $cache_bilan_f);
	fclose ($f);
}

// echo time() + microtime() - $t;

?>
</table>
<!-- <a href='trad_lang.php?etape=bilan' target='_top'>DÉTAILS</a> -->
</body></html>