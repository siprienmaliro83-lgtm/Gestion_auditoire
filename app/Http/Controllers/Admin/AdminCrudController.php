<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCrudRequest;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCrudController extends Controller
{
    public function confirmer(User $user): RedirectResponse
    {
        $user->update(['confirme' => !$user->confirme]);

        $action = $user->confirme ? 'confirmé' : 'déconfirmé';
        return redirect()->route('admin.crud.index', 'users')
            ->with('success', "Le compte de {$user->name} a été {$action}.");
    }

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

        $item->delete();

        return redirect()
            ->route('admin.crud.index', $resource)
            ->with('success', $config['singular'].' supprimé avec succès.');
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
                'confirme' => ['nullable', 'boolean'],
                'domaine_id' => ['nullable', 'exists:domaines,id'],
                'promotion_id' => ['nullable', 'exists:promotions,id'],
            ],
            'batiments' => self::basicRules('batiments', $id, true),
            'auditoires' => [
                'batiment_id' => ['required', 'exists:batiments,id'],
                'nom' => ['required', 'string', 'max:255'],
                'capacite' => ['required', 'integer', 'min:1'],
                'etat' => ['required', Rule::in(['Disponible', 'Indisponible', 'Maintenance'])],
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
                'with' => ['role', 'domaine', 'promotion'],
                'fields' => ['role_id' => 'select:roles', 'name' => 'text', 'email' => 'email', 'password' => 'password', 'confirme' => 'checkbox', 'domaine_id' => 'select:domaines', 'promotion_id' => 'select:promotions'],
                'columns' => ['name', 'email', 'role.nom', 'confirme', 'domaine.nom', 'promotion.nom'],
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
            'users' => 'name',
            default => 'nom',
        };
    }

    private function optionLabel(string $resource, Model $model): string
    {
        return match ($resource) {
            'roles' => $model->nom,
            'users' => $model->name.' - '.$model->email,
            default => trim(($model->code ?? '').' '.$model->nom),
        };
    }
}
