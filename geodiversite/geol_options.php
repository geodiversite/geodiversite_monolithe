<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

// titres sans numero
$table_des_traitements['TITRE'][]= 'supprimer_numero(typo(%s))';

// urls prorpes en minuscules
define ('_url_minuscules',1);

// autoriser le prive uniquement pour les admins
function autoriser_ecrire($faire, $type, $id, $qui, $opt) {
	return $qui['statut'] == '0minirezo';
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