<?php
// This file is part of the learningtimecheck plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang file
 *
 * @package    mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
global $SITE;

// Capabilities.
$string['sharedresource:addinstance'] = 'Peut ajouter une instance';
$string['sharedresource:manageblocks'] = 'Gérer les blocs dans la librairie';
$string['sharedresource:manageclassifications'] = 'Gérer les classifications';
$string['sharedresource:manageclassificationtokens'] = 'Gérer les tokens de classification';

$string['privacy:metadata'] = "Le modules Ressource Mutualisée ne stocke aucune données en relation avec les utilisateurs.";

$string['accesscontrol'] = 'Contrôle d\'accès';
$string['add'] = 'Ajouter';
$string['addclassification'] = 'Ajouter une classification';
$string['addclassificationtitle'] = 'Ajout de classification';
$string['addclassificationvalue'] = 'Ajouter une valeur de classification';
$string['adddeploy'] = 'Déploiement d\'une archive d\'activité';
$string['addfile'] = 'Ajouter aux fichiers du cours';
$string['addfiletocourse'] = 'Ajouter aux fichiers du cours';
$string['addheader'] = 'Ajouter une nouvelle ressource';
$string['addinstance'] = 'Ajouter une ressource mutualisée';
$string['addlocal'] = 'Relocaliser une ressource distante';
$string['addltiinstall'] = 'Installer un outil externe à partir de la librairie';
$string['addmetadataform'] = 'Ajouter un formulaire de métadonnées';
$string['addremote'] = 'Ajouter une ressource distante';
$string['addshared'] = 'Ajouter une ressource partagée';
$string['addsharedresource'] = 'Ajouter une ressource mutualisée';
$string['addsharedresourcetypefile'] = 'Ajouter une ressource mutualisée';
$string['addtocourse'] = 'Ajouter au cours';
$string['addtoken'] = 'Ajouter une valeur de classification';
$string['all'] = 'Toutes les sources';
$string['allowmultipleaccessvalues'] = 'Autoriser une sélection multiple';
$string['appliedsqlrestrict'] = 'Clause appliquée&nbsp;:&ensp;';
$string['configarticlequantity'] = 'Nombre d\'articles';
$string['attributes'] = 'Liste des attributs renseignés dans le formulaire et enregistrés&nbsp;:&ensp;';
$string['author'] = 'Auteur';
$string['backadminpage'] = 'Retour à la page des réglages';
$string['backclassifpage'] = 'Retour à la page de configuration des classifications';
$string['backtoconfig'] = 'Retour à la page de configuration des classifications';
$string['backtocourse'] = 'Revenir au cours';
$string['badcourseid'] = 'Identifiant de cours invalide';
$string['badsqlrestrict'] = 'La clause devrait être le contenu d\'une clause WHERE';
$string['bycapability'] = 'Par capacité';
$string['byprofilefield'] = 'Par champ de profil';
$string['cancelform'] = 'Annuler';
$string['cannotrestore'] = 'l\'entrée du catalogue de ressources est manquante - problème de restauration : {$a}';
$string['choose'] = 'Choisir';
$string['chooseparameter'] = 'Choisir le paramètre';
$string['chooseprovidertopushto'] = 'En mutualisant la ressource vers un fournisseur externe, vous:<ul><li>Déplacez la
ressource</li><li>Supprimez la ressource stockée dans cette plate-forme</li><li>Permettez à d\'autres plates-formes connectées
à ce fournisseur d\'utiliser cette ressource</li><li>Déplacerez la position de cette ressource dans toutes ses utilisations à
l\'intérieur du réseau.</li></ul></p>';
$string['classification'] = 'Classification&nbsp;:&ensp;';
$string['classificationacls'] = 'Contrôle d\'accès';
$string['classificationconfiguration'] = 'Configuration des classifications';
$string['classificationconfiguration_desc'] = 'Cette <a href="{$a}">page supplémentaire</a> permet la configuration des
classifications pour la norme choisie.';
$string['classificationname'] = 'Intitulé de la classification';
$string['classifications'] = 'Classification';
$string['classificationsearch'] = 'Recherche sur les classifications';
$string['classificationupdate'] = 'Modification de la classification';
$string['clearthumbnail'] = 'Supprimer la vignette';
$string['completeform'] = 'Entrez les données dans le formulaire ci-dessous';
$string['configaccesscontrol'] = 'Contrôle d\'accès';
$string['configaccesscontrol_desc'] = 'Activer le contrôle d\'accès par les champs de profil.';
$string['configallowlocalfiles'] = 'Lors de la création d\'une nouvelle ressource de type fichier, permettre des liens vers les
fichiers disponibles sur un système de fichiers local, par exemple sur un CD ou sur un disque dur. Cela peut être utile dans une
classe où tous les étudiants ont accès a un volume réseau commun ou si des fichiers sur un CD sont nécessaires. Il est possible
que l\'utilisation de cette fonctionnalité requière une modification des réglages de sécurité de votre navigateur.';
$string['configallowmultipleaccessvalues'] = 'Plusieurs valeurs du champ de contrôle peuvent être choisies pour valider l\'accès';
$string['configarticlequantity_desc'] = 'Configure le nombre de nouvelles ressources publiées dans le flux';
$string['configautofilerenamesettings'] = 'Mettre à jour automatiquement les références vers d\'autres fichiers et dossiers lors
d\'un changement de nom dans la gestion des fichiers.';
$string['configbackupindex'] = 'Sauvegarder le référentiel des ressources';
$string['configbackupindex_desc'] = 'Lors de la sauvegarde d\'un cours, sauvegarder TOUTES les entrées de catalogue correspondantes
(y compris les fichiers locaux)&nbsp;?';
$string['configblockdeletingfilesettings'] = 'Empêcher la suppression de fichiers et dossiers référencés par des ressources.
Veuillez remarquer que les images et autres fichiers référencés dans le code HTML ne sont pas protégés par ce réglage.';
$string['configclassification'] = 'Configurer';
$string['configdefaulturl'] = 'URL par défaut';
$string['configdefaulturl_desc'] = 'Cette valeur est utilisée pour préremplir l\'URL lors de la création d\'une nouvelle ressource
pointée par URL.';
$string['configdefaultuserfield'] = 'Champ de profil utilisateur par défaut pour le contrôle d\'accès.';
$string['configenablerssfeeds'] = 'Activer les flux RSS d\'exposition des ressources';
$string['configenablerssfeedsdesc'] = 'Si ce réglage est activé, un flux RSS peut être obtenu qui donnera la liste des ressources
récentes ajoutées à la librairie';
$string['configfilterexternalpages'] = 'L\'activation de ce réglage permettra le filtrage des ressources externes (pages web,
fichiers HTML déposés) par les filtres définis dans le site (comme les liens des glossaires). Lorsque ce réglage est actif,
l\'affichage de vos pages sera ralenti de façon sensible. À utiliser avec précaution.';
$string['configforeignurlscheme'] = 'URL d\'accès aux ressources';
$string['configforeignurlsheme_desc'] = 'Forme générale de l\'Url. Utiliser \'&lt;%%%%ID%%%%&gt;\' comme emplacement de
l\'Identifiant Unique de Ressource';
$string['configframesize'] = 'Taille du cadre';
$string['configframesize_desc'] = 'Quand une page web ou un fichier est affiché dans un cadre (frame), cette valeur indique
(en pixels) la taille du cadre contenant la navigation (en haut de la fenêtre).';
$string['configfreezeindex'] = 'Geler le référentiel de ressources';
$string['configfreezeindex_desc'] = 'Lors de la sauvegarde d\'un cours, ne sauvegarder aucun fichier physique du référentiel
commun&nbsp;?';
$string['confighidemetadatadesc'] = 'Cacher la description de la norme';
$string['configparametersettings'] = 'Détermine si par défaut la zone de configuration des paramètres est affichée ou non, lors
de l\'ajout de nouvelles ressources. Après la première utilisation, ce réglage devient individuel.';
$string['configpluginscontrol'] = 'Contrôle des plugins de métadonnées';
$string['configpluginscontrol_desc'] = 'Ce paramètre permet de choisir le plugin utilisé pour les métadonnées lors de l\'indexation
de la ressource.';
$string['configpopup'] = 'Fenêtre';
$string['configpopup_desc'] = 'Lors de l\'ajout d\'une ressource pouvant être affichée dans une fenêtre pop-up, cette option
doit-elle être activée par défaut ?';
$string['configpopupdirectories'] = 'Montrer les liens directs';
$string['configpopupdirectories_desc'] = 'Les fenêtres pop-up affichent le lien du dossier par défaut';
$string['configpopupheight'] = 'Hauteur par défaut (en pixels)';
$string['configpopupheight_desc'] = 'Hauteur par défaut des fenêtres pop-up';
$string['configpopuplocation'] = 'Montrer la barre d\'adresse';
$string['configpopuplocation_desc'] = 'La barre de l\'URL est affichée par défaut dans les fenêtres pop-up';
$string['configpopupmenubar'] = 'Montrer la barre de menu';
$string['configpopupmenubar_desc'] = 'La barre des menus est affichée par défaut dans les fenêtres pop-up';
$string['configpopupresizable'] = 'Autoriser le redimensionnement';
$string['configpopupresizable_desc'] = 'Les fenêtres pop-up sont redimensionnables par défaut';
$string['configpopupscrollbars'] = 'Autoriser le défilement';
$string['configpopupscrollbars_desc'] = 'Les barres de défilement sont affichées par défaut dans les fenêtres pop-up';
$string['configpopupstatus'] = 'Montrer la barre d\'état';
$string['configpopupstatus_desc'] = 'La barre d\'état est affichée par défaut dans les fenêtres pop-up';
$string['configpopuptoolbar'] = 'Montrer la barre d\'outils';
$string['configpopuptoolbar_desc'] = 'La barre des outils est affichée par défaut dans les fenêtres pop-up';
$string['configpopupwidth'] = 'Largeur par défaut (en pixels)';
$string['configpopupwidth_desc'] = 'Largeur par défaut des fenêtres pop-up';
$string['configrestoreindex'] = 'Restaurer la librairie';
$string['configrestoreindex_desc'] = 'Lors d\'une restauration, restaurer TOUTES les entrées de catalogue (y compris les fichiers
locaux) ?  Ceci ne remplacera pas les entrées et métadonnées existantes.';
$string['configschema'] = 'Standard des métadonnées';
$string['configschema_desc'] = 'Ce choix détermine le plugin utilisé pour le formulaire de métadonnées';
$string['configsecretphrase'] = 'Cette phrase secrète est utilisée pour générer le code crypté pouvant être envoyé comme paramètre
à certaines ressources. Ce code crypté est fabriqué en concaténant une valeur md5 de l\'adresse IP du current_user et de cette
phrase secrète, par exemple : code = md5(IP.secretphrase). Ceci permet à la ressource recevant le paramètre de vérifier la
connexion pour plus de sécurité.';
$string['configscormintegration'] = 'Mode d\'intégration scorm';
$string['configscormintegration_desc'] = 'Détermine comment les scorms sont intégrés dans les cours à partir de la librairie.';
$string['configwebsearch'] = 'URL affichée lors de l\'ajout d\'une page web ou d\'un lien, pour permettre à l\'utilisateur de
rechercher l\'URL désirée.';
$string['configwindowsettings'] = 'Détermine si, par défaut, la zone de configuration des fenêtres est affichée ou non, lors de
l\'ajout de nouvelles ressources. Après la première utilisation, ce réglage devient individuel.';
$string['confirmclassifdeletion'] = 'Supprimer une classification peut avoir un impact important sur votre base de ressources.
Confirmez-vous la suppression ?';
$string['contentintegration'] = 'Intégration de contenus';
$string['conversioncancelled'] = 'conversion annulée';
$string['conversioncancelledtocourse'] = 'Conversion annulée. Vous allez être redirigés vers la gestion des activités';
$string['conversioncancelledtolibrary'] = 'Conversion annulée. Vous allez être redirigés vers la librairie';
$string['convert'] = 'Convertir la sélection';
$string['convertall'] = 'Mettre en commun et indexer les ressources';
$string['convertback'] = 'Rappatrier une ressource commune';
$string['convertingsharedresource'] = 'Conversion de la ressource mutualisée {$a->id} : {$a->name}';
$string['correctsave'] = '<h2> Ressource enregistrée correctement </h2>';
$string['d'] = 'j(s)';
$string['datachanged'] = 'Modifications effectuées';
$string['datefmt'] = '%x';
$string['datesearch'] = 'Recherche en champ de type date';
$string['day'] = '- Jour -';
$string['days'] = 'Jour(s)';
$string['defaultselect'] = 'Reinitialiser à la sélection par défaut';
$string['defaultuserfield'] = 'Champ de profil utilisateur (défaut)';
$string['deleteconfirm'] = 'Etes-vous certain de vouloir supprimer cette classification&nbsp;?';
$string['description'] = 'Description';
$string['directlink'] = 'Lien direct vers ce fichier';
$string['disabled'] = 'Désactivé';
$string['disablednode'] = 'Le noeud {$a} est désactivé dans ce schéma d\'application';
$string['discouragednode'] = 'Champ déconseillé (compatibilité)';
$string['displayoptions'] = 'Options d\'affichage';
$string['directaccess'] = 'Le lien ci-dessus accède directement à la ressource, en général en la téléchargeant.';
$string['dmdescription'] = 'Description du modèle de métadonnées';
$string['dmuse'] = 'Utilisation du modèle de métadonnées';
$string['dmused'] = 'Modèle utilisé';
$string['down'] = 'Descendre';
$string['durationdescr'] = 'Description d\'une durée';
$string['durationsearch'] = 'Recherche d\'une durée';
$string['edit'] = 'Modifier';
$string['editclassificationtable'] = 'Modifier les entrées de classification';
$string['educational'] = 'Aspects éducatifs';
$string['enabled'] = 'Actif';
$string['entry'] = 'Entrée';
$string['erroraclmisconf'] = 'ERREUR : ID de taxonomie ou ID de ressource manquant.';
$string['erroraddinstance'] = 'ERREUR : Echec de création de l\'instance de resssource';
$string['errorcmaddition'] = 'ERREUR : Le module de cours n\'a pas pu être ajouté';
$string['errorcmsectionbinding'] = 'ERREUR : La section n\'a pu être enregistrée dans le module de cours';
$string['errordeletesharedresource'] = 'ERREUR : Echec de l\'effacement de fichier d\'une ressource mutualisée ({$a})';
$string['erroremptytokenvalue'] = 'ERREUR : Une valeur non vide de taxonomie est attendue';
$string['erroremptyurl'] = 'ERREUR : Tentative de créer une ressource mutualisée sans URL d\'accès';
$string['errorinvalididentifier'] = 'ERREUR : L\'identifiant {$a} ne correspond à aucune ressource connue';
$string['errormetadata'] = 'Erreurs trouvées pour les champs suivants (ces erreurs seront affichées en rouge dans le
formulaire)&nbsp;:&ensp;';
$string['errornometadataenabled'] = 'ERREUR : Aucun plugin de métadonnées n\'est activé. Les métadonnées ne peuvent être
configurée.';
$string['errornometadataplugins'] = 'ERREUR : Aucun plugin de métadonnées installé';
$string['errornoticecreation'] = 'ERREUR : Impossible de créer la notice';
$string['errornotinstalled'] = 'ERREUR : Le module "ressource mutualisée" n\'est pas installé !!';
$string['errorscormtypelocalwithnofile'] = 'ERREUR : Un scorm installé localement nécessite un fichier local.';
$string['errorsectionaddition'] = 'ERREUR : Impossible de créer une nouvelle section';
$string['errorupdatecm'] = 'ERREUR de mise à jour de la ressource mutualisée (instance)';
$string['existothermetadata'] = 'Une fiche de métadonnées pour cette ressource existe déjà dans une autre norme. <br/>La
validation de ce formulaire pour une nouvelle fiche entraînera la suppression des anciennes métadonnées.';
$string['existsignorechanges'] = 'la donnée existe mais n\'est pas modifiée';
$string['export'] = 'Exporter vers un référentiel externe';
$string['failadd'] = 'Echec de la sauvegarde (ajout) de la ressource à la base de données';
$string['failupdate'] = 'Echec de la sauvegarde (mise à jour) de la ressource à la base de données';
$string['fieldname'] = 'Nom du champ';
$string['file'] = 'Fichier ou lien';
$string['fileadvice'] = '<p>La représentation physique de la ressource a été ajoutée dans les fichiers locaux du cours. Vous
allez être redirigé vers cet espace des fichiers. Aucun module n\'a cependant été ajouté au cours.</p>';
$string['filenotfound'] = 'Désolé, le fichier demandé ne peut être trouvé. Raison : {$a}';
$string['filesharedresource'] = 'Ressource mutualisée (fichier ou url)';
$string['fileuploadfailed'] = 'Echec du téléchargement';
$string['fillcategory'] = 'Tous les champs de la catégorie sont vides. Remplissez-en au moins un.';
$string['fillprevious'] = 'Le champ précédent est vide. Veuillez le remplir avant de rajouter un autre champ';
$string['filtername'] = 'Nom du filtre';
$string['forcedownload'] = 'Forcer le téléchargement';
$string['frameifpossible'] = 'Cadre, si posible';
$string['frameifpossible_help'] = 'Si activé, la ressource est présentée dans un cadre autonome';
$string['frozenfile'] = 'Le fichier ressource ne peut être changé car il existe déjà des versions ultérieures de cette ressource.
Les métadonnées peuvent cependant toujours être modifiées.';
$string['frozenurl'] = 'L\'URL de la ressource ne peut être changée car il existe déjà des versions ultérieures de cette ressource.
Les métadonnées peuvent cependant toujours être modifiées.';
$string['gometadataform'] = 'Etape suivante';
$string['gometadataform2'] = 'Documenter la ressource';
$string['h'] = 'h(s)';
$string['hideclassification'] = 'Désactiver la classification';
$string['hours'] = 'Heure(s)';
$string['idname'] = 'Nom du champ id';
$string['incorrectdate'] = 'Date rentrée non correcte <br/>';
$string['incorrectday'] = 'Le nombre de jours doit être supérieur à 1 <br/>';
$string['incorrecthour'] = 'Le nombre d\'heures doit être supérieur à 1 <br/>';
$string['incorrectminute'] = 'Le nombre de minutes doit être supérieur à 1 <br/>';
$string['incorrectsecond'] = 'Le nombre de secondes doit être supérieur à 1 <br/>';
$string['indexer'] = 'Documentaliste';
$string['integerday'] = 'Le nombre de jours doit être un entier <br/>';
$string['integerhour'] = 'Le nombre d\'heures doit être un entier <br/>';
$string['integerminute'] = 'Le nombre de minutes doit être un entier <br/>';
$string['keepnavigationvisible'] = 'Garder la navigation visible';
$string['keyword'] = 'Mot-clef';
$string['keywordpunct'] = 'Pas de ponctuation dans un mot-clé <br/>';
$string['keywords'] = 'Mots-clefs';
$string['labelname'] = 'Nom du champ label';
$string['language'] = 'Langue';
$string['layout'] = 'Mise en forme';
$string['libraryengine'] = 'Moteur de librarie';
$string['license'] = 'License';
$string['local'] = 'Ressources '.$SITE->shortname;
$string['localizeadvice'] = '<p>La ressource a été relocalisée, cela veut dire qu\'une copie de la ressource originale est désormais
disponible dans le cours, dissociée de la ressource mutualisée d\'origine. Si cette ressource a une représentation physique, le
fichier qui la représente est stocké dans les fichiers locaux du cours.</p>';
$string['localizetocourse'] = 'Localiser dans le cours';
$string['location'] = 'Emplacement de la ressource';
$string['m'] = 'm(s)';
$string['mandatory'] = 'Obligatoire';
$string['matchedvalues'] = 'Valeur';
$string['medatadaconfiguration_desc'] = 'Cette <a href="{$a}">page supplémentaire</a> permet la configuration des formulaires de
metadonnées pour chaque rôle, et de choisir les widgets de recherche.';
$string['metadata'] = 'Métadonnées';
$string['metadataconfiguration'] = 'Configuration des métadonnées';
$string['metadatadescr'] = 'Description des métadonnées';
$string['minutes'] = 'Minute(s)';
$string['missingid'] = 'Le nom entré pour l\'id n\'existe pas dans la table de la base de données <br/>';
$string['missinglabel'] = 'Le nom entré pour le label n\'existe pas dans la table de la base de données <br/>';
$string['missingnameid'] = 'Veuillez rentrer une variable dans le champ "Nom du champ id" <br/>';
$string['missingnamelabel'] = 'Veuillez rentrer une variable dans le champs "Nom du champ label"<br/>';
$string['missingnameparent'] = 'Veuillez rentrer une variable dans le champs "Nom du champ parent"<br/>';
$string['missingnametable'] = 'Veuillez rentrer une variable dans le champ "Nom de la table" <br/>';
$string['missingordering'] = 'Le nom entré pour l\'ordering n\'existe pas dans la table de la base de données <br/>';
$string['missingparent'] = 'Le nom entré pour le parent n\'existe pas dans la table de la base de données <br/>';
$string['missingresource'] = 'choisir une URL ou un fichier';
$string['missingtable'] = 'La table n\'existe pas dans la base de données <br/>';
$string['modulename'] = 'Ressource mutualisée';
$string['modulename_help'] = 'Une ressource partagée est naturellement partagée dans tout le site ou dans une catégorie de cours.
Les ressources partagées sont renseignées par des métadonnées complètes qui permettent une recherche et une exploration des
ressources. Les librairies peuvent être exposées au réseau de Moodle pour améliorer la mutualisation des ressources pédagogiques.';
$string['modulenameplural'] = 'Ressources mutualisées';
$string['month'] = '- Mois -';
$string['mtdfieldid'] = 'Id du champ';
$string['mtdfieldname'] = 'Nom du champ';
$string['mtdvalue'] = 'Valeur';
$string['name'] = 'Nom';
$string['newwindow'] = 'Nouvelle fenêtre';
$string['newresizable'] = 'Redimensionable';
$string['newlocation'] = 'Position';
$string['newmenubar'] = 'Avec barre de menu';
$string['newtoolbar'] = 'Avec barre d\'outil';
$string['newstatus'] = 'Avec ligne d\'état';
$string['newwidth'] = 'Largeur';
$string['newheight'] = 'Hauteur';
$string['newscrollbars'] = 'Autoriser le défilement';
$string['newdirectories'] = 'Afficher les liens de répertoires';
$string['noaccessform'] = 'Votre catégorie d\'utilisateur n\'a pas accès à ce formulaire';
$string['noclassification'] = 'Aucune classification repertoriée';
$string['node'] = 'Branche';
$string['nodescription'] = 'Il n\'y a pas de description disponible pour cette norme.';
$string['nometadataplugin'] = 'L\'administrateur n\'a pas configuré le schéma de métadonnées applicable aux ressources.';
$string['none'] = '(pas de restriction)';
$string['noplugin'] = 'Pas de métadonnées';
$string['noprovidertopushto'] = 'Votre plate-forme n\'est raccordée à aucun fournisseur de mutualisation.';
$string['noresourcesfound'] = 'Aucune ressource dans le catalogue';
$string['noresourcestoconvert'] = 'Aucune ressource à convertir';
$string['nosharedresources'] = 'Aucune ressource mutualisée publiée dans ce cours';
$string['notaxonomies'] = 'Aucune taxonomie active disponible';
$string['notselectable'] = 'Non sélectionnables';
$string['nowidget'] = 'Aucun widget de recherche défini par l\'admin';
$string['numericsearch'] = 'Recherche en champ de type numeric';
$string['onekeyword'] = 'Un seul mot-clé par champ (pas d\'espaces)<br/>';
$string['orderingmin'] = 'Ordering minimum';
$string['orderingname'] = 'Nom du champ ordering';
$string['othersearch'] = 'Nouvelle recherche';
$string['pagewindow'] = 'Même fenêtre';
$string['pan'] = 'Pan';
$string['parameter'] = 'Paramètre';
$string['parameters'] = 'Paramètres';
$string['parentname'] = 'Nom du champ parent';
$string['popupresource'] = 'Cette ressource apparaîtra dans une fenêtre popup.';
$string['popupresourcelink'] = 'Dans le cas contraire, cliquez sur le lien suivant : {$a}';
$string['pluginadministration'] = 'Administration du plugins';
$string['pluginname'] = 'Ressource mutualisée';
$string['predatanotprovided'] = '<- Non encore fourni ->';
$string['preview'] = 'Prévisualiser';
$string['profilefieldname'] = 'Champ de profil';
$string['profilefieldplaceholder'] = 'Code de champ précédé de profile_field: ou user:';
$string['profilefieldsyntax'] = 'La règle de champs de profil {$a} n\'a pas la syntaxe attendue';
$string['pushtosingleprovider'] = '<p>Votre plate-forme ne connait qu\'un fournisseur externe mutualisé : {$a}.</p><p>En
mutualisant la ressource vers ce fournisseur externe, vous:<ul><li>Déplacez la ressource</li><li>Supprimez la ressource
stockée dans cette plate-forme</li><li>Permettez à d\'autres plates-formes connectées à ce fournisseur d\'utiliser cette
ressource</li><li>Déplacerez la position de cette ressource dans toutes ses utilisations à l\'intérieur du réseau.
</li></ul></p>';
$string['readnotice'] = 'Consulter la fiche descriptive';
$string['remotesearchquery'] = 'Recherche dans les référentiels de ressources';
$string['remotesearchresults'] = 'Résultats de recherche ';
$string['remotesubmission'] = 'Soumission de ressource';
$string['repository'] = 'Entrepôt';
$string['repositorytoresource'] = 'Lirairie vers cours';
$string['resource_consumer_description'] = 'En publiant ce service, vous permettez aux plates-formes "fournisseurs" de
vérifier la consommation de leurs ressources sur cet hôte.<br/><br/>En vous abonnant à ce service, vous pouvez vérifier
la consommation de vos ressources sur les sites "consommateur" distant.<br/><br/>';
$string['resource_consumer_name'] = 'Service de consommation de ressources';
$string['resource_provider_description'] = 'En publiant ce service, vous permettez aux "consommateurs" distants de venir
utiliser les ressources partagées de votre catalogue.<br/><br/>En vous abonnant à ce service, vous fournissez votre catalogue
local aux plates-formes "consommateur" distants.<br/><br/>';
$string['resource_provider_name'] = 'Service de fourniture de ressources';
$string['resourceacls'] = 'Contrôle d\'accès sur la ressource: {$a}';
$string['resourcebuilt'] = 'Nouvelle ressource : {$a}';
$string['resourceconversion'] = 'Conversion de ressources';
$string['resourceexists'] = 'Il existe déjà une ressource de même contenu';
$string['resourceintheway'] = 'Une ressource de même contenu est déjà présente dans la librairie.';
$string['resourcenewdescription'] = 'Nouvelle description';
$string['resourceolddescription'] = 'Ancienne description';
$string['resources'] = 'Ressources';
$string['resourceaskupdate'] = 'Voulez vous consulter et modifier la description de la ressource existante ?';
$string['resourcetorepository'] = 'Cours vers Librairie';
$string['resourcetypefile'] = 'Identification de la ressource';
$string['resourceupdate'] = 'En confirmant, vous autorisez la mise à jour de la description avec les nouvelles données.
Voulez-vous continuer ?';
$string['restrictclassification'] = 'Restreindre une classification';
$string['restrictsql'] = 'Entrez une clause SQL WHERE pour restreindre une classification : ';
$string['rss'] = 'RSS (en dévelopement)';
$string['s'] = 's(s)';
$string['saveselection'] = 'Enregistrer la sélection';
$string['savesqlrestrict'] = 'Enregistrer';
$string['schema'] = 'Norme';
$string['score'] = 'Score';
$string['searchfor'] = 'Chercher';
$string['searchheader'] = 'Critères de recherche';
$string['searchin'] = 'Rechercher dans';
$string['searchinlibrary'] = 'Rechercher dans la librairie';
$string['searchinsubs'] = 'Rechercher dans les sous catégories';
$string['searchsharedresource'] = 'Chercher une ressource mutualisée';
$string['seconds'] = 'Seconde(s)';
$string['selectable'] = 'Sélectionnables';
$string['selectall'] = 'tout';
$string['selectclassification'] = 'Sélection et configuration des classifications apparentes';
$string['selectnone'] = 'aucun';
$string['selectsearch'] = 'Recherche en champ de type select';
$string['selecttaxons'] = 'Choisir les taxons';
$string['semantic_density'] = 'Densité sémantique';
$string['serverurl'] = 'URL Serveur';
$string['sharedresourcedetails'] = 'Détails sur les ressources mutualisées';
$string['sharedresourceintro'] = 'Introduction';
$string['sharedresourcenotice'] = 'Notice de la ressource : {$a}';
$string['sharedresourceservice_name'] = 'Services de mutualisation de ressources';
$string['sharedresourcetypefile'] = 'Ressource mutualisée';
$string['sharingcontext'] = 'Niveau de partage';
$string['showclassification'] = 'Activer la classification';
$string['somewhere'] = 'quelque part (site, catégorie ou cours)';
$string['sqlmapping'] = 'Correspondances SQL';
$string['sqloptions'] = 'Options SQL';
$string['sqlrestriction'] = 'Restriction SQL';
$string['step2'] = 'Passer à l\'étape 2';
$string['subplugintype_sharedmetadata'] = 'Modèle de métadonnées';
$string['subplugintype_sharedmetadata_plural'] = 'Modèles de métadonnées';
$string['successfulmodification'] = 'Modification effectuée';
$string['system'] = 'Administrateur';
$string['systemcontext'] = 'Partage global site';
$string['tablename'] = 'Nom de la table';
$string['taxonchoicetitle'] = 'Sélection des taxons apparents';
$string['taxonpath'] = 'TAXON';
$string['taxons'] = 'Taxons';
$string['taxonselection'] = 'Sélection de taxons';
$string['technical'] = 'Technique';
$string['textsearch'] = 'Recherche en champ de type texte';
$string['thumbnail'] = 'Vignette (35k max)';
$string['title'] = 'Titre';
$string['tokenvalue'] = 'Valeur de taxon';
$string['typical_age_range'] = 'Tranche d\'âge typique';
$string['typical_learning_time'] = 'Temps d\'apprentissage nominal';
$string['unselectall'] = 'aucun';
$string['up'] = 'Remonter';
$string['updatebutton'] = 'Effectuer la modification';
$string['updateclassification'] = 'Modifier la classification';
$string['updateclassificationvalue'] = 'Modifier une valeur de classification';
$string['updatemetadata'] = 'Mettre à jour la configuration';
$string['updatemetadataform'] = 'Mettre à jour la description';
$string['updateresourcepageoff'] = 'Quitter mode édition';
$string['updateresourcepageon'] = 'Passer en mode édition';
$string['updatesharedresource'] = 'Mettre à jour la ressource mutualisée';
$string['updatesharedresourcetypefile'] = 'Mettre à jour la ressource mutualisée';
$string['url'] = 'URL de la ressource mutualisée';
$string['urlbuilt'] = 'Nouvelle ressource : {$a}';
$string['used'] = 'Utilisée {$a} fois';
$string['validateform'] = 'Valider';
$string['variablename'] = 'Nom de la variable';
$string['vcard'] = 'Description de la structure Vcard';
$string['view_pageitem_page_embedded_content'] = 'Voir la ressource dans la page';
$string['view_resource_info'] = 'Voir les infos sur la ressource';
$string['vol'] = 'Vol';
$string['widget'] = 'Widgets de recherche';
$string['wrongform'] = '<h2> Le formulaire n\'a pas été renseigné correctement. Retour au formulaire dans 15sec</h2>';
$string['year'] = '- Année -';
$string['urlchange'] = 'Modification de la source';
$string['urlchange_help'] = '<b>Attention ! Changer l\'URL construira une nouvelle ressource liée à celle-ci.</b>';

$string['taxonpotentialselector'] = 'Taxons possibles';
$string['selectedtaxonselector'] = 'Taxons actifs';
$string['selectedtaxons'] = 'Taxons actifs';
$string['pottaxonsmatching'] = 'Taxons potentiels correspondants';
$string['pottaxons'] = 'Taxons potentiels';
$string['selectedtaxons'] = 'Taxons sélectionnés';

$string['backtoclassifications'] = 'Retour à la liste des taxonomies';
$string['classificationvalues'] = 'Taxons';
$string['token'] = 'Taxon';
$string['addtoken'] = 'Ajouter un nouveau taxon';
$string['goup'] = 'Remonter d\'un niveau';
$string['notsupportedyet'] = 'Modifier des taxons dans une autre table que sharedresource_taxonomy n\'est pas encore supporté.';

// Help Strings.

$string['sharedresourceservice_description'] = 'Permet les échanges de service entre fournisseurs et consommateurs de ressources.
Les sites consommateurs de ressources doivent souscrire à ce service provenant d\'un fournisseur. Les sites fournisseurs
de ressources doivent publier ce service vers les sites consommateurs. La librairie des sites fournisseurs devient accessible
aux consommateurs';

$string['profilefieldname_help'] = '
Le nom du champ doit être donné comme un couple préfixe:nom. Le préfix peut être "user" ou "profile_field", et adresse
respectivement un champ standard du profil utilisateur, ou un champ personnaisé de profil.
';

$string['matchedvalues_help'] = '
You can enter an exact text value here, or a regexp value starting with ~ (ex : ~^someprefix).
';

$string['description_help'] = "
La description est un résumé très court de ce qu'est la ressource\n\n
Pour certaines options d'affichage d'une ressource, le résumé est affiché à côté de la ressource,
sinon il apparaît dans la page d'index de ressources, facilitant ainsi la recherche de
ressources particulières pour les étudiants.
";

$string['addclassification_help'] = "
Une classification fait référence à une table en base de données, qui doit être constituée de quatre champs :\n\n
- id
- parent, comme pointeur vers un taxon parent
- label, pour  mention du taxon
- ordering, comme moyen d'ordonner les taxons dans le même niveau de branche.\n\n
Il est possible de sélectionner quelle origine de la suite ordonnée est utilisée (0 ou 1)\n\n
A noter que le nom de la table peut être indiqué avec ou sans le préfixe des tables de moodle, et que le champ \"ordering\" est
facultatif (dans ce cas, l'ordre est basé sur l'id).\n\n Une classification configurer doit ensuite être activée pour être
utilisable dans le formulaire de description et le moteur de recherche.
";

$string['addsharedresource_help'] = "
Une ressource \"standard\" est un contenu : une information que le professeur veut publier dans le cours. Il peut s'agir de
fichiers téléchargés dans le serveur, de pages rédigées \"directement dans Moodle\", ou des pages web externes.\n\n Les ressources
mutualisées sont indépendantes du cours, et sont créées avant d'être attachées à un cours.\n\n
Les ressources mutualisées sont soit des URLs, soit des fichers mis en librairie locale, soit des fichiers mutualisés dans d'autres
Moodle.\n\n Lorsque vous ajoutez une ressource à partager, il est essentiel de bien la renseigner pour que les autres utilisateurs
potentiels puissent la trouver.
";

$string['classificationsearch_help'] = "
La recherche par la classification présente une sucession de liste de choix. La première permet de sélectionner l'une des
classifications actives.\n\n Une fois la classification active sélectionnée des listes supplémentaires vous permettent de
rafiner votre choix dans des catégories ou sous-catégories.\n\n Pour sélectionner les ressources d'une catégorie \"mère\",
laissez la liste du niveau suivant indéterminée.
";

$string['datesearch_help'] = '
Un champ de recherche de type date comporte deux champs distincts : une date de début une date de fin.\n\n
Les dates de début et de fin peuvent ne pas être définies.
';

$string['durationdesc_help'] = "
La durée peut être exprimée par une expression normalisée et formatée de la durée, et éventuellement par une description textuelle
non formatée, si elle ne peut être exprimée autrement.\n\n Le format d'une durée est \"P2Y1M2DT1H20M25.55S\". La mention \"P\"
préfixe la partie calendaire de la durée ; Ex. \"2Y\" = 2 ans (years); \"1M\" = 1 mois; \"2D\" = 2 jours (days); La mention \"T\"
préfixe la partie horaire de la durée ; Ex. \"1H\" = 1 heure; \"20M\" = 20 minutes; \"25.55S\" = 25,55 secondes.
";

$string['durationsearch_help'] = '
Un champ de recherche sur une durée combine un opérateur de comparaison (égal, différent, inférieur à...) et d\'une description
de la durée par composantes.
';

$string['classificationupdate_help'] = "
Une classification utilise les valeurs d'une table en base de données en utilisant quatre champs requis:\n\n
- id, comme identifiant primaire\n
- parent, comme liaison à un taxon parent\n
- label, fournissant la mention du taxon\n
- ordering, pour ordonner les taxons dans leur niveau de l'arbre\n\n
Vous pouvez par cette configuration, appuyer vos classifications sur n'importe quelle table satisfaisant aux exigences de sélection.
";

$string['numericsearch_help'] = '
Un champ de recherche de type numeric comporte deux champs distincts : un champ sous forme de liste déroulante contenant des
opérateurs de comparaison mathématiques et un champ texte. Choisissez un symbole de comparaison puis entrez un nombre dans le
champ texte pour effectuer une recherche sur ce type de champ.
';

$string['sqlrestriction_help'] = '
La restriction d\'une classification permet à l\'administrateur de sélectionner un sous-ensemble de valeurs comme source
des taxons en ajoutant une clause SQL arbitraire à la requete SELECT dans la table source. Une connaisance du SQL et de la
construction de la table source sont nécessaires.
';

$string['searchsharedresource_help'] = "
Toutes les ressources sont indépendantes du cours et créées en amont.\n\n
Cherchez une ressource et selectionnez \"Choisir\" pour l'ajouter au cours courant, ou utilisez \"Prévisualiser\" pour l'examiner.
";

$string['selectclassification_help'] = "
Tous les taxons d'une classification active apparaîtront dans une liste déroulante dans le formulaire de description de la
ressource.\n\n La suppression d'une classification entraîne nécessairement la suppression des métadonnées y faisant référence
pour toutes les ressources mutualisées.\n\nLe bouton \"Configurer\" permet de sélectionner les valeurs applicables pour chaque
instance de classification.
";

$string['selectsearch_help'] = "
La recherche par liste peut être à valeur simple ou à valeur multiple.\n\n
Dans le cas d'une lsite multiple, utilisez le Ctrl-Clic pour sélectionner plusieurs valeurs.
";

$string['selecttaxon_help'] = "
Un \"taxon\" est un élément d'une classification (taxonomie). Ce réglage permet de déterminer quelle sélection de valeurs sera
utilisée pour fourni les éléments de classification.\n\nNotez que, pour pouvoir utilsier un taxon \"fils\", la sélection doit
activer tous les taxons \"pères\" de ce dernier.
";

$string['textsearch_help'] = "
La recherche via un champ texte comporte trois options : \"Contient\" (par défaut), \"Débute par\" ou \"Finit par\".\n
Vous pouvez associer plusieurs valeurs en les séparant par des virgules. Les résultats de chaque cas seront ajoutés.
";

$string['vcard_help'] = "
vCard est un format standard ouvert d'échange de données personnelles (Visit Card soit Carte de visite).\n
Il est utile pour donner ses coordonnées personnelles ou professionnelle à une relation.\n
L'exemple suivant est un fichier avec une adresse unique : \n\n
BEGIN:VCARD\n
VERSION:3.0 \n
N:Gump;Forrest\n
FN:Forrest Gump\n
ORG:Bubba Gump Shrimp Co.\n
TITLE:Shrimp Man\n
PHOTO;VALUE=URL;TYPE=GIF:http://www.example.com/dir_photos/my_photo.gif\n
TEL;TYPE=WORK,VOICE:(111) 555-1212\n
ADR;TYPE=WORK:;;100 Waters Edge;Baytown;LA;30314;United States of America\n
LABEL;TYPE=WORK:100 Waters Edge\nBaytown, LA 30314\nUnited States of America\n
ADR;TYPE=HOME:;;42 Plantation St.;Baytown;LA;30314;United States of America\n
LABEL;TYPE=HOME:42 Plantation St.\nBaytown, LA 30314\nUnited States of America\n
EMAIL;TYPE=PREF,INTERNET:forrestgump@example.com\n
REV:20080424T195243Z\n
END:VCARD
";

$string['sharingcontext_help'] = "
Le contexte de partage de la ressource peut être le site entier ou seulement une catégorie (et ses sous-catégories). \n\n
Les ressources limitéées à des catégories ne peuvent être mutualisée via le réseau.
";

$string['userfieldvaluessingle'] = 'Single value access field';
$string['userfieldvaluessingle_help'] = 'Si vous choisissez une valeur, alors seuls les utilisateurs ayant cette valeur dans
leur profil pourront accéder à la ressource.';

$string['userfieldvaluesmultiple'] = 'Multiple values access field';
$string['userfieldvaluesmultiple_help'] = 'Choisissez une ou plusieurs valeurs ouvrant l\'accès à la ressource (OU). Si aucune
valeur n\'est sélectionnée, alors la ressource est librement accessible.';

$string['addinstance_search_desc'] = '
    Recherchez une ressource mutualisée dans la librarie et publiez-là dans le cours.
';

$string['addinstance_create_desc'] = '
    Apportez une nouvelle ressource mutualisée dans la librairie et publiez-la dans le cours.
';

require(__DIR__.'/pro_additional_strings.php');
