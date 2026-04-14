<?php

namespace App\Nova\Actions;

use App\Models\Branch;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AddBranchOnTenant extends Action
{
    use InteractsWithQueue, Queueable;

    public function name(): string
    {
        return 'Add Branch to School';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Tenant', 'tenant_id')
                ->options(Tenant::query()->orderBy('id')->pluck('id', 'id'))
                ->searchable()
                ->rules('required'),
            Text::make('Branch Name', 'branch_name')->rules('required', 'max:120'),
            Text::make('Branch Code', 'branch_code')->rules('required', 'alpha_dash', 'max:60'),
            Text::make('City', 'city')->nullable()->rules('max:120'),
            Text::make('Address', 'address')->nullable()->rules('max:255'),
            Text::make('School Name (if missing)', 'school_name')->nullable()->rules('max:120'),
        ];
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var \App\Models\Tenant $tenant */
        $tenant = Tenant::findOrFail($fields->tenant_id);

        try {
            tenancy()->initialize($tenant);

            // Ensure a School row exists (one-per-tenant pattern)
            $school = School::first();
            if (! $school) {
                $school = School::create([
                    'name' => $fields->school_name ?: ($tenant->name ?? 'School'),
                    'code' => 'default',
                    'status' => 'active',
                ]);
            }

            // Uniqueness: branch code per school
            if (Branch::where('school_id', $school->id)->where('code', $fields->branch_code)->exists()) {
                return Action::danger("Branch code '{$fields->branch_code}' already exists for this school.");
            }

            Branch::create([
                'school_id' => $school->id,
                'name' => $fields->branch_name,
                'code' => $fields->branch_code,
                'city' => $fields->city,
                'address' => $fields->address,
                'status' => 'active',
            ]);

            return Action::message("Branch '{$fields->branch_name}' created.");
        } catch (\Throwable $e) {
            return Action::danger('Failed: '.$e->getMessage());
        } finally {
            try {
                tenancy()->end();
            } catch (\Throwable $ignore) {
            }
        }
    }
}
