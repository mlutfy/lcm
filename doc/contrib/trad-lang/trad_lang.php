<?php

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
        Philippe Riviere <fil@rezo.net>

*/

//print_r($_GET);

ini_set(memory_limit, "32M");

$home = "..";
chdir($home);

$tlversion = "v0.3";
//$fond='vide';
$delais=10000000;
$flag_preserver=true;	// pas de bouton "recalculer cette page"


if (!isset($spip_lang))
  $spip_lang='en';

// on sauvegarde la valeur de 
// spip lang pour eviter les
// interferences avec SPIP
$spip_lang_sv = $spip_lang;

//include('./inc-public.php3');  

include("ecrire/inc_version.php3");
include("ecrire/inc_lang.php3");
include("ecrire/inc_filtres.php3");
include("ecrire/inc_charsets.php3");
include("ecrire/inc_texte.php3");
include_ecrire('inc_presentation.php3');

$spip_actif = (file_exists("ecrire/inc_connect.php3"));
if (!$spip_actif) {
  echo "SPIP n'est pas activ�.";
  exit;
}

$spip_lang = $spip_lang_sv;

// redefinition de la fonction _T de
// SPIP car celle ci fait trop de choses
function _TT($text, $args = '')
{
  //include_ecrire('inc_lang.php3');
  return traduire_chaine($text, $args);
}

include_ecrire('inc_session.php3');
verifier_visiteur();
if (!$auteur_session)
{
  //echo _TT('ts:texte_avis_enregistrer');
  Header("Location: ../spip_login.php3?var_url=.%2Ftrad-lang%2Ftrad_lang.php");
  exit;
}

$admin_ok = false;
if ($auteur_session['statut'] == '0minirezo') 
  $admin_ok = true;

//include_ecrire('inc_filtres.php3');
//include_ecrire("inc_connect.php3");
include("ecrire/inc_connect.php3");

// securite & uniformisation en minuscules (et _) des codes de langue
$lang_orig  = strtolower(eregi_replace("[^a-z0-9_]", "", $lang_orig));
$lang_cible = strtolower(eregi_replace("[^a-z0-9_]", "", $lang_cible));
$nouv_lang_cible = strtolower(eregi_replace("[^a-z0-9_]", "", $nouv_lang_cible));
$module = strtolower(eregi_replace("[^a-z0-9_]", "", $module));

$direction = "";
$left="left";
$right="right";
$direction = get_dir($spip_lang);
if ($direction=="rtl")
  {
    $left="right";
    $right="left";
  }
    
// recuperation des ref. sur
// les modules
if ($module=="")
  $module = "lcm"; // [ML] etait "spip"
$modules = get_modules();
include($modules[$module]["fichier"]);


function get_dir($lg)
{
  if ($lg=="ar"||$lg=="fa")
    return "rtl";
  else
    return "ltr";
}



// calcul approximatif du nombre
// de lignes pour les champs 'textarea'
function calc_nb_row($item)
{
  $nbmots = substr_count(trim($item), " ") + 1;
  $nbrow = intval(($nbmots / 10) + 2);
  return $nbrow;
}


// cette fonction est redefinie car celle 
// de spip utlise htmlspecialchar qui 
// appremment foire avec les car. espagnols
function entites_html_local($texte) 
{
	global $lang_cible; // dest language

	// [ML]
	$texte = preg_replace("/'/", '&rsquo;', $texte);

	// c.f. http://en.wikipedia.org/wiki/Quotation_mark
	switch($lang_cible) {
		case "fr":
		case "ru":
		case "it":
		case "el": // greek
		case "tr":
			$texte = preg_replace("/\"([^\"]*)\"/", "&laquo;\${1}&raquo;", $texte);
			break;

		case "bg":
		case "de":
		case "sr":
		case "be": // belarus
		case "es";
		case "uk"; // ukrainian
		case "cs":
		case "cz":
			$texte = preg_replace("/\"([^\"]*)\"/", "&bdquo;\${1}&ldquo;", $texte);
			break;

		case "pl":
		case "du":
		case "hu":
		case "ro":
			$texte = preg_replace("/\"([^\"]*)\"/", "&bdquo;\${1}&rdquo;", $texte);
			break;

		case "hr":
		case "da":
			$texte = preg_replace("/\"([^\"]*)\"/", "&bdquo;\${1}&rdquo;", $texte);
			break;

		case "pt_br":
		case "pt":
		case "en":
			$texte = preg_replace("/\"([^\"]*)\"/", "&ldquo;\${1}&rdquo;", $texte);
			break;

		// default:
		//  [ML] leave "as is", the language should be added to the list
		//  using "en" by default would then maker it harder to convert.
	}
  
	return $texte;

  // ajout pour le slovaque
  $trans_caron = array(
    "\xa6" => "&#352;",
    "\x8a" => "&#352;",
    "\xa8" => "&#353;",
    "\x9a" => "&#353;",
    "\xb4" => "&#381;",
    "\xb8" => "&#382;");
  $texte = strtr($texte, $trans_caron);

  // [ML] return corriger_entites_html(htmlentities($texte));

}


function get_modules()
{
  $dir_modules = "./trad-lang";

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
	}
    }  
  closedir($handle);

  return $ret;
}


function get_langues_interface()
{
  // recuperation des langues utilisees par l'interface
  // de traduction

  global $modules;
  include($modules["ts"]["fichier"]);

  return get_langues($nom_mod);
}


/*
 * functions get_id
 * renvoie une string permettant d'encapsuler
 * l'ensemble des champs id en input hidden
 */
function get_id_post($ids)
{
  reset($ids);
  $ret = "";
  while(list(,$id)=each($ids))
    $ret .= "<INPUT TYPE='hidden' VALUE='".$id."' NAME='id[]'>";
  return $ret;
}


function get_id_get($ids)
{
  reset($ids);
  $ret = "";
  while(list(,$id)=each($ids))
    $ret .= "&id[]=".$id;
  return $ret;
}


/*
 * fonctions pour serialiser/deserialiser
 * l'array qui stocke les dates
 */
function get_dates($lang_cible)
{
  global $nom_mod;

  $arr_date = array();

  $quer = "SELECT id,date_modif FROM trad_lang ".
    "WHERE module = '".$nom_mod."' AND lang='".$lang_cible."'";

  $res = mysql_query($quer);
  while($row = mysql_fetch_assoc($res))
    {
      $arr_date[$row["id"]] = $row["date_modif"];
    }

  return $arr_date;
}


/*
 * recherche occurences de $chaines
 * dans la langue donnee
 */
function cherche_occurence($chaine, $lang_or, $lang_dst)
{
  $ret = array();

  $chaine = supp_ltgt(entites_html_local($chaine));

  $quer = "SELECT t1.id,t1.str,t2.str FROM trad_lang t1,trad_lang t2 ".
    "WHERE t1.str like '%".$chaine."%' AND t1.lang='".$lang_or."' ".
    " AND t1.id=t2.id AND t2.lang='".$lang_dst."'";

  $res = mysql_query($quer);
  while($row = mysql_fetch_row($res))
    {
      $ret[$row[0]] = array($row[1], $row[2]);
    }

  return $ret;
}


/*
 * recherche de toutes les occurences
 * d'une chaine dans le fichier langue
 */
function cherche_chaines($texte, $lang_str_orig)
{
  $idr = array();
  while(list($id, $chaine) = each($lang_str_orig))
    {
      if (eregi($texte, $chaine) OR eregi($texte,$id))
	$idr[] = $id;
    }
  return $idr;
}


// sordide bidouille pour recuperer la variable
// langue. Permet la coexistence de l'ancienne forme
// avec celle de la nouvelle
function get_idx_lang()
{
  if (is_array($GLOBALS['idx_lang']))
    return $GLOBALS['idx_lang'];
  else
    return $GLOBALS[$GLOBALS['idx_lang']];
}


// $table_reponse=reference sur la table resultat
// type=nature de la recheche (non traduit, traduit, tous, etc.)
// $langue=la langue sur laquelle appliquer le filtre
// $filtre = le filtre de recherche
// $date = date filtrant la recherche ; si egal a "-", toutes les dates
// $id= la liste eventuellement selectionnee
// $cpt = reference sur le pourcentage renvoye
// $lgorig, $lgcible=references sur les noms de langues selectionnees
// $type_recherche=type de recherche (1=fenetre recherche, 2=fenetre traduction
//    3=fenetre administration)
// $date_pos = reference sur une array qui contient au retour toutes
//    les dates possibles
function recherche($table_reponse, $type, $langue, $filtre, $date, $id, 
   $cpt, $lgorig, $lgcible, $type_recherche, $date_pos)
{
  global $nom_mod, $lang_orig, $lang_suffix;
  global $lang_cible;

  $lang_str_orig = array();
  lire_lang($lang_orig, &$lang_str_orig);
  $lgorig = $lang_str_orig["0_langue"];

  if ($type_recherche != 3)
    { 
      $lang_str_cible = array();
      lire_lang($lang_cible, &$lang_str_cible);

      $lgcible = $lang_str_cible["0_langue"];
      if (ereg("^<NEW>", $lgcible)) $lgcible = "nouveau [$lang_cible]";
    }

  $cpt_tous = 0.0;
  $cpt_aff = 0.0;
  $lang_str = array();
  if ($filtre!='')
    $filtre = supp_ltgt(entites_html_local($filtre));

  reset($lang_str_orig);
  while (list($idl, $val) = each($lang_str_orig))
    {
      $cpt_tous += 1.0;

      if ($type_recherche != 3)
	{
	  $lang_str[$idl]['orig'] = $val;
	  
	  // si filtre sur cible et vide, exclure
	  if ($filtre=='' || $langue=='origine')
	    $lang_str[$idl]['cible'] = '-vide-';
	  else
	    $lang_str[$idl]['cible'] = '-exclu-';
	  
	  if (($filtre!='') && ($langue=='origine'))
	    {
	      if (!eregi($filtre, $val) and !eregi($filtre,$idl))
		$lang_str[$idl]['orig'] = '-exclu-';
	    }
	}
      
      else // type_recherche = 3 (admin, pas de pbs. conflit, etc.)
	{
	  if ($filtre == '')
	    {
	      $table_reponse[$idl] = $val;
	      $cpt_aff += 1.0;
	    }
	  else
	    {
	      if (eregi($filtre, $val) || eregi($filtre,$idl))
		{
		  $table_reponse[$idl] = $val;
		  $cpt_aff += 1.0;
		}
	    }
	}	
    }

  // si type de recherche admin, on arrete les frais
  if ($type_recherche == 3)
    {
      $cpt = ($cpt_aff/$cpt_tous)*100;
      reset($table_reponse);
      return;
    }

  // recupere l'array qui contient l'info
  // sur les dates
  $arr_date = get_dates($lang_cible);

  // positionne le tableau date.
  while(list($val, $dt) = each($arr_date))
    {
      $date_pos[] = $dt;
    }

  reset($lang_str_cible);
  while (list($idl, $val) = each($lang_str_cible))
    {
      $lang_str[$idl]['cible'] = $val;

      if (!$lang_str_orig[$idl])
	{
	  $cpt_tous += 1.0;
	  // si filtre sur origine et vide, exclure
	  if ($filtre=='' || $langue=='cible')
	    $lang_str[$idl]['orig'] = '-vide-';
	  else
	    $lang_str[$idl]['orig'] = '-exclu-';
	}

      if (($filtre!='') && ($langue == 'cible'))
	{
	  if (!eregi($filtre, $val) AND !eregi($filtre,$idl))
	    $lang_str[$idl]['cible'] = '-exclu-';
	}
    }

  ksort($lang_str);
  reset($lang_str);
  $flag_sel = 0;

  $cpt_traduit = 0.0;
  $cpt_non_traduit = 0.0;
  $cpt_conflit = 0.0;
  $cpt_modifie = 0.0;

  $dern_id = '';
  $nb_id = 1;
  if (is_array($id))
    {
      $nb_id = count($id);
      $dern_id = $id[$nb_id-1];
    }

  if (count($lang_str)==1)
    $flag_sel=1;

  while (list($idl, $val) = each($lang_str))
    {
      $val_orig = $val['orig'];
      $val_cible = $val['cible'];

      if (($val_orig=='-exclu-') || ($val_cible=='-exclu-'))
	continue;

      //echo $val_orig.", ".$val_cible."<br>";

      if (($val_orig=='-vide-') || ereg("^<NEW>(.*)", $val_orig))
	$orig = 0;
      else
	$orig = 1;

      if (($val_cible=='-vide-') || ereg("^<NEW>(.*)", $val_cible))
	$cible = 0;
      else
	$cible = 1;

      if (ereg("^<MODIF>(.*)", $val_cible))
	$modifie = 1;
      else
	$modifie = 0;

      $statut = "";

      // calcule la date
      $dt = $arr_date[$idl];
      if ($dt)
	$dt = substr($dt, 8, 2)."/".substr($dt, 5, 2)."/".substr($dt, 0, 4);
      //$dt = date("d M Y", (int)$dt);

      $date_ok = true;
      // si le critere de date est active, verifie
      // que la date est bonne
      if ($date != "" && $date != "-")
	{
	  if ($dt != $date)
	    $date_ok = false;
	}

      if ($dt != "")
	$dt = " - ".$dt;

      if ($date_ok)
	{
	  $cpt_aff += 1.0;

	  if ($orig == 0)  // champ non present en vo
	    {
	      if ($type=='tous' || $type=='conflit')
		{
		  $statut = $idl."&nbsp;["._TT('ts:item_conflit')."]".$dt;
		  $cpt_conflit += 1.0;
		}
	    }
	  else if ($orig==1 && $cible==0)  // champ non present en langue cible
	    {
	      if ($type=='tous' || $type=='non_traduit' || $type=='revise')
		{
		  $statut = $idl."&nbsp;["._TT('ts:item_non_traduit')."]".$dt;
		  $cpt_non_traduit += 1.0;
		}
	    }
	  else  // $orig==1 && cible==1 ; champs present en vo et en cible
	    {
	      if (($type=='tous' || $type=='traduit') && $modifie==0)
		{
		  $statut = $idl."&nbsp;["._TT('ts:item_traduit')."]".$dt;
		  $cpt_traduit += 1.0;
		}
	      else if (($type=='tous' || $type=='modifie' || $type=='revise') && $modifie==1)
		{
		  $statut = $idl."&nbsp;["._TT('ts:item_modifie')."]".$dt;
		  $cpt_modifie += 1.0;
		}
	    }
	}
 
      $opt = "";
      if ($statut != "")
	{
	  if ($flag_sel != 0)
	    {
	      $flag_sel --;
	      $opt = " SELECTED";
	    }

	  if ($type_recherche==1)
	    $table_reponse[] = "<OPTION VALUE='".$idl."'".$opt.">".$statut."\n";
	  else
	    $table_reponse[$idl] = $val;
	}

      if ($dern_id==$idl)
	$flag_sel = $nb_id;
    }

  reset($table_reponse);

  $cpt_courant = $cpt_aff;
  if ($type == 'traduit')
    $cpt_courant = $cpt_traduit;
  else if ($type == 'non_traduit')
    $cpt_courant = $cpt_non_traduit;
  else if ($type == 'conflit')
    $cpt_courant = $cpt_conflit;
  else if ($type == 'modifie')
    $cpt_courant = $cpt_modifie;
  else if ($type == 'revise')
    $cpt_courant = $cpt_modifie+$cpt_non_traduit;

  $cpt = ($cpt_courant/$cpt_tous)*100;

}



function test_module($nom_mod)
{
  $quer = "SELECT id FROM trad_lang WHERE module='".
    $nom_mod."' LIMIT 0,1";
  $res = mysql_query($quer);
  if (mysql_num_rows($res)>0)
    return 1;
  return 0;
}


/* 
 * fonction pour lire la table langue
 * lang_str = ref.
 */
function lire_lang($lang_orig, $lang_str, $type="")
{
  global $nom_mod;

  $lang_str = array();

  if ($type=="md5")
    {
      $quer = "SELECT id,md5 FROM trad_lang ".
	"WHERE module='".$nom_mod."' AND lang='".$lang_orig."' AND !ISNULL(md5)";
      $res = mysql_query($quer);
      while($row = mysql_fetch_assoc($res))
	$lang_str[$row["id"]] = $row["md5"];
    }
  else
    {
      $quer = "SELECT id,str,status FROM trad_lang ".
	"WHERE module = '".$nom_mod."' AND lang='".$lang_orig."' ORDER BY id";
      $res = mysql_query($quer);
      while($row = mysql_fetch_assoc($res))
	{
	  if ($row["status"] != "")
	    $statut = "<".$row["status"].">";
	  else
	    $statut = "";
	  $lang_str[$row["id"]] = $statut.$row["str"];
	}
    }
}


function get_comment($id, $lang, $module)
{
  $quer = "SELECT comm FROM trad_lang WHERE id='".$id."' AND ".
    "lang='".$lang."' AND module='".$module."';";

  $res = mysql_query($quer);
  $row = mysql_fetch_assoc($res);
  return ($row["comm"]);
}

function enregistre_comment($id, $lang, $module, $comm)
{
  $quer = "UPDATE trad_lang SET comm='".texte_script($comm)."' WHERE ".
    "id='".$id."' AND lang='".$lang."' AND module='".$module."';";

  $res = mysql_query($quer);
}

function test_item($nom_mod, $codelg, $key)
{
  $quer = "SELECT id FROM trad_lang ". 
    "WHERE id = '".$key."' AND module = '".$nom_mod."' ".
    "AND lang='".$codelg."'";
  $res = mysql_query($quer);
  if (mysql_num_rows($res) > 0)
    return 1;
  return 0;
}


/*
 * cette fonction permet d'extraire le statut
 * de la chaine depuis le format originel, vers
 * le format base de donnee
 * chaine et statut sont passes par ref.
 */
function extrait_statut($chaine, $statut)
{
  if (ereg("^\<([a-zA-Z_]+)\> (.*)", $chaine, $r))
    {
      $chaine = $r[2];
      $statut = $r[1];
    }
}


function effacer_item($nom_mod, $key)
{
  $quer = "DELETE FROM trad_lang WHERE module='".$nom_mod."' AND id='".$key."'";
  $res = mysql_query($quer);  
}


// retourne la valeur du champ dans
// la langue origine
function get_val_orig($id)
{
  global $nom_mod, $lang_mere;

  $ret = "";
  $quer = "SELECT str FROM trad_lang WHERE module='".$nom_mod."' AND lang='".$lang_mere."'".
    " AND id='".$id."'";
  $res = mysql_query($quer);  
  if ($res)
    {
      $row = mysql_fetch_assoc($res);
      $ret = $row['str'];
    }
  return $ret;
}


/*
 * ecrit une ligne dans la base. si  la ligne existait
 * deja, elle est ecrasee
 */
function ecrire_item($codelg, $id, $chaine, $type="")
{
	global $nom_mod, $lang_mere;

	$orig = 0;
	if ($codelg == $lang_mere)
		$orig = 1;

	$statut = "";
	extrait_statut(&$chaine, &$statut);

	$dummy = "";
	if ($orig == 0)
	{
		// on va chercher la valeur dans la 
		// langue origine pour calculer le md5
		$md5 = md5(get_val_orig($id));
		$dummy = ", md5='".$md5."'";
	}

	if (test_item($nom_mod, $codelg, $id)) {
		if ($type == "debut")  // pas de remise a jour de date_modif ou md5
			$quer = "UPDATE trad_lang SET str='".$chaine."'".
				",status='".$statut."' WHERE id='".$id."' AND lang='".$codelg."' AND ".
				" module='".$nom_mod."'";

		else
			$quer = "UPDATE trad_lang SET str='".$chaine."'".
				",status='".$statut."', date_modif=NOW()".$dummy." WHERE ".
				"id='".$id."' AND lang='".$codelg."' AND ".
				" module='".$nom_mod."'";
	} else {
		if ($dummy != "")
			$quer = "INSERT INTO trad_lang (id,module,lang,status,str,orig,md5) ".
				"VALUES ('".$id."','".$nom_mod."','".$codelg."','".$statut."','".
				$chaine."',".$orig.",'".$md5."')";
		else
			$quer = "INSERT INTO trad_lang (id,module,lang,status,str,orig) ".
				"VALUES ('".$id."','".$nom_mod."','".$codelg."','".$statut."','".
				$chaine."',".$orig.")";
	}

	$res = mysql_query($quer);  
}


/*
 * ecrire la table langue dans la base
 * le champ "type" doit etre initialise avec "md5" pour sauvegarder
 * les fichiers de controle
 */
function ecrire_lang($lang_str, $codelg, $conflit=array(), $type="") 
{
	global $left, $right;
	global $nom_mod, $lang_prolog, $lang_epilog;
	global $export_function;

	if(!$codelg) return false;

	ksort($lang_str);
	reset($lang_str);
	$initiale = "";

	while (list($code, $chaine) = each($lang_str)) {
		if (!array_key_exists($code, $conflit))
			ecrire_item($codelg, $code, texte_script($chaine), $type);
	}

	// ecriture des chaines en conflit
	if (count($conflit)) {
		ksort($conflit);
		reset($conflit);
		while (list($code, $chaine) = each($conflit)) 
			ecrire_item($codelg, $code, texte_script($chaine));
	}

	$res = false;
	if (($export_function != "") && function_exists($export_function)) {
		// sauvegarde du fichier
		$fic_exp_cible = "";
		$res = call_user_func($export_function, $codelg, &$fic_exp_cible, false);
	}

	return $res;
}


function supp_ltgt($texte) 
{
  $texte = ereg_replace ('&quot;', '"', $texte);
  $texte = ereg_replace ('&lt;', '<', $texte);
  $texte = ereg_replace ('&gt;', '>', $texte);
  $texte = ereg_replace ('&amp;lt;', '&lt;', $texte);
  $texte = ereg_replace ('&amp;gt;', '&gt;', $texte);
  return $texte;
}


function recup_modif($item)
{
  // [ML]
  $item = str_replace("^", "&nbsp;", $item);
  $item = interdire_scripts($item);
  $item = supp_ltgt(entites_html_local(stripslashes($item)));
  return $item;

  $item = str_replace("^", "&nbsp;", $item);
  $item = str_replace("\x80", "&euro;", $item);
  $item = str_replace("\r", "", $item);
  $item = str_replace("\n", "", $item);
  $item = str_replace("$", "\n", $item);
  $item = interdire_scripts($item);
  return $item;
}

function affiche_modif($item)
{
  $item = str_replace("&nbsp;", "^", $item);
  $item = ereg_replace("\r\n", "\n", $item);
  $item = ereg_replace("\n", "$\n", $item);
  $item = ereg_replace("\t", " ", $item);
  $item = ereg_replace("&lt;", "&amp;lt;", $item);
  $item = ereg_replace("&gt;", "&amp;gt;", $item);
  $item = ereg_replace("<", "&lt;", $item);
  $item = ereg_replace(">", "&gt;", $item);
  return $item;
}

function affiche_consult($texte)
{
  $texte = str_replace("<", "&lt;", $texte);
  $texte = str_replace(">", "&gt;", $texte);
  $texte = ereg_replace ('"', '&quot;', $texte);
  $texte = ereg_replace ("'", '&#039;', $texte);
  return $texte;
}



function debut_html_ts($titre = "", $taille=550) 
{
  global $direction;

  if ($titre=='')
    $titre = _TT('ts:titre_traduction');
  include("./trad-lang/trad_lang_header.php");
}


function fin_html_ts() 
{
  global $tlversion;
  include("./trad-lang/trad_lang_footer.php");
}


function debut_table($titre_table, $retour="./trad_lang.php")
{
  global $left,$right;
  global $spip_lang, $module, $admin_ok;
  echo " <table border=0 cellspacing=2 cellpadding=5 bgcolor=#cccccc ";
  echo "class=window align=center width=700>";
  echo "<tr> ";
  echo "<td colspan=2 bgcolor=#000088>";
  echo "<table width=100% border=0 cellspacing=0 cellpadding=2 class=windowtitle>";
  echo "<FORM ACTION='".$retour."' METHOD=POST> ";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<tr> ";
  echo "<td align='$left' width='80%'>".$titre_table;
  echo "</td>";

  echo "<td align='$right' width='15%'>";
  if ($admin_ok == true)
    echo "<font color='white'>"._TT("ts:item_admin")."</font>";
  echo "</td>";

  echo "<td align='$right' width='5%'>";
                                                                       
  echo "<input ALT=\""._TT('ts:lien_quitter')."\" title=\""._TT('ts:lien_quitter')."\" type=\"image\" value=\"Quitter\" HSPACE=0 border=0 src=\"./images/stop.gif\" name=\"quitter\" OnClick=\"if (confirm('".addslashes(_TT('ts:lien_page_depart'))."')) submit(); else return false;\">";   

  //echo "<INPUT TYPE='submit' NAME='X' VALUE='&nbsp;X&nbsp;' OnClick='if (confirm(\"".addslashes(_TT('ts:lien_page_depart'))."\")) submit(); else return false;'>";

  echo "</td> ";
  echo "</tr> ";
  echo "</FORM>";
  echo "</table> ";
  echo "</td> </tr>";

}


function fin_table()
{
  echo "</table>";
}


function erreur($texte, $action) 
{
  global $left,$right;
  debut_table(_TT('ts:texte_interface')."<font color='red'>"._TT('ts:texte_erreur')."</font>"."<br>&nbsp;");

  echo "<tr><td colspan=2 class=line_pres align=center>";
  echo "<br>".$texte."<br>";
  echo "</td></tr>";

  echo "<FORM ACTION='".$action."' NAME='returnable' METHOD='POST'>";
  echo "<tr><td class=line align=$right colspan=2>";
  echo "<INPUT TYPE='submit' NAME='Valider' VALUE='"._TT('ts:bouton_revenir_2')."'>\n";
  echo "</td></tr>";
  echo "</FORM>";

  fin_table();
}




function get_langues($nom_mod, $excl="")
{
  $quer = "SELECT distinct lang FROM trad_lang WHERE module='".$nom_mod."'";
  $res = mysql_query($quer);

  $ret = array();
  while ($row=mysql_fetch_assoc($res))
    {
      if ($excl != $row["lang"])
	$ret[] = $row["lang"];
    }
  return $ret;
}

/*
 * Premier ecran : choix de selection des langues
 */
if (!$source AND !$etape) {
  
  $langues = get_langues($nom_mod);
  $langues_c = get_langues($nom_mod, $lang_mere);
  $langues_ihm = get_langues_interface();
  debut_html_ts();

  debut_table(_TT('ts:texte_interface2')."<br>&nbsp;", "..");

  echo "<tr><td align=$left colspan=2 class=line>";
  echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";

  echo "<FORM ACTION='./trad_lang.php' METHOD='POST' NAME='langue'>";
  echo "<tr>";    

  echo "<td width=100>";
  echo _TT("ts:texte_langue");
  echo "</td>";

  echo "<td width=100 align=$left class=window>";
  echo "<SELECT NAME='spip_lang' OnChange='langue.submit()'>\n";
  asort($langues_ihm);
  while (list(,$lg) = each($langues_ihm))
    {
      $option = "";
      if ($lg == $spip_lang)
	$option = " selected";
      echo "<OPTION STYLE='width:200px' VALUE='$lg'$option>".traduire_nom_langue($lg)." ($lg)\n";
    }
  echo "</SELECT>\n";
  echo "</td>";

  echo "<td align=$right>";
  echo "&nbsp;";
  echo "</td>";

  echo "</tr> </FORM>";
  echo "</table>";
  echo "</td></tr>";


  echo "<tr><td class=line_pres colspan=2 bgcolor=#f0f0f0 align=".$left.">";

  echo "<p>"._TT('ts:texte_selectionner');
  echo "<p><UL><LI><b>"._TT('ts:texte_module')."</b>";

  echo "<LI>"._TT('ts:texte_langue_origine');
  echo _TT('ts:texte_langue_origine2');

  echo "<LI>"._TT('ts:texte_langue_cible')."</UL>";

  echo "<p>"._TT('ts:texte_explication_langue_cible', array('module' => $module));
  
  echo "</td></tr>";

  echo "<tr bgcolor=#f0f0f0><td class=line_pres width=300 align=".$left.">";
  echo "<FORM NAME='choix' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='droits'>\n";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>\n";
  echo "<b>"._TT('ts:texte_module_traduire')." </b>";
  echo "</td><td align=$right>\n";

  echo "<SELECT NAME='module' OnChange='choix.etape.value=\"\";choix.submit()'>\n";
  reset($modules);
  while (list($nom,$def) = each($modules))
    {
      $option = "";
      if ($nom == $module)
	$option = " selected";
      echo "<OPTION STYLE='width:200px' VALUE='".$nom."'$option>".$def["nom"]."\n";
    }
  echo "</SELECT>\n";
  echo "</td></tr>\n";

  echo "<tr bgcolor=#f0f0f0><td class=line_pres align=".$left.">";
  echo "<b>"._TT('ts:item_langue_origine')." </b>";
  echo "</td><td align=$right>\n";

  echo "<SELECT NAME='lang_orig'>\n";
  asort($langues);
  while (list(,$lg) = each($langues))
    {
      $option = "";
      if ($lg == 'en') // [ML]
	$option = " selected";
      echo "<OPTION STYLE='width:200px' VALUE='$lg'$option>".traduire_nom_langue($lg)." ($lg)\n";
    }
  echo "</SELECT>\n";
  echo "</td></tr>\n";

  echo "<tr bgcolor=#f0f0f0><td class=line_pres align=".$left.">";
  echo "<b>"._TT('ts:item_langue_cible');
  echo "<br>"._TT('ts:item_nouveau_code')."</b>";
  echo "</td><td align=$right>";
  echo "<SELECT NAME='lang_cible'>\n";
  echo "<OPTION VALUE=''>--\n";
  sort($langues_c);
  reset($langues_c);
  while (list(,$lg) = each($langues_c))
      echo "<OPTION STYLE='width:200px' VALUE='$lg'>".traduire_nom_langue($lg)." ($lg)\n";
  echo "</SELECT>";
  echo "<br><INPUT TYPE='text' NAME='nouv_lang_cible' VALUE='' SIZE='3'>\n";

  echo "</td></tr>\n";

  echo "<tr><td class=line align=$right colspan=2>";

  if ($admin_ok)
    echo "<INPUT TYPE='submit' NAME='Administrer' VALUE=\""._TT("ts:bouton_administrer")."\">&nbsp;&nbsp;";

  echo "<INPUT TYPE='submit' NAME='Valider' VALUE=\""._TT('ts:bouton_continuer')."\">";

  echo "</td></tr>";
  echo "</FORM>";
  fin_table();

  fin_html_ts();
  exit;
}


/*
 * bilan
 */
if ($etape == 'bilan')
{
  $langues = get_langues($nom_mod);
  debut_html_ts();
  echo "<P><B>"._TT('ts:lien_bilan')."</B>\n";

  // chiffres langue originale

  $lang_str_fr = array();
  lire_lang($lang_mere, &$lang_str_fr);
  $total_fr = sizeof($lang_str_fr);

  $lang_buff=array();
  $lang_score=array();

  reset($langues);
  while(list(,$lg) = each($langues))
    {
      $lang_str = array();
      lire_lang($lg, &$lang_str);
      $buff = "";

      $cpt = 0;
      $cpt_non_traduit = 0;
      $cpt_conflit = 0;
      $cpt_modifie = 0;      

      $buff .= "<center>";
      $buff .= "<p><table bgcolor='#f0f0f0' cellspacing='0' cellpadding='4' style='border: ".
	"1px solid black; padding: 2px;' width='60%'>\n";

      reset($lang_str);
      while(list($str,$ch) = each($lang_str))
	{
	  if (ereg("^<NEW>(.*)", $ch))
	    $cpt_non_traduit += 1;
	  else if (ereg("^<MODIF>(.*)", $ch))
	    $cpt_modifie += 1;
	  else if(!$lang_str_fr[$str]) // conflit
	    $cpt_conflit += 1;

	  $cpt += 1;
	}

      $cpt_traduit = $cpt-$cpt_non_traduit-$cpt_conflit-$cpt_modifie;
      $cpt_non_traduit_total = $total_fr - $cpt + $cpt_non_traduit;

      $prt = 100.0;
      $prt_non_traduit = sprintf("%.02f", ($cpt_non_traduit_total/$total_fr) * 100);
      $prt_traduit = sprintf("%.02f", (($cpt_traduit)/$total_fr) * 100);
      $prt_conflit = sprintf("%.02f", (($cpt_conflit)/$total_fr) * 100);
      $prt_modifie = sprintf("%.02f", (($cpt_modifie)/$total_fr) * 100);
      
      $contact = $lang_str['0_mainteneur'];

      $buff .="<tr><td width='30%'><b>"._TT('ts:texte_langue')."</b></td><td><b>".$lang_str["0_langue"]."</b>&nbsp;</td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_fichier')."</td><td>".$nf."</td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_contact')."</td><td>".$contact."</td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_total_chaine')." </td><td>".$cpt."</td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_total_chaine_non_traduite')." </td><td><font color='orange'>".$cpt_non_traduit_total." <b>(".$prt_non_traduit." %)</b></font></td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_total_chaine_traduite')." </td><td><font color='green'>".$cpt_traduit." <b>(".$prt_traduit." %)</b></font></td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_total_chaine_modifie')." </td><td><font color='red'>".$cpt_modifie." <b>(".$prt_modifie." %)</b></font></td></tr>\n";
      $buff .="<tr><td>"._TT('ts:texte_total_chaine_conflit')." </td><td><font color='red'>".$cpt_conflit." <b>(".$prt_conflit." %)</b></font></td></tr>\n";
      $buff .="<tr><td></td><td></td></tr>\n";

      $buff .="<tr><td></td><td align='$right'>";
      if ($lg!='en') // [ML]
	$buff .= "<A HREF='./trad_lang.php?etape=droits&spip_lang=".
	  $spip_lang."&module=".$module."&lang_orig=en&lang_cible=".
	  $lg."'>[Traduire]</A>\n";          
      $buff .="<A HREF='./'>".
	_TT('ts:lien_telecharger')."</A></td></tr>\n";      
      
      $buff .="</table>\n";    
      $buff .="</center>";

      $lang_buff[$lg] = $buff;
      $lang_score[$lg] = $prt_traduit;

      //unset($GLOBALS[$GLOBALS['idx_lang']]);
    }

  arsort($lang_score);
  for (reset($lang_score); $key=key($lang_score); next($lang_score))
    echo $lang_buff[$key];

  echo "<p align='center'><a href='trad_lang.php?spip_lang=".
    $spip_lang."&module=".$module."'>"._TT('ts:lien_revenir_traduction')."</p>\n";

  fin_html_ts();
  exit;
}


/*
 * Tester les droits
 */
if ($etape == 'droits') 
{
  $operation = "";
  if (isset($Valider))
    {
      $operation = "traduction";

      if ((!isset($nouv_lang_cible) || ($nouv_lang_cible==''))
	  && ($lang_cible==''))
	{
	  @header("Location: trad_lang.php?spip_lang=".
		  $spip_lang."&module=".$module);
	  exit;
	}
    }

  else if (isset($Administrer))
    {
      $lang_orig = $lang_mere;
      $operation = "administration";
    }

  else
    {
       @header("Location: trad_lang.php?spip_lang=".
	       $spip_lang."&module=".$module);
       exit;
    }

  $erreur = 0; 
  $save_lang_cible = $lang_cible;

  $nouv_lang = false;
  if (isset($nouv_lang_cible) && ($nouv_lang_cible!=''))
    {
      $nouv_lang = true;
      $lang_cible = $nouv_lang_cible;
      //if (strlen($lang_cible) != 2)
      //	$erreur = 1;
    }

  // langue cible egale a langue origine
  if (($erreur==0) && ($lang_cible==$lang_orig))
    $erreur = 2;
   
  // langue cible = langue mere
  if (($erreur == 0) && ($lang_cible == $lang_mere))
    $erreur = 4;

  if (!test_module($nom_mod))
    $erreur = 3;

  if ($erreur != 0) 
    {
      debut_html_ts();
      
      switch($erreur)
	{
	case 1:
	  erreur("<p>"._TT('ts:lien_code_langue')."</p>", "trad_lang.php?spip_lang=".$spip_lang."&module=".$module);
	  break;
	case 2:
	  erreur("<p>"._TT('ts:texte_langues_differentes').
		 "</p>", "trad_lang.php?spip_lang=".$spip_lang."&module=".$module);
	  break;
	case 3:
	  erreur("<p>"."xxs:module_inexistant".
		 "</p>", "trad_lang.php?spip_lang=".$spip_lang."&module=".$module);
	case 4:
	  erreur("<p>"."xxs:langue_cible_invalide".
		 "</p>", "trad_lang.php?spip_lang=".$spip_lang."&module=".$module);
	  break;
	}
           
      fin_html_ts();
      exit;
    }
  
  else
    {
      $lgs = get_langues($nom_mod);

      // creation  langue si necessaire
      if (isset($nouv_lang_cible) && ($nouv_lang_cible!='')
	  && !in_array($nouv_lang_cible, $lgs))
	{  
	  $lang_str = array();
	  $nouv_lang_str = array();

	  lire_lang($lang_orig, &$lang_str);
	  
	  while (list($key, $val) = each($lang_str)) 
	    $nouv_lang_str[$key] = "<NEW> ".$val;
	  
	  ecrire_lang($nouv_lang_str, $nouv_lang_cible, array(), "debut");
	}

      else  // remise à jour fichier langue cible 
	{  // (traitement nouvelles valeurs éventuelles dans le fichier vo)

	  $lang_str_orig = array();
	  $lang_str_cible = array();
	  $lang_modif = array();

	  lire_lang($lang_orig, &$lang_str_orig);
	  lire_lang($lang_cible, &$lang_str_cible);

	  $lang_str_cible_md5 = array();
	  if ($lang_orig == $lang_mere)
	    {
	      lire_lang($lang_cible, &$lang_str_cible_md5, "md5");
	    }

  	  // ecrasement systématique des chaines non traduites
	  reset($lang_str_cible);
	  while (list($code, $chaine) = each($lang_str_cible)) 
	    {
	      if (ereg("^<NEW>(.*)", $chaine) && $lang_str_orig[$code]!='')  
		$lang_modif[$code] = "<NEW> ".$lang_str_orig[$code];
	    }
	  
	  // traitement nouvelles chaines du fichier vo
	  reset($lang_str_orig);
	  while (list($code, $chaine) = each($lang_str_orig))
	    {
	      if (!array_key_exists($code, $lang_str_cible))
		$lang_modif[$code] = "<NEW> ".$lang_str_orig[$code];

	      // recherche des chaines modifiees. on ne les marque comme
	      // modifiess que si elle ne sont pas marquees comme NEW
	      else if ($lang_orig == $lang_mere)
		{
		  if ( array_key_exists($code, $lang_str_cible_md5) && 
		      (md5($lang_str_orig[$code]) != $lang_str_cible_md5[$code]) )
		    {
		      if ( !ereg("^<MODIF>(.*)", $lang_str_cible[$code])
			   && !ereg("^<NEW>(.*)", $lang_str_cible[$code]) )
			$lang_modif[$code] = "<MODIF> ".$lang_str_cible[$code];
		    }
		}
	    }
	  
	  ecrire_lang($lang_modif, $lang_cible, array(), "debut");  
	}

      Header("Location: trad_lang.php?spip_lang=".$spip_lang."&module=".$module.
	     "&etape=".$operation."&type=revise&lang_orig=".$lang_orig."&lang_cible=".
	     $lang_cible."&nouv_lang_cible=".$nouv_lang_cible);
      exit;

    }
}




/*
 * affichage des identifiants 
 * a traduire
 */
if ($etape == 'traduction')
{
  debut_html_ts();

  $save_lang_cible = $lang_cible;
  if (isset($nouv_lang_cible) && ($nouv_lang_cible!=''))
    $lang_cible = $nouv_lang_cible;
  else if (!$lang_cible) {
	@header("Location: trad_lang.php");
	exit;
  }

  if (!isset($type) || ($type==''))
      $type = 'revise';

  if (!is_array($id))
    {
      $id = array();
      $id[] = '0_URL';
    }

  $cpt=0.0;
  $lang_orig_aff = "";
  $lang_cible_aff = "";

  $table_traduct=array();
  $res_date = array();
  recherche(&$table_traduct, $type, $langue, $filtre, $date, $id, &$cpt, 
     &$lang_orig_aff, &$lang_cible_aff, 1, &$res_date);

  $titre_table = _TT('ts:titre_traduction_de')."<b>".$lang_orig_aff."</b>"._TT('ts:lien_traduction_vers')."<b>".$lang_cible_aff."<br>"._TT('ts:lien_traduction_module')."(".$modules[$module]["nom"].")</b> ";
  debut_table($titre_table);
  
 echo "<tr><td align=$left colspan=2 class=line>";

  echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";

  echo "<FORM ACTION=\"./trad_lang.php?spip_lang=".$spip_lang."&langue=".$langue."&date=".$date."&filtre=".$filtre."&module=".$module."&etape=sauvegarde&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id)."\" METHOD=\"POST\" name=\"sauvegarder\">";

  echo "<tr>";    

  // bouton "sauvegarder" supprime
  echo "<!--td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
  echo "<input ALT=\""._TT('ts:lien_sauvegarder')."\" title='"._TT('ts:lien_sauvegarder')."' type=\"image\" value=\"Sauver\" HSPACE=0 border=0 src=\"./images/sauve.gif\" name=\"sauver\">";   
  echo "</td-->";

  // sauvegarde locale supprimee (faite a chaque valider)
  echo "<!--td width=8>&nbsp;</td>";
  echo "<td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
  echo "<input ALT=\""._TT('ts:lien_export')."\" title=\""._TT('ts:lien_export')."\" type=\"image\" value=\"Exporter\" HSPACE=0 border=0 src=\"./images/save_loc.gif\" name=\"exporter\" OnClick=\"if (confirm('".addslashes(_TT('ts:lien_confirm_export', array('fichier'=>$fic_cible)))."')) exporter.submit(); else return false;\">";   
  echo "</td-->";

  echo "<td width=8>&nbsp;</td>";

  echo "</form>";
  echo "<FORM ACTION=\"./trad_lang.php?spip_lang=".$spip_lang."&telech=1&langue=".$langue."&date=".$date."&filtre=".$filtre."&module=".$module."&etape=sauvegarde&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id)."\" METHOD=\"POST\" name=\"sauvegarder\">";

  echo "<td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
  echo "<input ALT=\""._TT('ts:lien_export_net')."\" title=\""._TT('ts:lien_export_net')."\" type=\"image\" value=\"Exporter\" HSPACE=0 border=0 src=\"./images/save_net.gif\" name=\"exporter\" OnClick=\"exporter.submit();\">";   
  echo "</td>";

  echo "<td align=$right>";
  $prt = sprintf("%.02f", $cpt);
  echo _TT('ts:lien_proportion')."&nbsp;<font color=green><b>(".$prt." %)</b></font>";
  echo "</td>";

  echo "</tr> </FORM>";
  echo "</table>";

  echo "</td></tr>";

  echo "<FORM NAME='chtype' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<tr bgcolor=#f0f0f0> <td  align=$left class=line width=25%><b>"._TT('ts:type_messages')."</b>";
  echo "</td><td align=$right class=line  nowrap>";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='traduction'>";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>";
  echo "<INPUT TYPE='hidden' NAME='id' VALUE='".$id."'>";
  echo "<INPUT TYPE='hidden' NAME='filtre' VALUE='".$filtre."'>";
  echo "<INPUT TYPE='hidden' NAME='date' VALUE='".$date."'>";
  echo "<INPUT TYPE='hidden' NAME='langue' VALUE='".$langue."'>";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_cible' VALUE='".$save_lang_cible."'>\n";
  echo get_id_post($id);
  echo "<INPUT TYPE='hidden' NAME='nouv_lang_cible' VALUE='".$nouv_lang_cible."'>";
  echo "<SELECT NAME='type' ORDERED OnChange='chtype.submit()'>\n";
  echo "<OPTION STYLE=\"width: 150px\" VALUE='revise' ".($type=='revise'?"SELECTED":"").">"._TT('ts:item_revise')."\n"; 
  echo "<OPTION VALUE='tous' ".($type=='tous'?"SELECTED":"").">"._TT('ts:item_tous')."\n"; 
  echo "<OPTION VALUE='traduit' ".($type=='traduit'?"SELECTED":"").">"._TT('ts:item_traduit')."\n"; 
  echo "<OPTION VALUE='non_traduit' ".($type=='non_traduit'?"SELECTED":"").">"._TT('ts:item_non_traduit')."\n"; 
  echo "<OPTION VALUE='conflit' ".($type=='conflit'?"SELECTED":"").">"._TT('ts:item_conflit')."\n"; 
  echo "<OPTION VALUE='modifie' ".($type=='modifie'?"SELECTED":"").">"._TT('ts:item_modifie')."\n"; 
  echo "</SELECT>";
  echo "</td> </tr>";
  echo "</FORM>\n";
 
  echo "<FORM NAME='chfiltre' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<tr bgcolor=#f0f0f0><td align=$left class=line>";
  echo "<b>"._TT('ts:texte_filtre')."</b>";
  echo "</td><td align=$right class=line nowrap>";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='traduction'>";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>";
  echo "<INPUT TYPE='hidden' NAME='id' VALUE='".$id."'>";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_cible' VALUE='".$save_lang_cible."'>\n";
  echo get_id_post($id);
  echo "<INPUT TYPE='hidden' NAME='nouv_lang_cible' VALUE='".$nouv_lang_cible."'>";
  echo "<SELECT NAME='date' OnChange='chfiltre.submit()'>\n";
  reset($res_date);
  sort($res_date);
  echo "<OPTION VALUE='-'>"._TT('ts:item_date')."</OPTION>\n";
  $oldd = "";
  while(list(,$dt)=each($res_date))
    {
      if ($dt)
	{
	  $dts = substr($dt, 8, 2)."/".substr($dt, 5, 2)."/".substr($dt, 0, 4);
	  if ($oldd!=$dts)
	    {
	      $opt = "";
	      if ($dts == $date)
		$opt = " SELECTED";
	      echo "<OPTION VALUE='".$dts."'".$opt.">".$dts."</OPTION>\n";
	      $oldd = $dts;
	    }
	}
    }
  echo "</SELECT>";
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<INPUT TYPE='text' VALUE='".$filtre."' NAME='filtre' SIZE='15' OnChange='chfiltre.submit()'>";
  echo "&nbsp;"._TT('ts:dans')." : "; 
  echo "<SELECT NAME='langue' OnChange='chfiltre.submit()'>\n";
  echo "<OPTION STYLE=\"width: 150px\" VALUE='origine'";
  if ($langue=='origine') echo "SELECTED";
  echo ">"._TT('ts:sel_langue_origine')."\n";
  echo "<OPTION VALUE='cible'";
  if ($langue=='cible') echo "SELECTED";
  echo ">"._TT('ts:sel_langue_cible')."\n";
  echo "</SELECT>\n";
  echo "</td> </tr>";
  echo "</FORM>\n";

  echo "<FORM ACTION='trad_lang.php' NAME='returnable' METHOD='POST'>\n";
  echo "<tr bgcolor=#f0f0f0><td align=$left class=line>";
  echo "<b>"._TT('ts:texte_type_operation')."</b>";
  echo "</td><td align=$right class=line nowrap>";
  echo "<b>"._TT('ts:texte_tout_selectionner')."</b>";
  echo "<INPUT TYPE='checkbox' NAME='tout' ".($tout=='on'?CHECKED:'').">";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
  echo "&nbsp;&nbsp;&nbsp;&nbsp;";
  echo "<SELECT NAME='affichage'>\n";
  echo "<OPTION STYLE=\"width: 150px\" VALUE='modification'";
  if ($affichage=='modification') echo "SELECTED";
  echo ">"._TT('ts:texte_modifier')."\n";
  echo "<OPTION VALUE='consultation_html' ";
  if ($affichage=='consultation_html') echo "SELECTED";
  echo ">"._TT('ts:texte_consulter')."\n";
  echo "<OPTION VALUE='consultation_brut' ";
  if ($affichage=='consultation_brut') echo "SELECTED";
  echo ">"._TT('ts:texte_consulter_brut')."\n";
  echo "</SELECT></td></tr>\n";

  echo "<tr bgcolor=#f0f0f0><td align=$left class=line colspan=2>";

  echo "<INPUT TYPE='hidden' NAME='langue' VALUE='".$langue."'>\n";
  echo "<INPUT TYPE='hidden' NAME='filtre' VALUE='".$filtre."'>\n";
  echo "<INPUT TYPE='hidden' NAME='date' VALUE='".$date."'>";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='traduction_id'>\n";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>\n";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>\n";
  echo "<INPUT TYPE='hidden' NAME='lang_cible' VALUE='".$save_lang_cible."'>\n";
  echo "<INPUT TYPE='hidden' NAME='nouv_lang_cible' VALUE='".$nouv_lang_cible."'>\n";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>\n";
  echo "<CENTER>\n";
  echo "<P><SELECT STYLE='width:100%' NAME='id[]' SIZE='14' ORDERED MULTIPLE OnDblClick='submit();'>\n";
  while (list(, $val) = each($table_traduct))
    echo $val;
  echo "</SELECT>\n";
  echo "</CENTER>\n";
  echo "</td></tr>";

  echo "<tr><td align=$right colspan=2>";
  echo "<INPUT TYPE='submit' NAME='Valider' VALUE=' >>> ' >\n";
  echo "</td></tr>";
  echo "</FORM>\n";

  fin_table();
  fin_html_ts();
  exit;
}



/*
 * traduction  identifiant(s)
 */
if ($etape == 'traduction_id')
{
  debut_html_ts("Traductions",750);

  $save_lang_cible = $lang_cible;
  if (isset($nouv_lang_cible) && ($nouv_lang_cible!=''))
    $lang_cible = $nouv_lang_cible;

  if (!isset($id))
    $id=array();

  if ($affichage=='modification' && $tout=='on')
    {
      erreur(_TT('ts:texte_operation_impossible'), "trad_lang.php?spip_lang=".$spip_lang."&module=".$module."&annuler=".$annuler."&affichage=".$affichage."&tout=".$tout."&langue=".$langue."&date=".$date."&filtre=".$filtre."&etape=traduction&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id));
      exit();
    }

  if ($affichage=='modification')
    {      
      $titre_table = "<B>"._TT('ts:texte_saisie_informations')."</B><br>&nbsp;\n";
      debut_table($titre_table);

      echo "<td colspan=2 align=$left class=line>";

      echo "<table cellspacing=0 cellpadding=0 border=0>";

      echo "<tr>";
     
      echo "<td width=8>&nbsp;</td>";

      echo "<td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
      echo "<input ALT=\"Chercher\" title=\""._TT('ts:lien_chercher')."\" type=\"image\" value=\"Chercher\" HSPACE=0 border=0 src=\"./images/find.gif\" name=\"chercher\" OnClick=\"ouvrirfen(700,500,'./trad_lang.php?spip_lang=$spip_lang&etape=chercher&lang_orig=$lang_orig&lang_cible=$save_lang_cible');return false;\">";   
      echo "</td>";
      echo "<td width=30>&nbsp;</td>";

      echo "<td>";
      $dollar = "$"; $circ = "^";
      echo _TT('ts:texte_saisie_informations2', array('circ'=>$circ))."<br>";
      echo _TT('ts:texte_saisie_informations3', array('dollar'=>$dollar));
      echo "</td>";

      echo "</tr></table>";
      
      echo "</td>";

    }
  else
    {
      echo "<B>"._TT('ts:texte_recapitulatif')."</B><BR><BR>\n";
      echo "<table border=1 cellspacing=0 cellpadding=2 width=90%>\n";
    }

  $lang_orig_aff = "";
  $lang_cible_aff = "";
  $cpt = 0.0;
  $table_ch=array();
  $res_date = array();
  recherche(&$table_ch, $type, $langue, $filtre, $date, $id, &$cpt, 
	    &$lang_orig_aff, &$lang_cible_aff, 2, &$res_date);

  echo "<FORM ACTION='trad_lang.php' NAME='returnable' METHOD='POST'>\n";
  echo "<INPUT TYPE='hidden' NAME='filtre' VALUE='".$filtre."'>\n";
  echo "<INPUT TYPE='hidden' NAME='date' VALUE='".$date."'>";
  echo "<INPUT TYPE='hidden' NAME='tout' VALUE='".$tout."'>\n";
  echo "<INPUT TYPE='hidden' NAME='langue' VALUE='".$langue."'>\n";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='enregistrer'>\n";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>\n";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>\n";
  echo "<INPUT TYPE='hidden' NAME='lang_cible' VALUE='".$save_lang_cible."'>\n";
  echo "<INPUT TYPE='hidden' NAME='nouv_lang_cible' VALUE='".$nouv_lang_cible."'>\n";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>\n";
  echo "<INPUT TYPE='hidden' NAME='affichage' VALUE='".$affichage."'>\n";

  if ($affichage=='modification')
    {
      echo "<tr>";
      echo "<td align=$left class=line>";
      echo "<b>".$lang_orig_aff."</b>\n";
      echo "</td>\n";

      echo "<td align=$left class=line>";
      echo "<b>".$lang_cible_aff."</b>\n";
      echo "</td>";
      echo "</tr>";
    }
    
  if (!is_array($id))
    $id = array();

  if ((count($id) == 0) && ($tout!="on"))
    {
      echo "<tr bgcolor=#f0f0f0>";
      echo "<td colspan='2' class=line_pres align=center>";
      echo "<b><p>"._TT('ts:texte_pas_de_reponse')."</b></td>";
      echo "</tr>";
    }
  else
    {
      reset($table_ch);
      $idx = 0;
      while (list($cle,$val) = each($table_ch))
	{     
	  if ((count($id)>0) && (!in_array($cle, $id)) && ($tout!="on"))
	    continue;

	  echo "<INPUT TYPE='hidden' VALUE='".$cle."' NAME='id[]'>";
	  if ($affichage=='modification')
	    {
	      echo "<tr bgcolor=#f0f0f0>";

	      echo "<td align=$left class=line>";
	      echo "<b>".$cle."</b>&nbsp;&nbsp;";

	      echo "<input ALT=\""._TT('ts:lien_commentaire')."\" title=\""._TT('ts:lien_commentaire')."\" type=\"image\" value=\"Commenter\" HSPACE=0 border=0 src=\"./images/comment.gif\" name=\"commenter\" OnClick=\"ouvrirfen(300,180,'./trad_lang.php?spip_lang=$spip_lang&etape=commenter&lang_orig=$lang_orig&id=$cle&nommodule=$nom_mod');return false;\">";
	      echo "&nbsp;".get_comment($cle, $lang_orig, $nom_mod);

	      echo "</td><td>";

	      echo "<input ALT=\""._TT('ts:lien_commentaire')."\" title=\""._TT('ts:lien_commentaire')."\" type=\"image\" value=\"Commenter\" HSPACE=0 border=0 src=\"./images/comment.gif\" name=\"commenter\" OnClick=\"ouvrirfen(300,180,'./trad_lang.php?spip_lang=$spip_lang&etape=commenter&affmodif=oui&lang_orig=$lang_cible&id=$cle&nommodule=$nom_mod');return false;\">";
	      echo "&nbsp;".get_comment($cle, $lang_orig, $nom_cible);

	      echo "</td>";
	      echo "<tr bgcolor=#f0f0f0>";
	    }
	  else
	    {
	      echo "<tr>";
	      echo "<td align=$left width=20% class=line_aff>";
	      $val_aff = $cle;
	      if (strlen($val_aff)>=21)
		$val_aff = substr($val_aff, 0, 20)."...";
	      echo $val_aff."</td>";
	    }
	  
	  $nb_row = 0;
	  if ($affichage=='modification')
	    {
	      echo "<td align=$left class=line width=50%>";
	      $item = affiche_modif($val["orig"]);
	      $nb_row = calc_nb_row($item);
	      echo "<TEXTAREA STYLE='width:100%' ROWS='".$nb_row."' COLS='45' WRAP='soft' NAME='val_orig[]' dir='".get_dir($lang_orig)."'>".
		$item."</TEXTAREA>\n";
	      echo "</td>";
	    }
	  else
	    {
	      if ($affichage=='consultation_html')
		echo "<td class=line_aff>".$val["orig"]."</td>";
	      else
		echo "<td class=line_aff>".affiche_consult($val["orig"])."</td>";
	    }
	  
	  if ($affichage=='modification')
	    {
	      echo "<td align=$left class=line width=50%>";
	      $item = affiche_modif($val["cible"]);
	      echo "<TEXTAREA tabindex='" . ($idx + 1) . "' STYLE='width:100%' ROWS='".$nb_row."' COLS='45' WRAP='soft' NAME='val_cible[".$idx."]' dir='".get_dir($lang_cible)."'>".
		$item."</TEXTAREA>\n";	      	      
	      echo "<INPUT TYPE='hidden' NAME='chk_cible[".$idx."]' VALUE='".md5($val["cible"])."'>";
	      echo "</td>";
	    }
	  else
	    {
	      if ($affichage=='consultation_html')
		echo "<td class=line_aff>".$val["cible"]."</td>";
	      else
		echo "<td class=line_aff>".affiche_consult($val["cible"])."</td>\n";	
	    }
	  
	  echo "</tr>\n";
	  
	  $idx ++;
	}
    }
 
  if ($affichage=='modification')
    {
      echo "<tr><td colspan=2 align=$right>";
      echo "<INPUT TYPE='submit' NAME='annuler' VALUE='"._TT('ts:bouton_annuler')."'>\n";
      echo "&nbsp;&nbsp;&nbsp;<INPUT TYPE='submit' NAME='Valider' VALUE='"._TT('ts:bouton_valider')."'>\n";
      echo "</td></tr>";
    }
  else
    {
      echo "<tr><td colspan=3 align=center>";
      echo "<INPUT TYPE='submit' NAME='annuler' VALUE='"._TT('ts:bouton_annuler')."'>\n";
      echo "</td></tr>";
    }

  echo "</FORM>\n";
    
  if ($affichage=='modification')
    echo fin_table();
  else
    echo "</table>";

  fin_html_ts();
  exit;
}



/*
 * recherche des chaines
 */
if ($etape == 'chercher')
{
  echo '<html><body>                                                                                                                                    
    <head>
    <title></title>
    <meta HTTP-EQUIV="Expires" CONTENT="0">
    <meta HTTP-EQUIV="cache-control" CONTENT="no-cache,no-store">
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
    </head>';

  echo '<form action="./trad_lang.php" name="recherche" method="post">';
  echo '<input name="lang_orig" type="hidden" value="'.$lang_orig.'">';
  echo '<input name="lang_cible" type="hidden" value="'.$lang_cible.'">';
  echo "<input name='spip_lang' type='hidden' value='$spip_lang'>";
  echo '<input name="etape" type="hidden" value="chercher">';

  echo '<input name="chaine" type="text" size="50" value="'.$chaine.'">';
  echo '<input type="submit" value="'._TT('ts:bouton_chercher').'">';

  $ch = 0;
  $res = array();
  if (isset($chaine) && ($chaine!=""))
    {
      $res = cherche_occurence($chaine, $lang_orig, $lang_cible);
      $ch=1;
    }
  echo "</form>";

  echo "<table border=1 cellspacing=0 cellpadding=2 width=90%>\n";

  reset($res);

  if ((count($res)==0) && ($ch==1))
    $res["pas de resultat"] = array("", "");

  while(list($id, $val)=each($res))
    {
      echo "<tr>";

      echo "<td align=$left width=20% class=line_aff>";
      $val_aff = $id;
      if (strlen($val_aff)>=21)
	$val_aff = substr($val_aff, 0, 20)."...";
      echo $val_aff."</td>";

      echo "<td class=line_aff>".$val[0]."</td>";
      echo "<td class=line_aff>".$val[1]."</td>";

      echo "</tr>\n";      
    }

  echo "</table>";
  echo "</body></html>";
  exit;
}

/*
 * commenter
 */
if ($etape == 'commenter')
{
  if (isset($modifier))
    {
      $item = recup_modif($comm);
      enregistre_comment($id, $lang_orig, $nom_mod, $item);
      $fermer = "oui";
    }

  if (isset($fermer))
    {    
      echo "<html><head><script language='javascript'><!--\n".
	"window.close();\n".
	"--></script></head><body></body></html>";
      exit;
    }

  echo '<html><body>
    <head>
    <title></title>
    <meta HTTP-EQUIV="Expires" CONTENT="0">
    <meta HTTP-EQUIV="cache-control" CONTENT="no-cache,no-store">
    <meta HTTP-EQUIV="pragma" CONTENT="no-cache">
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
    </head>';

  echo "<form name='commentaire' method='POST' action='./trad_lang.php'>";
  echo "<input name='lang_orig' type='hidden' value='$lang_orig'>";
  echo "<input name='etape' type='hidden' value='commenter'>";
  echo "<input name='spip_lang' type='hidden' value='$spip_lang'>";
  echo "<input name='id' type='hidden' value='$id'>";
  echo "<input name='affmodif' type='hidden' value='$affmodif'>";
  echo "<input name='nommodule' type='hidden' value='$nommodule'>";

  echo "<div>";
  echo _TT('ts:lien_commentaire2')."<b>".$nommodule."</b>, <b>".$id."</b>, <b>".$lang_orig."</b>";
  echo "</div>";

  echo "<div>";
  $comm = affiche_modif(get_comment($id, $lang_orig, $nommodule));
  echo "<TEXTAREA STYLE='width:100%' ROWS='6' COLS='45' WRAP='soft' NAME='comm'>".
    $comm."</TEXTAREA>";
  echo "</div>";

  echo "<div align=right Style='margin: 10px>'";
  if (isset($affmodif) && ($affmodif!=''))
    {
      echo "<input name='modifier' type='submit' value='"._TT('ts:bouton_modifier')."'>";
      echo "&nbsp;&nbsp;";
    }
  echo "<input name='fermer' type='submit' value='"._TT('ts:bouton_annuler')."'>";
  echo "</div>";

  echo "</form>";

  echo "</body></html>";
  exit;
}


/*
 * enregistrement d'une traduction
 */
if ($etape == 'enregistrer') {
	$save_lang_cible = $lang_cible;
	if (isset($nouv_lang_cible) && ($nouv_lang_cible!=''))
		$lang_cible = $nouv_lang_cible;

	if (!isset($annuler)) {
		$lang_str_cible = array();
		$lang_str_modif = array();
		lire_lang($lang_cible, &$lang_str_cible);

		reset($id);
		$idx = 0;

		while(list(,$val) = each($id)) {
			$item = recup_modif($val_cible[$idx]);

			// ajoute le champ seulement s'il a  ete modifie
			if (md5($item) != $chk_cible[$idx]) {
				$lang_str_modif[$val] = $item;
			}
			$idx++;
		}

		$lang_str_orig = array();
		$en_conflit = array();
		lire_lang($lang_orig, &$lang_str_orig);

		// traitement des chaines en conflit
		reset($lang_str_cible);

		while (list($code, $chaine) = each($lang_str_cible)) {
			if (!array_key_exists($code, $lang_str_orig))
				$en_conflit[$code] = $chaine;
		}

		ecrire_lang($lang_str_modif, $lang_cible, $en_conflit);
	}

	Header("Location: trad_lang.php?spip_lang=".$spip_lang."&module=".$module."&affichage=".$affichage."&tout=".$tout."&langue=".$langue."&date=".$date."&filtre=".$filtre."&etape=traduction&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id));
      
	exit;
}


if (($etape == 'sauvegarde') || ($etape=='sauvegarde_adm'))
{
  $save_lang_cible = $lang_cible;
  if (isset($nouv_lang_cible) && ($nouv_lang_cible!=''))
    $lang_cible = $nouv_lang_cible;

  if (($export_function != "") && function_exists($export_function))
    {  
      $fic_exp_cible = "";
      $res = call_user_func($export_function, $lang_cible, &$fic_exp_cible, $telech);
	
      if ($res == false)
	{
	  debut_html_ts();
	  erreur(_TT('ts:texte_export_impossible', array("cible"=>$fic_exp_cible)), "trad_lang.php?spip_lang=".$spip_lang."&module=".$module."&annuler=".$annuler."&affichage=".$affichage."&tout=".$tout."&langue=".$langue."&date=".$date."&filtre=".$filtre."&etape=traduction&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id));
	  fin_html_ts();
	  exit;
	}
     
      if ($etape == 'sauvegarde_adm')
        $operation = 'administration';
      else
        $operation = 'traduction';

      Header("Location: trad_lang.php?etape=".$operation."&spip_lang=".$spip_lang."&module=".$module."&annuler=".$annuler."&affichage=".$affichage."&tout=".$tout."&langue=".$langue."&date=".$date."&filtre=".$filtre."&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$save_lang_cible."&nouv_lang_cible=".$nouv_lang_cible.get_id_get($id));
    }
}


/*
 * administration
 */
if ($etape == 'administration')
{
  debut_html_ts();

  if ($admin_ok == false)
    {
      erreur(_TT("ts:texte_seul_admin"), "trad_lang.php?spip_lang=".$spip_lang."&module=".$module);
      fin_html_ts();
      exit;
    }

  if ( (isset($ajout) && ($nouveau!='')) ||
       isset($effacer) ||
       isset($modifier) )
    {  // ajout nouvelle chaine

      $lang_str_orig = array();
      $lang_modif = array();

      lire_lang($lang_orig, &$lang_str_orig);
      
      if (isset($ajout))
	{
	  $nouveau = strtolower($nouveau);
	  if (!array_key_exists($nouveau, $lang_str_orig))
	    $lang_modif[$nouveau] = "";
	  $id = $nouveau;

	  ecrire_lang($lang_modif, $lang_orig, array(), "debut");  
	}

      else if (isset($modifier))
	{
	  $item = recup_modif($texte);
	  $lang_modif[$id] = $item;

	  ecrire_lang($lang_modif, $lang_orig, array(), "debut");  
	}
	  
      else if (isset($effacer))
	{
	  effacer_item($nom_mod, $id);
	}
    }

  //non utilise dans le cas admin
  $type = 'tous';

  $cpt=0.0;
  $lang_orig_aff = "";
  $lang_cible_aff = "";  // non utilise, mais initialise poru la fonction rech.

  $table_rech=array();
  $res_date = array();
  recherche(&$table_rech, $type, $langue, $filtre, "-", array($id), &$cpt, 
     &$lang_orig_aff, &$lang_cible_aff, 3, &$res_date);

  if (!isset($id) || !array_key_exists($id, $table_rech))
    $id = key($table_rech);

  $titre_table = _TT("ts:texte_admin")."<b>".$lang_orig_aff."</b><br>".
    _TT('ts:lien_traduction_module')."(".$modules[$module]["nom"].")</b> ";

  debut_table($titre_table);
  
  // toolbar
  
  echo "<tr><td align=$left colspan=2 class=line>";
  echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";

  echo "<tr>";    

  echo "<FORM ACTION=\"./trad_lang.php?spip_lang=".$spip_lang."&langue=".$langue."&date=".$date."&filtre=".$filtre."&module=".$module."&etape=sauvegarde_adm&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$lang_orig."&nouv_lang_cible=".$nouv_lang_cible."\" METHOD=\"POST\" name=\"sloc\">";

  echo "<td width=8>&nbsp;</td>";
  echo "<td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
  echo "<input ALT=\""._TT('ts:lien_export')."\" title=\""._TT('ts:lien_export')."\" type=\"image\" value=\"Exporter\" HSPACE=0 border=0 src=\"./images/save_loc.gif\" name=\"exporter\" OnClick=\"if (confirm('".addslashes(_TT('ts:lien_confirm_export', array('fichier'=>$fic_cible)))."')) sloc.submit(); else return false;\">";   
  echo "</td>";

  echo "</form>";

  echo "<FORM ACTION=\"./trad_lang.php?spip_lang=".$spip_lang."&langue=".$langue."&date=".$date."&filtre=".$filtre."&telech=1&module=".$module."&etape=sauvegarde_adm&type=".$type."&lang_orig=".$lang_orig."&lang_cible=".$lang_orig."&nouv_lang_cible=".$nouv_lang_cible."\" METHOD=\"POST\" name=\"sauvegarder\">";

  echo "<td width=8>&nbsp;</td>";
  echo "<td width=32 class=window onMouseOver=\"this.style.backgroundColor='#eeee87'\"  onMouseOut=\"this.style.backgroundColor=''\">";
  echo "<input ALT=\""._TT('ts:lien_export_net')."\" title=\""._TT('ts:lien_export_net')."\" type=\"image\" value=\"Exporter\" HSPACE=0 border=0 src=\"./images/save_net.gif\" name=\"exporter\" OnClick=\"sauvegarder.submit();\">";   
  echo "</td>";

  echo "<td align=$right>";
  echo "&nbsp";
  echo "</td>";

  echo "</tr> </FORM>";
  echo "</table>";

  echo "</td></tr>";
  // fin toolbar

  echo "<FORM NAME='chfiltre' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<tr bgcolor=#f0f0f0><td align=$left class=line>";
  echo "<b>"._TT('ts:texte_filtre')."</b>";
  echo "</td><td align=$right class=line nowrap>";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='administration'>";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>";
  echo "<INPUT TYPE='hidden' NAME='id' VALUE='".$id."'>";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>";
  echo "<INPUT TYPE='text' VALUE='".$filtre."' NAME='filtre' SIZE='30'>";
  echo "&nbsp;<INPUT TYPE='submit' NAME='chercher' STYLE='width:20%' VALUE='"._TT("ts:bouton_chercher")."'>";
  echo "</td> </tr>";
  echo "</FORM>\n";

  echo "<FORM NAME='ajout' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<tr bgcolor=#f0f0f0><td align=$left class=line>";
  echo "<b>"._TT("ts:item_nouveau")."</b>";
  echo "</td><td align=$right class=line nowrap>";
  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='administration'>";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>";
  echo "<INPUT TYPE='hidden' NAME='id' VALUE='".$id."'>";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>";
  echo "<INPUT TYPE='hidden' NAME='filtre' VALUE='".$filtre."'>";
  echo "<INPUT TYPE='text' VALUE='".$nouveau."' NAME='nouveau' SIZE='30'>";
  echo "&nbsp;<INPUT TYPE='submit' NAME='ajout' STYLE='width:20%' VALUE='"._TT("ts:bouton_ajouter")."'>";
  echo "</td> </tr>";
  echo "</FORM>\n";

  echo "<tr bgcolor=#f0f0f0>";
  echo "<td align=$left class=line colspan=2>";

  echo "<FORM NAME='returnable' ACTION='trad_lang.php' METHOD='POST'>\n";
  echo "<INPUT TYPE='hidden' NAME='langue' VALUE='".$langue."'>\n";
  echo "<INPUT TYPE='hidden' NAME='filtre' VALUE='".$filtre."'>\n";

  echo "<INPUT TYPE='hidden' NAME='etape' VALUE='administration'>\n";
  echo "<INPUT TYPE='hidden' NAME='module' VALUE='".$module."'>\n";
  echo "<INPUT TYPE='hidden' NAME='spip_lang' VALUE='".$spip_lang."'>";
  echo "<INPUT TYPE='hidden' NAME='lang_orig' VALUE='".$lang_orig."'>\n";
  echo "<INPUT TYPE='hidden' NAME='type' VALUE='".$type."'>\n";
  echo "<CENTER>\n";
  echo "<P><SELECT STYLE='width:100%' NAME='id' SIZE='10' ORDERED OnChange='returnable.submit()'>\n";
  while (list($idl,$val) = each($table_rech))
    {
      $opt = "";
      if ($idl == $id)
	$opt = "SELECTED";
      echo "<OPTION VALUE='".$idl."' ".$opt.">".$idl."\n";
    }
  echo "</SELECT>\n";
  echo "</CENTER>\n";
  echo "</td>";

  echo "<tr>";
  echo "<td align=$left class=line colspan=2>";
  echo "<b>".$id."</b>&nbsp;&nbsp;";

  echo "<input ALT=\""._TT('ts:lien_commentaire')."\" title=\""._TT('ts:lien_commentaire')."\" type=\"image\" value=\"Commenter\" HSPACE=0 border=0 src=\"./images/comment.gif\" name=\"commenter\" OnClick=\"ouvrirfen(300,180,'./trad_lang.php?spip_lang=$spip_lang&etape=commenter&affmodif=oui&lang_orig=$lang_orig&id=$id&nommodule=$nom_mod');return false;\">";   
  echo "&nbsp;".get_comment($id, $lang_orig, $nom_mod);

  echo "</td></tr>";

  reset($table_rech);

  echo "<tr><td align=$right colspan=2>";
  $item = affiche_modif($table_rech[$id]);
  $nbrows = calc_nb_row($item);
  echo "<TEXTAREA STYLE='width:100%' ROWS='".$nbrows."' COLS='45' WRAP='soft' NAME='texte'>".
    $item."</TEXTAREA>";
  echo "</td></tr>";

  echo "<tr><td align=$right colspan=2>";
  echo "<INPUT TYPE=\"submit\" NAME=\"effacer\" VALUE=\""._TT("ts:item_effacer")."\" OnClick=\"if (confirm('".addslashes(_TT("ts:confirmer_effacer", array("chaine"=>$id)))."')) effacer.submit(); else return false;\">\n";
  echo "&nbsp;&nbsp;<INPUT TYPE='submit' NAME='modifier' VALUE='"._TT("ts:bouton_modifier")."' >\n";
  echo "</FORM>\n";
  echo "</td></tr>";

  fin_table();
  fin_html_ts();
  exit;
}


exit;



?>

