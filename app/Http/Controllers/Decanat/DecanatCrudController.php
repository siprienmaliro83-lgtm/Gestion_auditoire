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
use App\Models\Ue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DecanatCrudController extends Controller
{
    public function index(string $resource): View
    {
        $config = self::config($resource);
        $query = $config['model']::query();

        foreach ($config['with'] ?? [] as $relation) {
            $query->with($relation);
        }

        $this->scopeToDomaine($query, $resource, auth()->user()?->domaine_id);

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
        $config = self::config($resource);

        return view('decanat.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => new $config['model'](),
            'options' => $this->options($config),
            'ecs' => $resource === 'enseignants' ? $this->domainEcs() : null,
            'enseignant' => null,
        ]);
    }

    public function store(DecanatCrudRequest $request, string $resource): RedirectResponse
    {
        $config = self::config($resource);
        $data = $this->payload($request->validated(), $config);

        if ($resource === 'enseignants') {
            $ecIds = $request->input('ec_ids', []);
            unset($data['ec_ids']);
            $enseignant = $config['model']::create($data);
            if (! empty($ecIds)) {
                $enseignant->ecs()->attach($ecIds);
            }
        } elseif ($resource === 'programmes-academiques') {
            $promotionIds = $request->input('promotions', []);
            unset($data['promotions']);
            $programme = $config['model']::create($data);
            if (! empty($promotionIds)) {
                $programme->promotions()->attach($promotionIds);
            }
        } else {
            $config['model']::create($data);
        }

        return redirect()
            ->route('decanat.crud.index', $resource)
            ->with('success', $config['singular'].' ajouté avec succès.');
    }

    public function edit(string $resource, int $id): View
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        $ecs = null;
        $enseignant = null;
        if ($resource === 'enseignants') {
            $ecs = $this->domainEcs();
            $enseignant = $item;
        }

        return view('decanat.crud.form', [
            'resource' => $resource,
            'config' => $config,
            'item' => $item,
            'options' => $this->options($config),
            'ecs' => $ecs,
            'enseignant' => $enseignant,
        ]);
    }

    public function update(DecanatCrudRequest $request, string $resource, int $id): RedirectResponse
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);
        $data = $this->payload($request->validated(), $config, $item);

        if ($resource === 'enseignants') {
            $ecIds = $request->input('ec_ids', []);
            unset($data['ec_ids']);
            $item->update($data);
            $item->ecs()->sync($ecIds);
        } elseif ($resource === 'programmes-academiques') {
            $promotionIds = $request->input('promotions', []);
            unset($data['promotions']);
            $item->update($data);
            $item->promotions()->sync($promotionIds);
        } else {
            $item->update($data);
        }

        return redirect()
            ->route('decanat.crud.index', $resource)
            ->with('success', $config['singular'].' modifié avec succès.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = self::config($resource);
        $item = $config['model']::findOrFail($id);

        $item->delete();

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
                'domaine_id' => ['required', 'exists:domaines,id', self::inDomaineRule('Ce domaine ne vous est pas rattaché.', fn ($value, $domaineId) => Domaine::whereKey($value)->whereKey($domaineId))],
            ],
            'mentions' => self::basicRules('mentions', $id) + [
                'filiere_id' => ['required', 'exists:filieres,id', self::inDomaineRule('Cette filière n\'appartient pas à votre domaine.', fn ($value, $domaineId) => Filiere::whereKey($value)->where('domaine_id', $domaineId))],
            ],
            'promotions' => [
                'mention_id' => ['required', 'exists:mentions,id', self::inDomaineRule('Cette mention n\'appartient pas à votre domaine.', fn ($value, $domaineId) => Mention::whereKey($value)->whereHas('filiere', fn ($q) => $q->where('domaine_id', $domaineId)))],
                'code' => ['required', 'string', 'max:255', Rule::unique('promotions', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'niveau' => ['required', 'integer', 'min:1', 'max:5'],
                'effectif' => ['required', 'integer', 'min:0'],
            ],
            'annees-academiques' => [
                'libelle' => ['required', 'string', 'max:255', Rule::unique('annees_academiques', 'libelle')->ignore($id)],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after:date_debut'],
                'active' => ['nullable', 'boolean'],
            ],
            'programmes-academiques' => [
                'annee_academique_id' => ['required', 'exists:annees_academiques,id'],
                'code' => ['required', 'string', 'max:255', Rule::unique('programmes_academiques', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'promotions' => ['required', 'array', 'min:1'],
                'promotions.*' => ['exists:promotions,id', self::inDomaineRule('Une promotion sélectionnée n\'appartient pas à votre domaine.', fn ($value, $domaineId) => Promotion::whereKey($value)->whereHas('mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)))],
            ],
            'ues' => [
                'programme_academique_id' => ['required', 'exists:programmes_academiques,id', self::inDomaineRule('Ce programme n\'appartient pas à votre domaine.', fn ($value, $domaineId) => ProgrammeAcademique::whereKey($value)->whereHas('promotions.mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)))],
                'code' => ['required', 'string', 'max:255', Rule::unique('ues', 'code')->ignore($id)],
                'nom' => ['required', 'string', 'max:255'],
                'credits' => ['required', 'integer', 'min:0'],
                'volume_horaire' => ['required', 'integer', 'min:0'],
            ],
            'ecs' => [
                'ue_id' => ['required', 'exists:ues,id', self::inDomaineRule('Cette UE n\'appartient pas à votre domaine.', fn ($value, $domaineId) => Ue::whereKey($value)->whereHas('programmeAcademique.promotions.mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)))],
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
                'ec_ids' => ['nullable', 'array'],
                'ec_ids.*' => ['exists:ecs,id'],
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
                'search' => ['matricule', 'nom', 'prenom', 'email'],
                'with' => ['user', 'ecs'],
                'fields' => ['user_id' => 'select:users', 'matricule' => 'text', 'nom' => 'text', 'prenom' => 'text', 'email' => 'email', 'telephone' => 'text', 'grade' => 'text'],
                'columns' => ['matricule', 'nom', 'prenom', 'email', 'grade'],
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

    private static function inDomaineRule(string $message, \Closure $scope): \Closure
    {
        return function (string $attribute, $value, $fail) use ($message, $scope): void {
            $domaineId = auth()->user()?->domaine_id;

            if ($domaineId === null || $value === null) {
                return;
            }

            if (! $scope($value, $domaineId)->exists()) {
                $fail($message);
            }
        };
    }

    private function options(array $config): array
    {
        $domaineId = auth()->user()?->domaine_id;
        $options = [];

        foreach ($config['fields'] as $definition) {
            if (! str_starts_with($definition, 'select:') && ! str_starts_with($definition, 'select-multiple:')) {
                continue;
            }

            $resource = str_replace(['select:', 'select-multiple:'], '', $definition);
            $optionConfig = self::config($resource);
            $query = $optionConfig['model']::query();

            $this->scopeToDomaine($query, $resource, $domaineId);

            $options[$resource] = $query
                ->orderBy($this->optionOrderColumn($resource))
                ->get()
                ->map(fn (Model $model): array => [
                    'id' => $model->getKey(),
                    'label' => $this->optionLabel($resource, $model),
                ]);
        }

        return $options;
    }

    private function domainEcs(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Ec::with('ue');
        $this->scopeToDomaine($query, 'ecs', auth()->user()?->domaine_id);

        return $query->orderBy('code')->get();
    }

    private function scopeToDomaine($query, string $resource, ?int $domaineId): void
    {
        if ($domaineId === null) {
            return;
        }

        match ($resource) {
            'domaines' => $query->whereKey($domaineId),
            'filieres' => $query->where('domaine_id', $domaineId),
            'mentions' => $query->whereHas('filiere', fn ($q) => $q->where('domaine_id', $domaineId)),
            'promotions' => $query->whereHas('mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)),
            'programmes-academiques' => $query->whereHas('promotions.mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)),
            'ues' => $query->whereHas('programmeAcademique.promotions.mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)),
            'ecs' => $query->whereHas('ue.programmeAcademique.promotions.mention.filiere', fn ($q) => $q->where('domaine_id', $domaineId)),
            default => null,
        };
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
