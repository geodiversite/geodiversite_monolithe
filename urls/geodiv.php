<?php

if (!defined("_ECRIRE_INC_VERSION")) return; // securiser

define('URLS_GEODIV_EXEMPLE', 'media12');

// http://doc.spip.org/@_generer_url_html
function _generer_url_geodiv($type, $id, $args='', $ancre='') {

	if ($type == 'forum') {
		include_spip('inc/forum');
		return generer_url_forum_dist($id, $args, $ancre);
	}

	if ($type == 'document') {
		include_spip('inc/documents');
		return generer_url_document_dist($id, $args, $ancre);
	}
	
	if ($type == 'article') {
		return _DIR_RACINE . 'media' . $id . ($args ? "?$args" : '') .($ancre ? "#$ancre" : '');
	}
	
	if ($type == 'rubrique') {
		return _DIR_RACINE . 'cat' . $id . ($args ? "?$args" : '') .($ancre ? "#$ancre" : '');
	}
	
	if ($type == 'mot') {
		return _DIR_RACINE . 'tag' . $id . ($args ? "?$args" : '') .($ancre ? "#$ancre" : '');
	}
	
	if ($type == 'auteur') {
		return _DIR_RACINE . $type . $id . ($args ? "?$args" : '') .($ancre ? "#$ancre" : '');
	}

	return _DIR_RACINE . $type . $id . ($args ? "?$args" : '') .($ancre ? "#$ancre" : '');
}

// retrouver les parametres d'une URL dite "html"
// http://doc.spip.org/@urls_html_dist
function urls_geodiv_dist($i, $entite, $args='', $ancre='') {
	$contexte = $GLOBALS['contexte']; // recuperer aussi les &debut_xx

	if (is_numeric($i))
		return _generer_url_geodiv($entite, $i, $args, $ancre);

	// traiter les injections du type domaine.org/spip.php/cestnimportequoi/ou/encore/plus/rubrique23
	if ($GLOBALS['profondeur_url']>0 AND $entite=='sommaire'){
		return array(array(),'404');
	}
	$url = $i;

	// Decoder l'url html, page ou standard
	$objets = 'article|breve|rubrique|mot|auteur|site|syndic';
	if (preg_match(
	',^(?:[^?]*/)?('.$objets.')([0-9]+)(?:\.html)?([?&].*)?$,', $url, $regs)
	OR preg_match(
	',^(?:[^?]*/)?('.$objets.')\.php3?[?]id_\1=([0-9]+)([?&].*)?$,', $url, $regs)
	OR preg_match(
	',^(?:[^?]*/)?(?:spip[.]php)?[?]('.$objets.')([0-9]+)(&.*)?$,', $url, $regs)) {
		$type = preg_replace(',s$,', '', table_objet($regs[1]));
		$_id = id_table_objet($regs[1]);
		$id_objet = $regs[2];
		$suite = $regs[3];
		$contexte[$_id] = $id_objet;
		if ($type == 'syndic') $type = 'site';
		return array($contexte, $type, null, $type);
	}

}

?>
