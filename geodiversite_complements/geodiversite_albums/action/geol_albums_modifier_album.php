<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function action_geol_albums_modifier_album_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	list($action,$id_grappe) = explode('/',$arg);

	if ($action != 'ouvrir' AND $action != 'fermer' AND $action != 'balader') {
		include_spip('inc/minipres');
		minipres(_T('action_inconnue',array('action'=>$action)));
	}

	if (!autoriser('modifier','grappe',$id_grappe)){
		include_spip('inc/minipres');
		minipres(_T('info_acces_interdit'));
	}
	
	include_spip('action/editer_grappe');
	
	if ($action == 'ouvrir') {
		grappe_modifier($id_grappe,array('type'=>'album_coop', 'acces'=>array('0minirezo','1comite')));
	} elseif ($action == 'fermer') {
		grappe_modifier($id_grappe,array('type'=>'album_perso', 'acces'=>array('0minirezo')));
	} elseif ($action == 'balader') {
		grappe_modifier($id_grappe,array('type'=>'balade', 'acces'=>array('0minirezo')));
	}

	include_spip('inc/invalideur');
	suivre_invalideur("id='id_grappe/$id_grappe'");
}

?>
