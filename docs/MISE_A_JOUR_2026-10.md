# Mise à jour d'octobre 2026 — installation et limites

## Installation (sur une copie du projet existant)
1. Sauvegardez la base de données et le dossier du projet.
2. Remplacez les fichiers du projet par ceux de l'archive (conservez votre `.env`).
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate` (2 nouvelles migrations, voir ci-dessous ; aucune donnée existante n'est supprimée).
5. `php artisan view:clear && php artisan route:clear`
6. Facultatif : `npm ci && npm run build`. Le CSS `public/css/exam-ui.css` fonctionne sans build.

## Migrations
- `2026_10_26_000001_add_provinces_transcription_centres_and_widen_eps` : provinces, centres de transcription (plusieurs par CISCO), `same_site_as_correction`, élargissement de `eps_capacity` (valeur > 255).
- `2026_10_26_000002_create_dispatching_point_orders_table` : ordre de livraison des points de largage.
- `2026_10_28_000001_harden_inventory_movements` : jeton anti-doublon et pièce justificative sur les mouvements de matières.
- `2026_10_28_000002_add_map_coordinates` : latitude/longitude sur les centres de correction, d'écrit et de transcription ; table des coordonnées des points de largage.

## Ce qui est fait
- **Vacation 2026** : besoins et montants par province, DREN, CISCO, centre, phase et catégorie ; affectés comparés au besoin ; plusieurs centres de transcription par CISCO ; source unique des candidats EPS ; tableau EPS masqué quand il n'y a ni centre ni agent EPS (écran DREN).
- **Hiérarchie** : province obligatoire à la création d'une DREN ; CISCO historique rattachée directement à une province, sans DREN ; case « même lieu que la correction » sur le centre d'écrit ; un sous-centre n'a pas de chef de centre propre (adjoint seulement).
- **RH** : page « Bilan des activités » (menu RH, administrateur) avec congé, absence, mission, formation et atelier par agent, filtre de période, sans double comptage des jours qui se chevauchent ; effectifs par unité et fonction ; validations en attente ; affectations de vacation.
- **Dispatching** : ordre de livraison des points enregistré en base, restitué à la réouverture, appliqué à tous les axes dans l'aperçu et l'export.
- **Comptabilité des matières** : types entrée, sortie et retour ; jeton anti-doublon ; numéro de pièce unique par matériel et par type ; sortie refusée si elle dépasse le stock ou la quantité demandée ; stock modifiable uniquement par mouvement ; page d'historique par matériel et page de rapprochement du stock.
- **/home** : cartes et indicateurs selon le rôle (administrateur, RH, statistique, examens, utilisateur) ; la logistique garde sa redirection. Les routes restent protégées côté serveur.
- **Carte** (menu « Carte », onglet « Carte » de Vacation) : régions de Madagascar, un point par centre coloré selon son rôle (écrit seul, correction seule, écrit + correction, sous-centre, transcription, EPS). Un clic affiche salles, candidats, rôle ; pour l'administrateur, les agents requis avant / pendant / après la session, le total des postes et les affectés/validés. Filtres examen, DREN, type. Vue « trajet du dispatching » : un tracé par axe, de votre point de départ jusqu'aux points de largage dans l'ordre enregistré, avec candidats et salles déposés à chaque point.
- **Coordonnées de la carte** (`/carte/coordonnees`, administrateur, statistique, examens) : saisie par centre, modèle CSV à remplir dans Excel puis import, paramètres (données en ligne oui/non, URL GeoJSON, point de départ). Toute valeur hors de Madagascar est refusée (latitude et longitude inversées détectées).
- Feuille de style `public/css/exam-ui.css` pour les pages Vacation, RH, matières et accueil.

## Carte : fonctionnement hors ligne et en ligne
- Avec connexion (et l'option activée) : fond OpenStreetMap et régions lues depuis l'URL GeoJSON des paramètres (geoBoundaries ADM1 par défaut, licence ODbL). Si la source ne répond pas en 6 secondes, la carte bascule seule.
- Sans connexion : `public/geo/adm1-geoboundaries/regions.geojson` (22 régions) ; fichier de secours `public/geo/madagascar-regions.geojson` (Natural Earth, domaine public). Le shapefile (`regions.shp/.shx/.dbf/.prj`, même dossier) sert aux logiciels SIG ; le navigateur lit le GeoJSON. Leaflet 1.7.1 (BSD-2) est dans `public/vendor/leaflet`, sans CDN.
- Un centre sans coordonnées n'est pas dessiné ; il est listé sous la carte. Un centre d'écrit sur le même site que sa correction prend la position de celle-ci. Un point de largage sans position saisie est placé au centre de gravité des centres qu'il dessert.
- Les « agents » de la carte sont les postes requis par le décret, pas des personnes distinctes ; la source en ligne n'a pas pu être testée depuis l'environnement de développement (réseau restreint), seul le repli local l'a été.

## Non réalisé
- Rattachement d'un utilisateur à une DREN, une CISCO ou un centre, donc pas d'accueil ni de droits par DREN/CISCO/centre (il faudrait filtrer toutes les données par zone, module par module).
- Colonne province dans l'import des références.
- Agents affectés avant, pendant et après la session dans la fiche d'un centre.
- Refonte des documents officiels RH (en-têtes, PDF).
- Refonte graphique des autres modules.

## Règles à confirmer administrativement
- Les taux viennent d'un arrêté non fourni : à saisir dans « Barèmes ». Aucun montant n'est inventé.
- Interprétation du nombre de correcteurs et des enveloppes (Art. 12) : réglable dans « Paramètres ».
- Types de centres assurant la transcription.
- Le paiement n'est pas suivi dans l'application : seul le montant des affectations validées est calculé.
- Aucun contrôle de non-cumul des indemnités entre activités.

## Tests
`vendor/bin/phpunit` : 30 tests, 28 réussis (dont 6 pour la carte : données, droits, saisie, import CSV, trajet, bornes). Deux échecs existaient déjà avant ces changements (`ExampleTest`, `HrPersonnelTest`).
