<?php

include_spip('inc/licence');

function formulaires_recherche_geol_charger_dist(){

	$valeurs = array(
		'action'=> generer_url_public('recherche'),
		);
	
	$valeurs['recherche'] = _request('recherche');
	
	// on recupere les donnees des licences pour la saisie
	if (defined('_DIR_PLUGIN_LICENCE')) {
		$licences = $GLOBALS['licence_licences'];
		foreach ($licences as $licence) {
			$valeurs['licences'][$licence['id']] = $licence['name'];
		}
		// on recupere la ou les licences saisies
		$id_licence = _request('id_licence');
		// si aucune licence saisie ou si Toutes est saisi
		if ((!is_array($id_licence)) OR ($id_licence[0] == ''))
			$id_licence = '';
	
		$valeurs['id_licence'] = $id_licence;
	}

	// on recupere la ou les categories saisies
	$valeurs['categories'] = _request('categories');
	
	$valeurs['echelle'] = _request('echelle');
	
	$valeurs['type_doc'] = _request('type_doc');
	
	return $valeurs;
}

?>