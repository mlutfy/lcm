<?


// Name of the module
$nom_module = "LCM interface";

// Short name of the module
$nom_mod = "lcm"; 

// Default language
$lang_mere = 'en';

// Export function of the files
$export_function = "export_lcm";


// --------------------------------
// For SPIP modules
// --------------------------------

// Work directory
$dir_lang = "public/inc/lang";   
$dir_bak = "public/inc/lang/bak";

// File prefix for the language file
$lang_prefix = 'lcm_';
$lang_suffix = '.php';

// Language file header
$lang_prolog = "<"."?php\n\n// This is a LCM language file  --  Ceci est un fichier langue de LCM\n\n";

// Language file footer
$lang_epilog = "\n\n?".">\n";


if (defined("MODULE_LCM"))
  return;
define("MODULE_LCM", "1");


// Should return true or false
// The second param should be passed by ref
// and is initialized by the function
function export_lcm($lang_cible, $nomfic, $telech=0)
{
  global $left,$right;
  global $dir_lang, $lang_prefix, $lang_suffix;
  global $lang_prolog, $var_mod, $lang_epilog;

  $fic_nom = $lang_prefix.$lang_cible.$lang_suffix;
  $fic_exp = $dir_lang."/".$fic_nom;

  $tab = array();
  $conflit = array();  // TO CHANGE
  lire_lang($lang_cible, &$tab);

  ksort($tab);
  reset($tab);
  $initiale = "";
  $texte = $lang_prolog;
  $texte .= "\$GLOBALS[\$GLOBALS['idx_lang']] = array(\n";

  while (list($code, $chaine) = each($tab))
    {
      if (!array_key_exists($code, $conflit))
        {
          if ($initiale != strtoupper($code[0]))
            {
              $initiale = strtoupper($code[0]);
              $texte .= "\n\n// $initiale\n";
            }                                                                                                                                              
	  $texte .= "'".$code."' => '".texte_script($chaine)."',\n";
        }
    }

  // ecriture des chaines en conflit
  if (count($conflit))
    {
      ksort($conflit);
      reset($conflit);
      $texte .= "\n\n// PLUS_UTILISE\n";
      while (list($code, $chaine) = each($conflit))
        $texte .= "'".$code."' => '".texte_script($chaine)."',\n";
    }

  $texte = ereg_replace (",\n$", "\n\n);\n", $texte);
  $texte .= $lang_epilog;

  if ($telech == 1)
    {
       header("content-type: text/plain");
       header("Content-Disposition: attachment; filename=".$fic_nom);
       flush();
       echo $texte;
       exit;
    }
  else
    {
      $nomfic=$fic_exp;
      $f = @fopen($fic_exp, "wb");
      if (!$f)
        return false;
      fwrite($f, $texte);
      fclose($f);
      @chmod($fic_exp, 0666);
    }
  
  return true;
}


?>
