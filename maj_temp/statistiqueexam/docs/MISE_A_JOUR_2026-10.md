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

## Ce qui est fait
- Vacation 2026 (besoins, montants, province/DREN/CISCO/centre, phases, catégories, affectés vs besoin, transcription multiple, EPS source unique).
- Ordre de livraison du dispatching enregistré en base et respecté par l'aperçu et l'export.
- Nouvelle feuille de style `public/css/exam-ui.css` pour les pages Vacation.

## Non réalisé dans cette livraison
Tableau de bord RH et documents RH, totaux formation/absence/atelier/congé par agent, comptabilité des matières, refonte de `/home` par rôle, refonte graphique des autres modules.

## Règles à confirmer administrativement
- Les taux viennent d'un arrêté non fourni : à saisir dans « Barèmes ». Aucun montant n'est inventé.
- Interprétation du nombre de correcteurs et des enveloppes (Art. 12) : réglable dans « Paramètres ».
- Types de centres assurant la transcription.
- Le paiement n'est pas suivi dans l'application : seul le montant des affectations validées est calculé.
- Aucun contrôle de non-cumul des indemnités entre activités.

## Tests
`vendor/bin/phpunit` : 11 tests, 9 réussis. Deux échecs existaient déjà avant ces changements (`ExampleTest`, `HrPersonnelTest`).
