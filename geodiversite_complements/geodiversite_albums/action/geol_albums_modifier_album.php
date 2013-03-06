<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function action_geol_albums_modifier_album_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	list($action,$id_collection) = explode('/',$arg);

	if (!in_array($action, array('ouvrir', 'fermer', 'balader', 'supprimer'))) {
		include_spip('inc/minipres');
		minipres(_T('action_inconnue',array('action'=>$action)));
	}

	if (!autoriser('modifier','collection',$id_collection)){
		include_spip('inc/minipres');
		minipres(_T('info_acces_interdit'));
	}
	
	include_spip('action/editer_objet');
	
	if ($action == 'ouvrir') {
		objet_modifier('collection',$id_collection,array('type_collection'=>'coop'));
	} elseif ($action == 'fermer') {
		objet_modifier('collection',$id_collection,array('type_collection'=>'perso'));
	} elseif ($action == 'balader') {
		objet_modifier('collection',$id_collection,array('type_collection'=>'balade'));
	} elseif ($action == 'supprimer') {
		objet_modifier('collection',$id_collection,array('statut'=>'poubelle'));
	}

	include_spip('inc/invalideur');
	suivre_invalideur("id='id_collection/$id_collection'");
}

?>
