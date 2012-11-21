<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('formulaires/lier_objets');

function formulaires_lier_album_charger_dist($objet, $source, $id_source, $identifiant){
	$valeurs = formulaires_lier_objets_charger($objet, $source, $id_source, $identifiant);
	$valeurs['_hidden'] = str_replace('lier_objets', 'lier_album', $valeurs['_hidden']);
	return $valeurs;
}

function formulaires_lier_album_verifier_dist($objet, $source, $id_source, $identifiant){
	$erreurs = formulaires_lier_objets_verifier($objet, $source, $id_source, $identifiant);
	return $erreurs;
}

function formulaires_lier_album_traiter_dist($objet, $source, $id_source, $identifiant){
	$message = formulaires_lier_objets_traiter($objet, $source, $id_source, $identifiant);
	$message['message_ok'] .= '<script type="text/javascript">if (window.jQuery) ajaxReload("albums");</script>';
	return $message;
}

?>
