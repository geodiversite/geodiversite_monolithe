<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

function formulaires_editer_balade_charger_dist($id_collection='new', $retour=''){
	$valeurs = array();
	include_spip('inc/autoriser');
	if (!autoriser('modifier','collection',$id_collection))
		return false;
	$id_gis = sql_getfetsel("id_gis","spip_gis_liens","objet='collection' AND id_objet=$id_collection");
	$valeurs['id_gis'] = intval($id_gis) ? $id_gis : 'new';
	if (intval($id_gis)) {
		$wkt = sql_getfetsel("AsText(geo)","spip_gis","id_gis = $id_gis");
		include_spip('gisgeom_fonctions');
		$valeurs['geo'] = $wkt;
		$valeurs['geojson'] = wkt_to_json($wkt);
	}
	$valeurs['id_collection'] = $id_collection;
	$valeurs['editable'] = true;
	return $valeurs;
}

function formulaires_editer_balade_verifier_dist($id_collection='new', $retour=''){
	$erreurs = array();
	if (isset($_FILES['import']) && $_FILES['import']['error'] != 4) {
		include_spip('action/ajouter_documents');
		$infos_doc = verifier_upload_autorise($_FILES['import']['name']);
		if (in_array($infos_doc['extension'], array('gpx', 'kml'))) {
			unset($erreurs['titre']);
			unset($erreurs['zoom']);
		} else {
			$erreurs['import'] = _T('medias:erreur_upload_type_interdit', array('nom'=>$_FILES['import']['name']));
		}
	}
	return $erreurs;
}

function formulaires_editer_balade_traiter_dist($id_collection='new', $retour=''){
	$message = array();
	// récupérer le rang des articles de la balade et le mettre à jour
	$rangs = _request('rang');
	foreach ($rangs as $rang=>$id_article){
		$rang = $rang + 1;
		$ok = sql_updateq('spip_collections_liens',array('rang' => intval($rang)),"objet='article' AND id_objet = $id_article");
	}
	// éditer le gis associé
	if ($action_editer = charger_fonction("editer_gis",'action',true)) {
		list($id,$err) = $action_editer(_request('id_gis'));
	}
	// ne pas polluer l'url de retour avec des paramètres inutiles
	set_request('id_gis');
	set_request('id_objet');
	// invalider le cache
	include_spip('inc/invalideur');
	suivre_invalideur("id='gis/$id");
	// rediriger sur l'url de retour passée en paramètre
	if ($retour) {
		include_spip('inc/headers');
		$message['redirect'] = redirige_formulaire($retour);
	}
	return $message;
}

?>
