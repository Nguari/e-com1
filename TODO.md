# TODO - Corrections PHP (User model & autoload)

- [x] Identifier la cause du fatal error : `Class "App\Models\User" not found` dans `src/Repositories/UserRepository.php`
- [x] Créer le modèle attendu : `src/Models/User.php`
- [x] Ajouter les méthodes requises par le code existant (`getFullName()`, `setPassword()`)
- [x] Mettre à jour le legacy `src/models/user.php` pour contenir une version cohérente (au minimum éviter les erreurs de méthodes attendues)
- [ ] Valider l’absence de fatal error sur `public/admin/produits.php`
- [ ] (Optionnel) Aligner complètement les noms de fichiers/casse pour PSR-4 (supprimer `src/models/user.php` ou le fusionner)

