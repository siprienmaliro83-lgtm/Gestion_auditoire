<?php

namespace App\Http\Controllers\Decanat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Decanat\DecanatCrudRequest;
use App\Models\AnneeAcademique;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\ProgrammeAcademique;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DecanatCrudController extends Controller
{
    /** Ressources interdites au Décanat (gérées uniquement par le Super Administrateur). */
    private const FORBIDDEN_FOR_DECANAT = ['domaines', 'users', 'filieres', 'mentions'];

    private static function assertAllowedForDecanat(string $resource): void
    {
        abort_if(in_array($resource, self::FORBIDDEN_FOR_DECANAT, true), 403);
    }

    public function index(string $resource): View
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);
        $query = $config['model']::query();

        foreach ($config['with'] ?? [] as $relation) {
            $query->with($relation);
        }

        if (isset($config['role'])) {
            $roleId = Role::where('nom', $config['role'])->value('id');
            if ($roleId) {
                $query->where('role_id', $roleId);
            }
        }

        if ($user = auth()->user()) {
            self::applyDecanatScope($query, $resource, $user);
        }

        if ($search = request('q')) {
            $query->where(function ($builder) use ($config, $search): void {
                foreach ($config['search'] as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view('decanat.crud.index', [
            'resource' => $resource,
            'config' => $config,
            'items' => $query->latest('id')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(string $resource): View
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);

        return view('decanat.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => new $config['model'](),
            'options' => $this->options($config, $resource),
        ]);
    }

    public function store(DecanatCrudRequest $request, string $resource): RedirectResponse
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);
        $data = $this->payload($request->validated(), $config);

        if ($resource === 'enseignants') {
            $data['statut'] = $data['statut'] ?? 'Actif';
            $enseignant = $config['model']::create($data);
            $this->syncEnseignantUser($enseignant);
        } elseif ($resource === 'programmes-academiques') {
            $promotionIds = $request->input('promotions', []);
            unset($data['promotions']);
            $programme = $config['model']::create($data);
            if (! empty($promotionIds)) {
                $programme->promotions()->attach($promotionIds);
            }
        } elseif ($resource === 'etudiants') {
            $roleId = Role::where('nom', 'Étudiant')->value('id');
            $data['role_id'] = $roleId;
            $data['confirme'] = true;
            $data['password'] = Hash::make($data['matricule']);
            $config['model']::create($data);
        } else {
            $config['model']::create($data);
        }

        return redirect()
            ->route('decanat.crud.index', $resource)
            ->with('success', $config['singular'].' ajouté avec succès.');
    }

    public function edit(string $resource, int $id): View
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        return view('decanat.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($config, $resource),
        ]);
    }

    public function update(DecanatCrudRequest $request, string $resource, int $id): RedirectResponse
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);
        $data = $this->payload($request->validated(), $config, $item);

        if ($resource === 'enseignants') {
            $data['statut'] = $data['statut'] ?? 'Actif';
            $item->update($data);
            $this->syncEnseignantUser($item);
        } elseif ($resource === 'programmes-academiques') {
            $promotionIds = $request->input('promotions', []);
            unset($data['promotions']);
            $item->update($data);
            $item->promotions()->sync($promotionIds);
        } elseif ($resource === 'etudiants') {
            $data['confirme'] = true;
            $data['password'] = Hash::make($data['matricule']);
            $item->update($data);
        } else {
            $item->update($data);
        }

        return redirect()
            ->route('decanat.crud.index', $resource)
            ->with('success', $config['singular'].' modifié avec succès.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        self::assertAllowedForDecanat($resource);

        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        try {
            $item->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'delete' => 'Impossible de supprimer '.$config['singular']." : il est utilisé par d'autres enregistrements. Supprimez d'abord les dépendances.",
            ]);
        }

        return redirect()
            ->route('decanat.crud.index', $resource)
            ->with('success', $config['singular'].' supprimé avec succès.');
    }

    public static function rulesFor(string $resource, ?int $id = null): array
    {
        self::config($resource);

        return match ($resource) {
            'domaines' => self::basicRules('domaines', $id),
            'filieres' => self::basicRules('filieres', $id) + [
                'domaine_id' => ['required', 'exists:domaines,id', self::inScopeRule('Ce domaine ne vous est pas rattaché.', 'domaine')],
            ],
            'mentions' => self::basicRules('mentions', $id) + [
                'filiere_id' => ['required', 'exists:filieres,id', self::inScopeRule('Cette filière n\'appartient pas à votre périmètre.', 'filiere')],
            ],
            'promotions' => [
                'mention_id' => ['required', 'exists:mentions,id', self::inScopeRule('Cette mention n\'appartient pas à votre périmètre.', 'mention')],
                'code' => ['required', 'string', 'max:255', Rule::unique('promotions', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'niveau' => ['required', 'integer', 'min:1', 'max:5'],
                'effectif' => ['required', 'integer', 'min:0'],
            ],
            'annees-academiques' => [
                'libelle' => ['required', 'string', 'max:255', Rule::unique('annees_academiques', 'libelle')->ignore($id)],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after:date_debut'],
                'active' => ['nullable', 'boolean', function (string $attribute, $value, $fail) use ($id): void {
                    if (! (bool) $value) {
                        return;
                    }

                    $query = AnneeAcademique::where('active', true);
                    if ($id) {
                        $query->whereKeyNot($id);
                    }

                    if ($query->exists()) {
                        $fail('Une autre année académique est déjà active. Désactivez-la d\'abord.');
                    }
                }],
            ],
            'programmes-academiques' => [
                'annee_academique_id' => ['required', 'exists:annees_academiques,id'],
                'code' => ['required', 'string', 'max:255', Rule::unique('programmes_academiques', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'promotions' => ['required', 'array', 'min:1'],
                'promotions.*' => ['exists:promotions,id', self::inScopeRule('Une promotion sélectionnée n\'appartient pas à votre périmètre.', 'promotion')],
            ],
            'ues' => [
                'programme_academique_id' => ['required', 'exists:programmes_academiques,id', self::inScopeRule('Ce programme n\'appartient pas à votre périmètre.', 'programme')],
                'code' => ['required', 'string', 'max:255', Rule::unique('ues', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'credits' => ['required', 'integer', 'min:0'],
                'volume_horaire' => ['required', 'integer', 'min:0'],
            ],
            'ecs' => [
                'ue_id' => ['required', 'exists:ues,id', self::inScopeRule('Cette UE n\'appartient pas à votre périmètre.', 'ue')],
                'code' => ['required', 'string', 'max:255', Rule::unique('ecs', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'volume_horaire' => ['required', 'integer', 'min:1'],
                'statut' => ['required', Rule::in(['Non commencé', 'En cours', 'Entièrement dispensé'])],
            ],
            'enseignants' => [
                'matricule' => ['required', 'string', 'max:255', Rule::unique('enseignants', 'matricule')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('enseignants', 'email')->ignore($id)],
                'telephone' => ['nullable', 'string', 'max:255'],
                'grade' => ['nullable', 'string', 'max:255'],
                'specialite' => ['nullable', 'string', 'max:255'],
                'statut' => ['nullable', Rule::in(['Actif', 'Inactif'])],
            ],
            'etudiants' => [
                'matricule' => ['required', 'string', 'max:255', Rule::unique('users', 'matricule')->ignore($id)],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'promotion_id' => ['required', 'exists:promotions,id', self::inScopeRule('Cette promotion n\'appartient pas à votre périmètre.', 'promotion')],
            ],
            default => abort(404),
        };
    }

    public static function config(string $resource): array
    {
        $configs = [
            'domaines' => [
                'model' => Domaine::class,
                'title' => 'Domaines',
                'singular' => 'Domaine',
                'search' => ['code', 'nom'],
                'fields' => ['code' => 'text', 'nom' => 'text', 'description' => 'textarea'],
                'columns' => ['code', 'nom', 'description'],
            ],
            'filieres' => [
                'model' => Filiere::class,
                'title' => 'Filières',
                'singular' => 'Filière',
                'search' => ['code', 'nom'],
                'with' => ['domaine'],
                'fields' => ['domaine_id' => 'select:domaines', 'code' => 'text', 'nom' => 'text', 'description' => 'textarea'],
                'columns' => ['code', 'nom', 'domaine.nom'],
            ],
            'mentions' => [
                'model' => Mention::class,
                'title' => 'Mentions',
                'singular' => 'Mention',
                'search' => ['code', 'nom'],
                'with' => ['filiere'],
                'fields' => ['filiere_id' => 'select:filieres', 'code' => 'text', 'nom' => 'text', 'description' => 'textarea'],
                'columns' => ['code', 'nom', 'filiere.nom'],
            ],
            'promotions' => [
                'model' => Promotion::class,
                'title' => 'Promotions',
                'singular' => 'Promotion',
                'search' => ['code', 'nom'],
                'with' => ['mention'],
                'fields' => ['mention_id' => 'select:mentions', 'code' => 'text', 'nom' => 'text', 'niveau' => 'number', 'effectif' => 'number'],
                'columns' => ['code', 'nom', 'niveau', 'effectif', 'mention.nom'],
            ],
            'annees-academiques' => [
                'model' => AnneeAcademique::class,
                'title' => 'Années académiques',
                'singular' => 'Année académique',
                'search' => ['libelle'],
                'fields' => ['libelle' => 'text', 'date_debut' => 'date', 'date_fin' => 'date', 'active' => 'checkbox'],
                'columns' => ['libelle', 'date_debut', 'date_fin', 'active'],
            ],
            'programmes-academiques' => [
                'model' => ProgrammeAcademique::class,
                'title' => 'Programmes académiques',
                'singular' => 'Programme académique',
                'search' => ['code', 'nom'],
                'with' => ['anneeAcademique', 'promotions'],
                'fields' => ['annee_academique_id' => 'select:annees-academiques', 'code' => 'text', 'nom' => 'text', 'description' => 'textarea', 'promotions' => 'select-multiple:promotions'],
                'columns' => ['code', 'nom', 'anneeAcademique.libelle'],
            ],
            'ues' => [
                'model' => Ue::class,
                'title' => 'UE',
                'singular' => 'UE',
                'search' => ['code', 'nom'],
                'with' => ['programmeAcademique'],
                'fields' => ['programme_academique_id' => 'select:programmes-academiques', 'code' => 'text', 'nom' => 'text', 'credits' => 'number', 'volume_horaire' => 'number'],
                'columns' => ['code', 'nom', 'credits', 'volume_horaire', 'programmeAcademique.nom'],
            ],
            'ecs' => [
                'model' => Ec::class,
                'title' => 'EC',
                'singular' => 'EC',
                'search' => ['code', 'nom'],
                'with' => ['ue'],
                'fields' => ['ue_id' => 'select:ues', 'code' => 'text', 'nom' => 'text', 'volume_horaire' => 'number', 'statut' => 'enum:Non commencé,En cours,Entièrement dispensé'],
                'columns' => ['code', 'nom', 'volume_horaire', 'statut', 'ue.nom'],
            ],
            'enseignants' => [
                'model' => Enseignant::class,
                'title' => 'Enseignants',
                'singular' => 'Enseignant',
                'search' => ['matricule', 'nom', 'email', 'specialite'],
                'with' => ['user'],
                'labels' => ['nom' => 'Nom complet'],
                'fields' => ['matricule' => 'text', 'nom' => 'text', 'email' => 'email', 'telephone' => 'text', 'grade' => 'text', 'specialite' => 'text', 'statut' => 'enum:Actif,Inactif'],
                'columns' => ['matricule', 'nom', 'email', 'grade', 'specialite', 'statut'],
            ],
            'etudiants' => [
                'model' => User::class,
                'title' => 'Étudiants',
                'singular' => 'Étudiant',
                'search' => ['matricule', 'name', 'email'],
                'with' => ['promotion.mention.filiere.domaine'],
                'fields' => ['matricule' => 'text', 'name' => 'text', 'email' => 'email', 'promotion_id' => 'select:promotions'],
                'labels' => ['name' => 'Nom complet'],
                'columns' => ['matricule', 'name', 'email', 'promotion.nom', 'promotion.mention.filiere.nom', 'promotion.mention.filiere.domaine.nom'],
                'role' => 'Étudiant',
            ],
            'users' => [
                'model' => \App\Models\User::class,
                'title' => 'Utilisateurs',
                'singular' => 'Utilisateur',
                'search' => ['name', 'email'],
                'fields' => [],
                'columns' => [],
            ],
        ];

        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }

    private static function basicRules(string $table, ?int $id): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique($table, 'code')->ignore($id)],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    private static function inScopeRule(string $message, string $resource): \Closure
    {
        return function (string $attribute, $value, $fail) use ($message, $resource): void {
            if (! self::scopeExists($resource, $value)) {
                $fail($message);
            }
        };
    }

    /** Retourne le nom normalisé d'une ressource pour le périmètre du Décanat. */
    private static function scopeResource(string $resource): string
    {
        return match ($resource) {
            'domaines' => 'domaine',
            'filieres' => 'filiere',
            'mentions' => 'mention',
            'promotions' => 'promotion',
            'programmes-academiques' => 'programme',
            'ues' => 'ue',
            'ecs' => 'ec',
            'etudiants' => 'etudiant',
            default => $resource,
        };
    }

    /**
     * Applique au query builder le périmètre du Décanat : son Domaine,
     * sa Filière et sa Mention. Chaque filtre n'est appliqué que s'il est
     * renseigné sur le compte (rétrocompatibilité avec les comptes anciens).
     */
    private static function applyDecanatScope($query, string $resource, User $user): void
    {
        $domaineId = $user->domaine_id;
        $filiereId = $user->filiere_id;
        $mentionId = $user->mention_id;

        if ($domaineId === null && $filiereId === null && $mentionId === null) {
            return;
        }

        match (self::scopeResource($resource)) {
            'domaine' => $query->when($domaineId, fn ($q) => $q->whereKey($domaineId)),
            'filiere' => $query
                ->when($domaineId, fn ($q) => $q->where('domaine_id', $domaineId))
                ->when($filiereId, fn ($q) => $q->whereKey($filiereId)),
            'mention' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->where('filiere_id', $filiereId))
                ->when($mentionId, fn ($q) => $q->whereKey($mentionId)),
            'promotion' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('mention.filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->whereHas('mention', fn ($qq) => $qq->where('filiere_id', $filiereId)))
                ->when($mentionId, fn ($q) => $q->where('mention_id', $mentionId)),
            'programme' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('promotions.mention.filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->whereHas('promotions.mention', fn ($qq) => $qq->where('filiere_id', $filiereId)))
                ->when($mentionId, fn ($q) => $q->whereHas('promotions', fn ($qq) => $qq->where('mention_id', $mentionId))),
            'ue' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('programmeAcademique.promotions.mention.filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->whereHas('programmeAcademique.promotions.mention', fn ($qq) => $qq->where('filiere_id', $filiereId)))
                ->when($mentionId, fn ($q) => $q->whereHas('programmeAcademique.promotions', fn ($qq) => $qq->where('mention_id', $mentionId))),
            'ec' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('ue.programmeAcademique.promotions.mention.filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->whereHas('ue.programmeAcademique.promotions.mention', fn ($qq) => $qq->where('filiere_id', $filiereId)))
                ->when($mentionId, fn ($q) => $q->whereHas('ue.programmeAcademique.promotions', fn ($qq) => $qq->where('mention_id', $mentionId))),
            'etudiant' => $query
                ->when($domaineId, fn ($q) => $q->whereHas('promotion.mention.filiere', fn ($qq) => $qq->where('domaine_id', $domaineId)))
                ->when($filiereId, fn ($q) => $q->whereHas('promotion.mention', fn ($qq) => $qq->where('filiere_id', $filiereId)))
                ->when($mentionId, fn ($q) => $q->whereHas('promotion', fn ($qq) => $qq->where('mention_id', $mentionId))),
            default => null,
        };
    }

    /** Vérifie qu'un enregistrement appartient bien au périmètre du Décanat connecté. */
    private static function scopeExists(string $resource, $value): bool
    {
        $user = auth()->user();

        if ($user === null || $value === null) {
            return true;
        }

        $model = match (self::scopeResource($resource)) {
            'domaine' => Domaine::class,
            'filiere' => Filiere::class,
            'mention' => Mention::class,
            'promotion' => Promotion::class,
            'programme' => ProgrammeAcademique::class,
            'ue' => Ue::class,
            'ec' => Ec::class,
            default => null,
        };

        if ($model === null) {
            return true;
        }

        $query = $model::query()->whereKey($value);
        self::applyDecanatScope($query, $resource, $user);

        return $query->exists();
    }

    private function options(array $config, string $resource): array
    {
        $user = auth()->user();
        $options = [];

        foreach ($config['fields'] as $definition) {
            if (! str_starts_with($definition, 'select:') && ! str_starts_with($definition, 'select-multiple:')) {
                continue;
            }

            $optionResource = str_replace(['select:', 'select-multiple:'], '', $definition);
            $optionConfig = self::config($optionResource);
            $query = $optionConfig['model']::query();

            // Un enseignant est universel : son domaine de rattachement
            // peut être n'importe quel domaine de l'université.
            $scopeOption = ! ($resource === 'enseignants' && $optionResource === 'domaines');

            if ($user !== null && $scopeOption) {
                self::applyDecanatScope($query, $optionResource, $user);
            }

            $options[$optionResource] = $query
                ->orderBy($this->optionOrderColumn($optionResource))
                ->get()
                ->map(fn (Model $model): array => [
                    'id' => $model->getKey(),
                    'label' => $this->optionLabel($optionResource, $model),
                ]);
        }

        return $options;
    }

    /**
     * Crée ou met à jour le compte utilisateur lié à l'enseignant.
     * Identifiant de connexion : nom complet ou e-mail.
     * Mot de passe initial : le matricule (chiffré).
     */
    private function syncEnseignantUser(Enseignant $enseignant): void
    {
        $roleId = Role::where('nom', 'Enseignant')->value('id');

        if ($roleId === null) {
            return;
        }

        $user = $enseignant->user_id ? User::find($enseignant->user_id) : null;
        $user ??= User::where('email', $enseignant->email)->first();

        if ($user === null) {
            $user = new User;
            $user->email = $enseignant->email;
        }

        $user->role_id = $roleId;
        $user->name = trim(($enseignant->nom ?? '').' '.($enseignant->prenom ?? ''));
        $user->password = Hash::make($enseignant->matricule);
        $user->confirme = true;
        $user->save();

        if ($enseignant->user_id !== $user->id) {
            $enseignant->user_id = $user->id;
            $enseignant->save();
        }
    }

    private function payload(array $data, array $config, ?Model $item = null): array
    {
        if (array_key_exists('active', $config['fields'])) {
            $data['active'] = (bool) ($data['active'] ?? false);
        }

        return Arr::only($data, array_keys($config['fields']));
    }

    private function optionOrderColumn(string $resource): string
    {
        return match ($resource) {
            'annees-academiques' => 'libelle',
            'users' => 'name',
            default => 'nom',
        };
    }

    private function optionLabel(string $resource, Model $model): string
    {
        return match ($resource) {
            'annees-academiques' => $model->libelle,
            'users' => $model->name.' - '.$model->email,
            default => trim(($model->code ?? '').' '.$model->nom),
        };
    }
}
