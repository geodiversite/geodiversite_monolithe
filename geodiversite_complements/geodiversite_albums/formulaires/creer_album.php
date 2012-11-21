<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function formulaires_creer_album_charger_dist($retour=''){
	$valeurs = array();
	$valeurs['titre'] = '';
	$valeurs['editable'] = true;
	return $valeurs;
}

function formulaires_creer_album_verifier_dist($retour=''){
	$erreurs = array();
	if(!_request('titre'))
		$erreurs['titre'] = _T('info_obligatoire');
	return $erreurs;
}

function formulaires_creer_album_traiter_dist($retour=''){
	$message = $set = array();
	$set['titre'] = _request('titre');
	$set['liaisons'] = array('articles','auteurs');
	$set['type'] = 'album_perso';
	$set['acces'] = array('0minirezo');
	include_spip('action/editer_grappe');
	$id_grappe = grappe_inserer();
	$message['message_erreur'] = grappe_modifier($id_grappe,$set);
	include_spip('inc/headers');
	if ($retour)
		$message['redirect'] = redirige_formulaire(parametre_url($retour,'id_album',$id_grappe));
	else
		$message['redirect'] = redirige_formulaire(generer_url_entite($id_grappe,'grappe'));
	return $message;
}

?>
