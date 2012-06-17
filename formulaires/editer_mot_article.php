<?php

include_spip('inc/autoriser');

function formulaires_editer_mot_article_charger_dist($id_article='new', $id_groupe='', $retour=''){
	
	$id_mot = sql_getfetsel('mot.id_mot','spip_mots as mot left join spip_mots_liens as mots_liens ON (mot.id_mot=mots_liens.id_mot)','mots_liens.id_objet='.intval($id_article).' AND mots_liens.objet = "article" AND mot.id_groupe='.intval($id_groupe));
	
	$valeurs['id_article'] = $id_article;
	$valeurs['id_groupe'] = $id_groupe;
	$valeurs['id_mot'] = $id_mot;
	$valeurs['editable'] = true;
	
	if (!autoriser('modifier', 'article', $id_article))
		$valeurs['editable'] = false;

	return $valeurs;
}

function formulaires_editer_mot_article_verifier_dist($id_article='new', $id_groupe='', $retour=''){
	$erreurs = array();
	return $erreurs;
}

function formulaires_editer_mot_article_traiter_dist($id_article='new', $id_groupe='', $retour=''){
	
	$message = array('editable'=>true, 'message_ok'=>'');
	
	$id_mot_ancien = sql_getfetsel('mot.id_mot','spip_mots as mot left join spip_mots_liens as mots_liens ON (mot.id_mot=mots_liens.id_mot)','mots_liens.id_objet='.intval($id_article).' AND mots_liens.objet = "article" AND mot.id_groupe='.intval($id_groupe));
	
	include_spip('action/editer_liens');
	// si aucun mot selectionne on delie le mot de ce groupe
	if(!$id_mot = _request('id_mot')){
		objet_dissocier(array("mot"=>$id_mot_ancien), array("article"=>$id_article));
	} else {
		if ($id_mot_ancien != $id_mot) {
			// on delie l'ancien mot
			objet_dissocier(array("mot"=>$id_mot_ancien), array("article"=>$id_article));
			// on lie le nouveau
			objet_associer(array("mot"=>$id_mot), array("article"=>$id_article));
		}
	}
	
	// on invalide le cache
	include_spip('inc/invalideur');
	suivre_invalideur("id='id_article/$id_article'");
	
	if ($retour) {
		include_spip('inc/headers');
		$message .= redirige_formulaire($retour);
	}
	
	return $message;
	
}

?>
