<?php

function geol_insert_head($flux){
	$flux .= '<link rel="stylesheet" href="'._DIR_PLUGIN_GEOL.'geol.css" type="text/css" media="projection, screen, tv" />';
	return $flux;
}

// utiliser le pipeline 'styliser' pour
// définir le squelette a utiliser si on est dans le cas
// d'une rubrique du repertoire
/*
function geol_styliser($flux){
	// si article, rubrique ou sommaire,
	// on cherche si spip clear doit s'activer
	if (($fond = $flux['args']['fond'])
	AND in_array($fond, array('article','rubrique'))) {
		
		$ext = $flux['args']['ext'];
		
		// cas dans une rubrique
		// uniquement si configuration du squelette pour le secteur en question
		if ($id_rubrique = $flux['args']['id_rubrique']) {
			if ($id_rubrique == lire_config('fjka/rubrique_clubs', 16)) {
				if ($squelette = test_squelette_annuaire($fond, $ext)) {
					$flux['data'] = $squelette;
				}
			}
		}
		if ($id_rubrique = $flux['args']['id_rubrique']) {
			if ($id_rubrique == lire_config('fjka/rubrique_actus', 4)) {
				if ($squelette = test_squelette_actus($fond, $ext)) {
					$flux['data'] = $squelette;
				}
			}
		}
		if ($id_rubrique = $flux['args']['id_rubrique']) {
			if ($id_rubrique == lire_config('fjka/rubrique_profs', 28)) {
				if ($squelette = test_squelette_profs($fond, $ext)) {
					$flux['data'] = $squelette;
				}
			}
		}
	}
	return $flux;
}

function test_squelette_annuaire($fond, $ext) {
	if ($squelette = find_in_path($fond."_annuaire.$ext")) {
		return substr($squelette, 0, -strlen(".$ext"));
	}
	return false;
}

function test_squelette_actus($fond, $ext) {
	if ($squelette = find_in_path($fond."_actus.$ext")) {
		return substr($squelette, 0, -strlen(".$ext"));
	}
	return false;
}

function test_squelette_profs($fond, $ext) {
	if ($squelette = find_in_path($fond."_profs.$ext")) {
		return substr($squelette, 0, -strlen(".$ext"));
	}
	return false;
}
*/
?>