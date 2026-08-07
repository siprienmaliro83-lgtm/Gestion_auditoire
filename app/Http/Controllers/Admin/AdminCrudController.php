<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCrudRequest;
use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Domaine;
use App\Models\DemandeAuditoire;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\ProgrammeAcademique;
use App\Models\Programmation;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCrudController extends Controller
{
    public function index(string $resource): View
    {
        $config = self::config($resource);
        $query = $config['model']::query();

        foreach ($config['with'] ?? [] as $relation) {
            $query->with($relation);
        }

        if ($search = request('q')) {
            $query->where(function ($builder) use ($config, $search): void {
                foreach ($config['search'] as $column) {
                    $builder->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view('admin.crud.index', [
            'resource' => $resource,
            'config' => $config,
            'items' => $query->latest('id')->paginate(10)->withQueryString(),
        ]);
    }

    public function create(string $resource): View
    {
        $config = self::config($resource);

        return view('admin.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => new $config['model'](),
            'options' => $this->options($config),
        ]);
    }

    public function store(AdminCrudRequest $request, string $resource): RedirectResponse
    {
        $config = self::config($resource);
        $data = $this->payload($request->validated(), $config);

        $config['model']::create($data);

        return redirect()
            ->route('admin.crud.index', $resource)
            ->with('success', $config['singular'].' ajouté avec succès.');
    }

    public function edit(string $resource, int $id): View
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        return view('admin.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($config),
        ]);
    }

    public function update(AdminCrudRequest $request, string $resource, int $id): RedirectResponse
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);
        $data = $this->payload($request->validated(), $config, $item);

        $item->update($data);

        return redirect()
            ->route('admin.crud.index', $resource)
            ->with('success', $config['singular'].' modifié avec succès.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        if ($config['model'] === User::class && Auth::id() === $item->id) {
            return back()->withErrors(['delete' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        try {
            $item->delete();

            return redirect()
                ->route('admin.crud.index', $resource)
                ->with('success', $config['singular'].' supprimé avec succès.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'delete' => 'Impossible de supprimer '.$config['singular']." : il est utilisé par d'autres enregistrements. Supprimez d'abord les dépendances ou annulez les liens.",
            ]);
        }
    }

    public function confirmer(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (Auth::id() === $user->id) {
            return back()->withErrors(['confirm' => 'Vous ne pouvez pas modifier le statut de confirmation de votre propre compte.']);
        }

        $user->confirme = ! $user->confirme;
        $user->save();

        return back()->with('success', 'Statut de confirmation mis à jour.');
    }

    public static function rulesFor(string $resource, ?int $id = null): array
    {
        self::config($resource);

        return match ($resource) {
            'roles' => [
                'nom' => ['required', 'string', 'max:255', Rule::unique('roles', 'nom')->ignore($id)],
                'description' => ['nullable', 'string'],
            ],
            'users' => [
                'role_id' => ['required', 'exists:roles,id'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
                'domaine_id' => ['nullable', 'exists:domaines,id'],
                'filiere_id' => ['nullable', 'exists:filieres,id', function (string $attribute, $value, $fail): void {
                    if ($value && request()->input('domaine_id') && ! \App\Models\Filiere::whereKey($value)->where('domaine_id', request()->input('domaine_id'))->exists()) {
                        $fail('La filière choisie n\'appartient pas au domaine sélectionné.');
                    }
                }],
                'mention_id' => ['nullable', 'exists:mentions,id', function (string $attribute, $value, $fail): void {
                    if ($value && request()->input('filiere_id') && ! \App\Models\Mention::whereKey($value)->where('filiere_id', request()->input('filiere_id'))->exists()) {
                        $fail('La mention choisie n\'appartient pas à la filière sélectionnée.');
                    }
                }],
                'promotion_id' => ['nullable', 'exists:promotions,id'],
            ],
            'domaines' => self::basicRules('domaines', $id),
            'filieres' => self::basicRules('filieres', $id) + ['domaine_id' => ['required', 'exists:domaines,id']],
            'mentions' => self::basicRules('mentions', $id) + ['filiere_id' => ['required', 'exists:filieres,id']],
            'promotions' => [
                'mention_id' => ['required', 'exists:mentions,id'],
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
            ],
            'ues' => [
                'programme_academique_id' => ['required', 'exists:programmes_academiques,id'],
                'code' => ['required', 'string', 'max:255', Rule::unique('ues', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'credits' => ['required', 'integer', 'min:0'],
                'volume_horaire' => ['required', 'integer', 'min:0'],
            ],
            'ecs' => [
                'ue_id' => ['required', 'exists:ues,id'],
                'code' => ['required', 'string', 'max:255', Rule::unique('ecs', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'volume_horaire' => ['required', 'integer', 'min:1'],
                'statut' => ['required', Rule::in(['Non commencé', 'En cours', 'Entièrement dispensé'])],
            ],
            'enseignants' => [
                'user_id' => ['nullable', 'exists:users,id'],
                'matricule' => ['required', 'string', 'max:255', Rule::unique('enseignants', 'matricule')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'prenom' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('enseignants', 'email')->ignore($id)],
                'telephone' => ['nullable', 'string', 'max:255'],
                'grade' => ['nullable', 'string', 'max:255'],
                'specialite' => ['nullable', 'string', 'max:255'],
                'domaine_id' => ['nullable', 'exists:domaines,id'],
                'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
            ],
            'batiments' => self::basicRules('batiments', $id, true),
            'auditoires' => [
                'batiment_id' => ['required', 'exists:batiments,id'],
                'nom' => ['required', 'string', 'max:255'],
                'capacite' => ['required', 'integer', 'min:1'],
                'etat' => ['required', Rule::in(['Disponible', 'Indisponible', 'Maintenance'])],
            ],
            'demandes' => [
                'user_id' => ['required', 'exists:users,id'],
                'ec_id' => ['required', 'exists:ecs,id'],
                'enseignant_id' => ['required', 'exists:enseignants,id'],
                'promotions_concernees' => ['required', 'array'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'heure_debut' => ['required', 'date_format:H:i'],
                'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
                'effectif_total' => ['required', 'integer', 'min:1'],
                'statut' => ['required', Rule::in(['En attente', 'Acceptée', 'Refusée', 'Attribuée'])],
                'motif_refus' => ['nullable', 'string'],
            ],
            'programmations' => [
                'demande_auditoire_id' => ['nullable', 'exists:demandes_auditoire,id'],
                'ec_id' => ['required', 'exists:ecs,id'],
                'enseignant_id' => ['required', 'exists:enseignants,id'],
                'auditoire_id' => ['required', 'exists:auditoires,id'],
                'promotions_concernees' => ['required', 'array'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'heure_debut' => ['required', 'date_format:H:i'],
                'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
                'effectif_total' => ['required', 'integer', 'min:1'],
                'statut' => ['required', Rule::in(['Brouillon', 'Validée', 'Annulée'])],
            ],
            default => abort(404),
        };
    }

    public static function config(string $resource): array
    {
        $configs = [
            'roles' => [
                'model' => Role::class,
                'title' => 'Rôles',
                'singular' => 'Rôle',
                'search' => ['nom', 'description'],
                'fields' => ['nom' => 'text', 'description' => 'textarea'],
                'columns' => ['nom', 'description'],
            ],
            'users' => [
                'model' => User::class,
                'title' => 'Utilisateurs',
                'singular' => 'Utilisateur',
                'search' => ['name', 'email'],
                'with' => ['role', 'domaine', 'filiere', 'mention', 'promotion'],
                'fields' => ['role_id' => 'select:roles', 'name' => 'text', 'email' => 'email', 'password' => 'password', 'domaine_id' => 'select:domaines', 'filiere_id' => 'select:filieres', 'mention_id' => 'select:mentions', 'promotion_id' => 'select:promotions'],
                'columns' => ['name', 'email', 'role.nom', 'domaine.nom', 'filiere.nom', 'mention.nom', 'confirme'],
            ],
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
                'with' => ['anneeAcademique'],
                'fields' => ['annee_academique_id' => 'select:annees-academiques', 'code' => 'text', 'nom' => 'text', 'description' => 'textarea'],
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
                'search' => ['matricule', 'nom', 'prenom', 'email', 'specialite'],
                'with' => ['user', 'domaine'],
                'fields' => ['user_id' => 'select:users', 'matricule' => 'text', 'nom' => 'text', 'prenom' => 'text', 'email' => 'email', 'telephone' => 'text', 'grade' => 'text', 'specialite' => 'text', 'domaine_id' => 'select:domaines', 'statut' => 'enum:Actif,Inactif'],
                'columns' => ['matricule', 'nom', 'prenom', 'email', 'grade', 'specialite', 'statut'],
            ],
            'batiments' => [
                'model' => Batiment::class,
                'title' => 'Bâtiments',
                'singular' => 'Bâtiment',
                'search' => ['code', 'nom', 'localisation'],
                'fields' => ['code' => 'text', 'nom' => 'text', 'localisation' => 'text', 'description' => 'textarea'],
                'columns' => ['code', 'nom', 'localisation'],
            ],
            'auditoires' => [
                'model' => Auditoire::class,
                'title' => 'Auditoires',
                'singular' => 'Auditoire',
                'search' => ['nom'],
                'with' => ['batiment'],
                'fields' => ['batiment_id' => 'select:batiments', 'nom' => 'text', 'capacite' => 'number', 'etat' => 'enum:Disponible,Indisponible,Maintenance'],
                'columns' => ['nom', 'capacite', 'etat', 'batiment.nom'],
            ],
            'demandes' => [
                'model' => DemandeAuditoire::class,
                'title' => 'Demandes d\'auditoire',
                'singular' => 'Demande',
                'search' => ['statut', 'motif_refus'],
                'with' => ['user', 'ec', 'enseignant'],
                'fields' => ['user_id' => 'select:users', 'ec_id' => 'select:ecs', 'enseignant_id' => 'select:enseignants', 'promotions_concernees' => 'select-multiple:promotions', 'date_debut' => 'date', 'date_fin' => 'date', 'heure_debut' => 'time', 'heure_fin' => 'time', 'effectif_total' => 'number', 'statut' => 'enum:En attente,Acceptée,Refusée,Attribuée'],
                'columns' => ['user.name', 'ec.nom', 'enseignant.nom', 'date_debut', 'heure_debut', 'heure_fin', 'effectif_total', 'statut'],
            ],
            'programmations' => [
                'model' => Programmation::class,
                'title' => 'Programmations',
                'singular' => 'Programmation',
                'search' => ['statut'],
                'with' => ['demandeAuditoire', 'ec', 'enseignant', 'auditoire'],
                'fields' => ['demande_auditoire_id' => 'select:demandes', 'ec_id' => 'select:ecs', 'enseignant_id' => 'select:enseignants', 'auditoire_id' => 'select:auditoires', 'promotions_concernees' => 'select-multiple:promotions', 'date_debut' => 'date', 'date_fin' => 'date', 'heure_debut' => 'time', 'heure_fin' => 'time', 'effectif_total' => 'number', 'statut' => 'enum:Brouillon,Validée,Annulée'],
                'columns' => ['ec.nom', 'enseignant.nom', 'auditoire.nom', 'date_debut', 'heure_debut', 'heure_fin', 'statut'],
            ],
        ];

        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }

    private static function basicRules(string $table, ?int $id, bool $localisation = false): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255', Rule::unique($table, 'code')->ignore($id)],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];

        if ($localisation) {
            $rules['localisation'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    private function options(array $config): array
    {
        $options = [];

        foreach ($config['fields'] as $definition) {
            if (! str_starts_with($definition, 'select:') && ! str_starts_with($definition, 'select-multiple:')) {
                continue;
            }

            $resource = str_replace(['select:', 'select-multiple:'], '', $definition);
            $optionConfig = self::config($resource);
            $options[$resource] = $optionConfig['model']::query()
                ->orderBy($this->optionOrderColumn($resource))
                ->get()
                ->map(fn (Model $model): array => [
                    'id' => $model->getKey(),
                    'label' => $this->optionLabel($resource, $model),
                ]);
        }

        return $options;
    }

    private function payload(array $data, array $config, ?Model $item = null): array
    {
        if (array_key_exists('active', $config['fields'])) {
            $data['active'] = (bool) ($data['active'] ?? false);
        }

        if (array_key_exists('password', $data) && $data['password'] === null) {
            unset($data['password']);
        }

        if ($item !== null && array_key_exists('password', $data) && $data['password'] === '') {
            unset($data['password']);
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
            'roles' => $model->nom,
            'users' => $model->name.' - '.$model->email,
            'annees-academiques' => $model->libelle,
            default => trim(($model->code ?? '').' '.$model->nom),
        };
    }
}
