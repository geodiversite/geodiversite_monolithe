<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

// blocs Z
$GLOBALS['z_blocs'] = array('content','extra1','extra2','head','head_js','header','footer');

// urls prorpes en minuscules
define ('_url_minuscules',1);

// autoriser le prive uniquement pour les admins
function autoriser_ecrire($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo';
}

// surcharger autoriser_rubrique_modifier_dist pour y reproduire autoriser_rubrique_publierdans_dist
// puisque diogene surcharge autoriser_rubrique_publierdans et permet donc aux rédacteurs de modifier les rubriques
function autoriser_rubrique_modifier($faire, $type, $id, $qui, $opt) {
	return
		($qui['statut'] == '0minirezo')
		and (
			!$qui['restreint'] or !$id
			or in_array($id, $qui['restreint'])
		);
}

// surcharger autoriser_ecrire_ticket_dist sinon autoriser_ecrire passe avant
function autoriser_ticket_ecrire($faire, $type, $id, $qui, $opt) {
	include_spip('inc/tickets_autoriser');
	return autoriser_ticket_ecrire_dist($faire, $type, $id, $qui, $opt);
}

// surcharger autoriser_modererforum_article pour ne pas envoyer de notifs avec un lien vers le privé aux rédacteurs
function autoriser_modererforum($faire, $type, $id, $qui, $opt) {
	return autoriser_ecrire($faire, $type, $id, $qui, $opt);
}

define('_PAGE_PUBLIER','upload');
define('_DIOGENE_REDIRIGE_PUBLICATION',true);
define('_DIOGENE_MODIFIER_PUBLIC',false);


?>