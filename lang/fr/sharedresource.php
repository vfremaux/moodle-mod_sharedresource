<?php
/**
 *
 * @author  Valery Fremaux  valery@valeisti.fr
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
global $SITE;

// Capabilities
$string['sharedresource:addinstance'] = 'Peut ajouter une instance';
$string['sharedresource:manageblocks'] = 'Gérer les blocs dans la librairie';

$string['DMdescription'] = 'Description du modèle de métadonnées';
$string['DMuse'] = 'Utilisation du modèle de métadonnées';
$string['DMused'] = 'Modèle utilisé';
$string['SQLrestriction'] = 'Entrez une clause SQL WHERE pour restreindre une classification : ';
$string['add'] = 'Ajouter';
$string['addclassification'] = 'Ajouter la classification';
$string['addclassificationtitle'] = 'Ajout de classification';
$string['addfile'] = 'Ajouter aux fichiers du cours';
$string['addfiletocourse'] = 'Ajouter aux fichiers du cours';
$string['addheader'] = 'Ajouter une nouvelle ressource';
$string['addlocal'] = 'Relocaliser une ressource distante';
$string['addltiinstall'] = 'Installer un outil externe à partir de la librairie';
$string['addmetadataform'] = 'Ajouter un formulaire de métadonnées';
$string['addremote'] = 'Ajouter une ressource distante';
$string['addshared'] = 'Ajouter une ressource partagée';
$string['adddeploy'] = 'Déploiement d\'une archive d\'activité';
$string['addsharedresource'] = 'Ajouter une ressource mutualisée';
$string['addsharedresourcetypefile'] = 'Ajouter une ressource mutualisée';
$string['addtocourse'] = 'Ajouter la ressource au cours';
$string['all'] = 'Toutes les sources';
$string['appliedSQLrestrict'] = 'Clause appliquée : ';
$string['articlequantity'] = 'Nombre d\'articles';
$string['attributes'] = 'Liste des attributs renseignés dans le formulaire et enregistrés : ';
$string['backadminpage'] = 'Retour à la page d\'administration';
$string['backclassifpage'] = 'Retour à la page de configuration des classifications';
$string['backup_index'] = 'Sauvegarder le référentiel des ressources';
$string['badcourseid'] = 'Identifiant de cours invalide';
$string['basispluginchoice'] = 'Ce choix détermine le plugin utilisé pour le formulaire de métadonnées';
$string['cancelform'] = 'Annuler';
$string['cannotrestore'] = 'l\'entrée du catalogue de ressources est manquante - problème de restauration : {$a}';
$string['choose'] = 'Choisir';
$string['chooseparameter'] = 'Choisir le paramètre';
$string['chooseprovidertopushto'] = 'En mutualisant la ressource vers un fournisseur externe, vous:<ul><li>Déplacez la ressource</li><li>Supprimez la ressource stockée dans cette plate-forme</li><li>Permettez à d\'autres plates-formes connectées à ce fournisseur d\'utiliser cette ressource</li><li>Déplacerez la position de cette ressource dans toutes ses utilisations à l\'intérieur du réseau.</li></ul></p>';
$string['classificationconfiguration'] = 'Configuration des classifications';
$string['classificationconfigurationdesc'] = 'Cette <a href="{$a}">page supplémentaire</a> permet la configuration des classifications pour la norme choisie.';
$string['classificationname'] = 'Intitulé de la classification';
$string['classificationsearch'] = 'Recherche sur les classifications';
$string['classificationupdate'] = 'Modification de la classification';
$string['completeform'] = 'Entrez les données dans le formulaire ci-dessous';
$string['config_backup_index'] = 'Lors de la sauvegarde d\'un cours, sauvegarder TOUTES les entrées de catalogue correspondantes (y compris les fichiers locaux) ?';
$string['config_freeze_index'] = 'Lors de la sauvegarde d\'un cours, ne sauvegarder aucun fichier physique du référentiel commun ?';
$string['config_restore_index'] = 'Lors d\'une restauration, restaurer TOUTES les entrées de catalogue (y compris les fichiers locaux) ?  Ceci ne remplacera pas les entrées et métadonnées existantes.';
$string['configallowlocalfiles'] = 'Lors de la création d\'une nouvelle ressource de type fichier, permettre des liens vers les fichiers disponibles sur un système de fichiers local, par exemple sur un CD ou sur un disque dur. Cela peut être utile dans une classe où tous les étudiants ont accès a un volume réseau commun ou si des fichiers sur un CD sont nécessaires. Il est possible que l\'utilisation de cette fonctionnalité requière une modification des réglages de sécurité de votre navigateur.';
$string['configarticlequantity'] = 'Configure le nombre de nouvelles ressources publiées dans le flux';
$string['configautofilerenamesettings'] = 'Mettre à jour automatiquement les références vers d\'autres fichiers et dossiers lors d\'un changement de nom dans la gestion des fichiers.';
$string['configblockdeletingfilesettings'] = 'Empêcher la suppression de fichiers et dossiers référencés par des ressources. Veuillez remarquer que les images et autres fichiers référencés dans le code HTML ne sont pas protégés par ce réglage.';
$string['configclassification'] = 'Configurer';
$string['configdefaulturl'] = 'Cette valeur est utilisée pour préremplir l\'URL lors de la création d\'une nouvelle ressource pointée par URL.';
$string['configenablerssfeeds'] = 'Activer les flux RSS d\'exposition des ressources';
$string['configfilterexternalpages'] = 'L\'activation de ce réglage permettra le filtrage des ressources externes (pages web, fichiers HTML déposés) par les filtres définis dans le site (comme les liens des glossaires). Lorsque ce réglage est actif, l\'affichage de vos pages sera ralenti de façon sensible. À utiliser avec précaution.';
$string['configforeignurlsheme'] = 'Forme générale de l\'Url. Utiliser \'&lt;%%%%ID%%%%&gt;\' comme emplacement de l\'Identifiant Unique de Ressource';
$string['configframesize'] = 'Quand une page web ou un fichier est affiché dans un cadre (frame), cette valeur indique (en pixels) la taille du cadre contenant la navigation (en haut de la fenêtre).';
$string['configparametersettings'] = 'Détermine si par défaut la zone de configuration des paramètres est affichée ou non, lors de l\'ajout de nouvelles ressources. Après la première utilisation, ce réglage devient individuel.';
$string['configpopup'] = 'Lors de l\'ajout d\'une ressource pouvant être affichée dans une fenêtre pop-up, cette option doit-elle être activée par défaut ?';
$string['configpopupdirectories'] = 'Les fenêtres pop-up affichent le lien du dossier par défaut';
$string['configpopupheight'] = 'Hauteur par défaut des fenêtres pop-up';
$string['configpopuplocation'] = 'La barre de l\'URL est affichée par défaut dans les fenêtres pop-up';
$string['configpopupmenubar'] = 'La barre des menus est affichée par défaut dans les fenêtres pop-up';
$string['configpopupresizable'] = 'Les fenêtres pop-up sont redimensionnables par défaut';
$string['configpopupscrollbars'] = 'Les barres de défilement sont affichées par défaut dans les fenêtres pop-up';
$string['configpopupstatus'] = 'La barre d\'état est affichée par défaut dans les fenêtres pop-up';
$string['configpopuptoolbar'] = 'La barre des outils est affichée par défaut dans les fenêtres pop-up';
$string['configpopupwidth'] = 'Largeur par défaut des fenêtres pop-up';
$string['configsecretphrase'] = 'Cette phrase secrète est utilisée pour générer le code crypté pouvant être envoyé comme paramètre à certaines ressources. Ce code crypté est fabriqué en concaténant une valeur md5 de l\'adresse IP du current_user et de cette phrase secrète, par exemple : code = md5(IP.secretphrase). Ceci permet à la ressource recevant le paramètre de vérifier la connexion pour plus de sécurité.';
$string['configwebsearch'] = 'URL affichée lors de l\'ajout d\'une page web ou d\'un lien, pour permettre à l\'utilisateur de rechercher l\'URL désirée.';
$string['configwindowsettings'] = 'Détermine si, par défaut, la zone de configuration des fenêtres est affichée ou non, lors de l\'ajout de nouvelles ressources. Après la première utilisation, ce réglage devient individuel.';
$string['contains'] = 'contient';
$string['conversioncancelled'] = 'conversion annulée';
$string['conversioncancelledtocourse'] = 'Conversion annulée. Vous allez être redirigés vers la gestion des activités';
$string['conversioncancelledtolibrary'] = 'Conversion annulée. Vous allez être redirigés vers la librairie';
$string['convert'] = 'Convertir la sélection';
$string['convertall'] = 'Mettre en commun et indexer les ressources';
$string['convertback'] = 'Rappatrier une ressouce commune';
$string['convertingsharedresource'] = 'Conversuin de la ressource mutualisée {$a->id} : {$a->name}';
$string['correctsave'] = '<h2> Ressource enregistrée correctement </h2>';
$string['d'] = 'j(s)';
$string['datachanged'] = 'Modifications effectuées';
$string['datesearch'] = 'Recherche en champ de type date';
$string['day'] = '- Jour -';
$string['days'] = 'Jour(s)';
$string['defaultselect'] = 'Reinitialiser à la sélection par défaut';
$string['deleteconfirm'] = 'Etes-vous certain de vouloir supprimer cette classification ?';
$string['description'] = 'Description';
$string['directlink'] = 'Lien direct vers ce fichier';
$string['display'] = 'Fenêtre';
$string['disablednode'] = 'Le noeud {$a} est désactivé dans ce schéma d\'application';
$string['discouragednode'] = 'Champ déconseillé (compatibilité)';
$string['durationdescr'] = 'Description d\'une durée';
$string['durationsearch'] = 'Recherche d\'une durée';
$string['edit'] = 'Modifier';
$string['educational'] = 'Aspects éducatifs';
$string['endswith'] = 'finit par';
$string['entry'] = 'Entrée';
$string['equalto'] = 'est égal';
$string['erroraddinstance'] = 'Erreur de création de l\'instance de resssource';
$string['errorcmaddition'] = 'Le module de cours n\'a pas pu être ajouté';
$string['errorcmsectionbinding'] = 'La section n\'a pu être enregistrée dans le module de cours';
$string['errordeletesharedresource'] = 'Erreur d\'effacement de fichier d\'une ressource mutualisée ({$a})';
$string['erroremptyurl'] = 'Tentative de créer une resosurce mutualisée sans URL d\'accès';
$string['errorinvalididentifier'] = 'L\'identifiant {$a} ne correspond à aucune ressource connue';
$string['errormetadata'] = 'Erreurs trouvées pour les champs suivants (ces erreurs seront affichées en rouge dans le formulaire) : ';
$string['errornometadataenabled'] = 'Aucun plugin de métadonnées n\'est activé. Les métadonnées ne peuvent être configurée.';
$string['errornometadataplugins'] = 'Aucun plugin de métadonnées installé';
$string['errornoticecreation'] = 'Impossible de créer la notice';
$string['errornotinstalled'] = 'Le module "ressource mutualisée" n\'est pas installé !!';
$string['errorsectionaddition'] = 'Impossible de créer une nouvelle section';
$string['errorupdatecm'] = 'Erreur de mise à jour de la resosurce mutualisée (instance)';
$string['existothermetadata'] = 'Une fiche de métadonnées pour cette ressource existe déjà dans une autre norme. <br/>La validation de ce formulaire pour une nouvelle fiche entraînera la suppression des anciennes métadonnées.';
$string['export'] = 'Exporter vers un référentiel externe';
$string['existsignorechanges'] = 'la donnée existe mais n\'est pas modifiée';
$string['failadd'] = 'Echec de la sauvegarde (ajout) de la ressource à la base de données';
$string['failupdate'] = 'Echec de la sauvegarde (mise à jour) de la ressource à la base de données';
$string['fieldname'] = 'Nom du champ';
$string['file'] = 'Fichier ou lien';
$string['fileadvice'] = '<p>La représentation physique de la ressource a été ajoutée dans les fichiers locaux du cours. Vous allez être redirigé vers cet espace des fichiers. Aucun module n\'a cependant été ajouté au cours.</p>';
$string['filenotfound'] = 'Désolé, le fichier demandé ne peut être trouvé. Raison : {$a}';
$string['fileuploadfailed'] = 'Echec du téléchargement';
$string['fillcategory'] = 'Tous les champs de la catégorie sont vides. Remplissez-en au moins un.';
$string['fillprevious'] = 'Le champ précédent est vide. Veuillez le remplir avant de rajouter un autre champ';
$string['filtername'] = 'Nom du filtre';
$string['filesharedresource'] = 'Ressource mutualisée (fichier ou url)';
$string['forcedownload'] = 'Forcer le téléchargement';
$string['frameifpossible'] = 'Cadre, si posible';
$string['frameifpossible_help'] = 'Si activé, la ressource est présentée dans un cadre autonome';
$string['framesize'] = 'Taille du cadre';
$string['freeze_index'] = 'Geler le référentiel de ressources';
$string['gometadataform'] = 'Enregistrer et documenter la ressource';
$string['gometadataform2'] = 'Documenter la ressource';
$string['h'] = 'h(s)';
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
$string['keywordpunct'] = 'Pas de ponctuation dans un mot-clé <br/>';
$string['keywords'] = 'Mots-clefs';
$string['keyword'] = 'Mot-clef';
$string['labelname'] = 'Nom du champ label';
$string['language'] = 'Langue';
$string['license'] = 'License';
$string['layout'] = 'Mise en forme';
$string['local'] = 'Ressources '.$SITE->shortname;
$string['localizeadvice'] = '<p>La ressource a été relocalisée, cela veut dire qu\'une copie de la ressource originale est désormais disponible dans le cours, dissociée de la ressource mutualisée d\'origine. Si cette ressource a une représentation physique, le fichier qui la représente est stocké dans les fichiers locaux du cours.</p>';
$string['localizetocourse'] = 'Localiser comme ressource du cours';
$string['location'] = 'Emplacement de la ressource';
$string['m'] = 'm(s)';
$string['medatadaconfigurationdesc'] = 'Cette <a href="{$a}">page supplémentaire</a> permet la configuration des formulaires de metadonnées pour chaque rôle, et de choisir les widgets de recherche.';
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
$string['updatebutton'] = 'Effectuer la modification';
$string['modulename'] = 'Ressource mutualisée';
$string['modulename_help'] = 'Une ressource partagée est naturellement partagée dans tout le site ou dans une catégorie de cours. Les ressources partagées sont renseignées par des métadonnées complètes qui permettent une recherche et une exploration des ressources. Les librairies peuvent être exposées au réseau de Moodle pour améliorer la mutualisation des ressources pédagogiques.';
$string['modulenameplural'] = 'Ressources mutualisées';
$string['month'] = '- Mois -';
$string['mtdfieldname'] = 'Nom du champ';
$string['mtdfieldid'] = 'Id du champ';
$string['mtdvalue'] = 'Valeur';
$string['name'] = 'Nom';
$string['newdirectories'] = 'Montrer les liens directs';
$string['newheight'] = 'Hauteur par défaut (en pixels)';
$string['newlocation'] = 'Montrer la barre d\'adresse';
$string['newmenubar'] = 'Montrer la barre de menu';
$string['newresizable'] = 'Autoriser le redimensionnement';
$string['newscrollbars'] = 'Autoriser le défilement';
$string['newstatus'] = 'Montrer la barre d\'état';
$string['newtoolbar'] = 'Montrer la barre d\'outils';
$string['newwidth'] = 'Largeur par défaut (en pixels)';
$string['newwindow'] = 'Nouvelle fenêtre';
$string['badSQLrestrict'] = 'La clause devrait être le contenu d\'une clause WHERE';
$string['noaccessform'] = 'Votre catégorie d\'utilisateur n\'a pas accès à ce formulaire';
$string['noclassification'] = 'Aucune classification repertoriée';
$string['node'] = 'Branche';
$string['nodescription'] = 'Il n\'y a pas de description disponible pour cette norme.';
$string['nometadataplugin'] = 'L\'administrateur n\'a pas configuré le schéma de métadonnées applicable aux ressources.';
$string['noplugin'] = 'Pas de métadonnées';
$string['noprovidertopushto'] = 'Votre plate-forme n\'est raccordée à aucun fournisseur de mutualisation.';
$string['nosharedresources'] = 'Aucune ressource mutualisée publiée dans ce cours';
$string['noresourcesfound'] = 'Aucune ressource dans le catalogue';
$string['noresourcestoconvert'] = 'Aucune ressource à convertir';
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
$string['pluginadministration'] = 'Administration du plugins';
$string['pluginchoice'] = 'Choix du plugin';
$string['pluginname'] = 'Ressource mutualisée';
$string['pluginscontrol'] = 'Contrôle des plugins de métadonnées';
$string['pluginscontrolinfo'] = 'Ce paramètre permet de choisir le plugin utilisé pour les métadonnées lors de l\'indexation de la ressource.';
$string['preview'] = 'Prévisualiser';
$string['pushtosingleprovider'] = '<p>Votre plate-forme ne connait qu\'un fournisseur externe mutualisé : {$a}.</p><p>En mutualisant la ressource vers ce fournisseur externe, vous:<ul><li>Déplacez la ressource</li><li>Supprimez la ressource stockée dans cette plate-forme</li><li>Permettez à d\'autres plates-formes connectées à ce fournisseur d\'utiliser cette ressource</li><li>Déplacerez la position de cette ressource dans toutes ses utilisations à l\'intérieur du réseau.</li></ul></p>';
$string['readnotice'] = 'Lire la notice';
$string['remotesearchquery'] = 'Recherche dans les référentiels de ressources';
$string['remotesearchresults'] = 'Résultats de recherche ';
$string['remotesubmission'] = 'Soumission de ressource';
$string['repository'] = 'Entrepôt';
$string['repositorytoresource'] = 'Lirairie vers cours';
$string['resource_consumer_description'] = 'En publiant ce service, vous permettez aux plates-formes "fournisseurs" de vérifier la consommation de leurs ressources sur cet hôte.<br/><br/>En vous abonnant à ce service, vous pouvez vérifier la consommation de vos ressources sur les sites "consommateur" distant.<br/><br/>';
$string['resource_consumer_name'] = 'Service de consommation de ressources';
$string['resource_provider_description'] = 'En publiant ce service, vous permettez aux "consommateurs" distants de venir utiliser les ressources partagées de votre catalogue.<br/><br/>En vous abonnant à ce service, vous fournissez votre catalogue local aux plates-formes "consommateur" distants.<br/><br/>';
$string['resource_provider_name'] = 'Service de fourniture de ressources';
$string['resourceaccessurlasforeign'] = 'URL d\'accès aux ressources';
$string['resourcebuilt'] = 'Nouvelle ressource : {$a}';
$string['urlbuilt'] = 'Nouvelle ressource : {$a}';
$string['resourceconversion'] = 'Conversion de ressources';
$string['resourcedefaulturl'] = 'URL par défaut';
$string['resourceexists'] = 'Il existe déjà une ressource de même signature';
$string['resources'] = 'Ressources';
$string['resourcetorepository'] = 'Cours vers Librairie';
$string['resourcetypefile'] = 'Identification de la ressource';
$string['restore_index'] = 'Restaurer la librairie';
$string['restrictclassification'] = 'Restreindre une classification';
$string['s'] = 's(s)';
$string['saveSQLrestrict'] = 'Enregistrer';
$string['saveselection'] = 'Enregistrer la sélection';
$string['searchfor'] = 'Chercher';
$string['searchheader'] = 'Critères de recherche';
$string['searchin'] = 'Rechercher dans';
$string['searchinlibrary'] = 'Rechercher dans la librairie';
$string['searchsharedresource'] = 'Chercher une ressource mutualisée';
$string['searchorcreate'] = 'Chercher une ressource mutualisée ou enregistrer une nouvelle ressource';
$string['seconds'] = 'Seconde(s)';
$string['selectable'] = 'Sélectionnables';
$string['selectall'] = 'tout';
$string['selectclassification'] = 'Sélection et configuration des classifications apparentes';
$string['selecttaxon'] = 'Sélection des taxons';
$string['selectnone'] = 'aucun';
$string['selectsearch'] = 'Recherche en champ de type select';
$string['semantic_density'] = 'Densité sémantique';
$string['serverurl'] = 'URL Serveur';
$string['sharedresourcedetails'] = 'Détails sur les ressources mutualisées';
$string['sharedresourceintro'] = 'Introduction';
$string['sharedresourcenotice'] = 'Notice de la ressource : {$a}';
$string['sharedresourceservice_name'] = 'Services réseau de mutualisation';
$string['sharedresourceservice_description'] = 'Permet les échanges de servie entre fournisseurs et consommateurs. Les sites consommateurs doivent souscrire à ce service. Les sites fournisseurs doivent publier ce service.';
$string['sharedresourcetypefile'] = 'Ressource mutualisée';
$string['sharingcontext'] = 'Niveau de partage';
$string['startswith'] = 'commence par';
$string['step2'] = 'Passer à l\'étape 2';
$string['successfulmodification'] = 'Modification effectuée';
$string['system'] = 'Administrateur';
$string['systemcontext'] = 'Partage global site';
$string['tablename'] = 'Nom de la table';
$string['taxonpath'] = 'TAXON';
$string['technical'] = 'Technique';
$string['taxonchoicetitle'] = 'Sélection des taxons apparents';
$string['textsearch'] = 'Recherche en champ de type texte';
$string['title'] = 'Titre';
$string['thumbnail'] = 'Vignette (35k max)';
$string['clearthumbnail'] = 'Supprimer la vignette';
$string['typical_age_range'] = 'Tranche d\'âge typique';
$string['typical_learning_time'] = 'Temps d\'apprentissage nominal';
$string['unselectall'] = 'aucun';
$string['updatemetadata'] = 'Mettre à jour la configuration';
$string['updatemetadataform'] = 'Mettre à jour la description';
$string['updateresourcepageoff'] = 'Quitter mode édition';
$string['updateresourcepageon'] = 'Passer en mode édition';
$string['updatesharedresource'] = 'Mettre à jour la ressource mutualisée';
$string['updatesharedresourcetypefile'] = 'Mettre à jour la ressource mutualisée';
$string['url'] = 'URL de la ressource mutualisée';
$string['used'] = 'Utilisée {$a} fois';
$string['validateform'] = 'Valider';
$string['variablename'] = 'Nom de la variable';
$string['vcard'] = 'Description de la structure Vcard';
$string['view_resource_info'] = 'Voir les infos sur la ressource';
$string['vol'] = 'Vol';
$string['widget'] = 'Widgets de recherche';
$string['wrongform'] = '<h2> Le formulaire n\'a pas été renseigné correctement. Retour au formulaire dans 15sec</h2>';
$string['year'] = '- Année -';
$string['view_pageitem_page_embedded_content'] = 'Voir la ressource dans la page';

//************* Help Strings ******************/
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
A noter que le nom de la table peut être indiqué avec ou sans le préfixe des tables de moodle, et que le champ \"ordering\" est facultatif (dans ce cas, l'ordre est basé sur l'id).\n\n
Une classification configurer doit ensuite être activée pour être utilisable dans le formulaire de description et le moteur de recherche.
";

$string['addsharedresource_help'] = "
Une ressource \"standard\" est un contenu : une information que le professeur veut publier dans le cours. Il peut s'agir de fichiers téléchargés dans le serveur, de pages rédigées \"directement dans Moodle\", ou des pages web externes.\n\n
Les ressources mutualisées sont indépendantes du cours, et sont créées avant d'être attachées à un cours.\n\n
Les ressources mutualisées sont soit des URLs, soit des fichers mis en librairie locale, soit des fichiers mutualisés dans d'autres Moodle.\n\n
Lorsque vous ajoutez une ressource à partager, il est essentiel de bien la renseigner pour que les autres utilisateurs potentiels puissent la trouver.
";

$string['classificationsearch_help'] = "
La recherche par la classification présente une sucession de liste de choix. La première permet de sélectionner l'une des classifications actives.\n\n
Une fois la classification active sélectionnée des listes supplémentaires vous permettent de rafiner votre choix dans des catégories ou sous-catégories.\n\n
Pour sélectionner les ressources d'une catégorie \"mère\", laissez la liste du niveau suivant indéterminée.
";

$string['datesearch_help'] = '
Un champ de recherche de type date comporte deux champs distincts : une date de début une date de fin.\n\n
Les dates de début et de fin peuvent ne pas être définies.
';

$string['durationdesc_help'] = "
La durée peut être exprimée par une expression normalisée et formatée de la durée, et éventuellement par une description textuelle non formatée, si elle ne peut être exprimée autrement.\n\n
Le format d'une durée est \"P2Y1M2DT1H20M25.55S\". La mention \"P\" préfixe la partie calendaire de la durée ; Ex. \"2Y\" = 2 ans (years); \"1M\" = 1 mois; \"2D\" = 2 jours (days); La mention \"T\" préfixe la partie horaire de la durée ; Ex. \"1H\" = 1 heure; \"20M\" = 20 minutes; \"25.55S\" = 25,55 secondes.
";

$string['durationsearch_help'] = '
Un champ de recherche sur une durée combine un opérateur de comparaison (égal, différent, inférieur à...) et d\'une description de la durée par composantes.
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
Un champ de recherche de type numeric comporte deux champs distincts : un champ sous forme de liste déroulante contenant des opérateur de comparaison mathématiques et un champ texte. Choisissez un symbole de comparaison puis entrez un nombre dans le champ texte pour effectuer une recherche sur ce type de champ.
';

$string['restrictclassification_help'] = '
La restriction d\'une classification permet à l\'administrateur de sélectionner un sous-ensemble de valeurs comme source des taxons. Une connaisance du SQL et de la construction de la table source sont nécessaires.
';

$string['searchsharedresource_help'] = "
Toutes les ressources sont indépendantes du cours et créées en amont.\n\n
Cherchez une ressource et selectionnez \"Choisir\" pour l'ajouter au cours courant, ou utilisez \"Prévisualiser\" pour l'examiner.
";

$string['selectclassification_help'] = "
Tous les taxons d'une classification active apparaîtront dans une liste déroulante dans le formulaire de description de la ressource.\n\n
La suppression d'une classification entraîne nécessairement la suppression des métadonnées y faisant référence pour toutes les ressources mutualisées.\n\n
Le bouton \"Configurer\" permet de sélectionner les valeurs applicables pour chaque instance de classification.
";

$string['selectsearch_help'] = "
La recherche par liste peut être à valeur simple ou à valeur multiple.\n\n
Dans le cas d'une lsite multiple, utilisez le Ctrl-Clic pour sélectionner plusieurs valeurs.
";

$string['selecttaxon_help'] = "
Un \"taxon\" est un élément d'une classification (taxonomie). Ce réglage permet de déterminer quelle sélection de valeurs sera utilisée pour fourni les éléments de classification.\n\n
Notez que, pour pouvoir utilsier un taxon \"fils\", la sélection doit activer tous les taxons \"pères\" de ce dernier.
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

/*
global $CFG;
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
sharedresource_load_plugin_lang($string, 'fr');
sharedresource_load_pluginsmin_lang($string, 'fr');
*/
