<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WhatsAppTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(): View
    {
        $templates = WhatsAppTemplate::where('tenant_id', tenant('id'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('tenant.admin.whatsapp.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): View
    {
        return view('tenant.admin.whatsapp.templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'meta_template_name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'language' => ['required', 'string', 'max:10'],
            'category' => ['required', 'in:MARKETING,UTILITY,AUTHENTICATION'],
            'description' => ['nullable', 'string', 'max:500'],
            'parameter_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'parameter_names' => ['nullable', 'string'],
        ]);

        // Parse parameter names if provided
        $parameters = null;
        if ($request->filled('parameter_names')) {
            $paramNames = array_filter(array_map('trim', explode(',', $request->parameter_names)));
            if (! empty($paramNames)) {
                $parameters = array_map(fn ($i, $name) => [
                    'index' => $i + 1,
                    'name' => $name,
                ], array_keys($paramNames), $paramNames);
            }
        }

        try {
            WhatsAppTemplate::create([
                'tenant_id' => tenant('id'),
                'name' => $request->name,
                'meta_template_name' => $request->meta_template_name,
                'language' => $request->language,
                'category' => $request->category,
                'description' => $request->description,
                'status' => 'approved', // Assume it's already approved in Meta
                'components' => null,
                'parameters' => $parameters,
                'is_active' => true,
            ]);

            return redirect()->route('tenant.admin.whatsapp.templates.index')
                ->with('ok', 'WhatsApp template imported successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate template name
            if (str_contains($e->getMessage(), 'unique constraint') || str_contains($e->getMessage(), 'Unique violation')) {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'A template with this name and language already exists.']);
            }

            // Handle other database errors
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to import template. Please check your input and try again.']);
        } catch (\Throwable $e) {
            // Handle unexpected errors
            Log::error('WhatsApp template creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Show the form for editing a template.
     */
    public function edit(WhatsAppTemplate $template): View
    {
        // Ensure template belongs to current tenant
        if ($template->tenant_id !== tenant('id')) {
            abort(404);
        }

        return view('tenant.admin.whatsapp.templates.edit', compact('template'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, WhatsAppTemplate $template): RedirectResponse
    {
        // Ensure template belongs to current tenant
        if ($template->tenant_id !== tenant('id')) {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'meta_template_name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'language' => ['required', 'string', 'max:10'],
            'category' => ['required', 'in:MARKETING,UTILITY,AUTHENTICATION'],
            'description' => ['nullable', 'string', 'max:500'],
            'parameter_names' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        // Parse parameter names if provided
        $parameters = $template->parameters;
        if ($request->filled('parameter_names')) {
            $paramNames = array_filter(array_map('trim', explode(',', $request->parameter_names)));
            if (! empty($paramNames)) {
                $parameters = array_map(fn ($i, $name) => [
                    'index' => $i + 1,
                    'name' => $name,
                ], array_keys($paramNames), $paramNames);
            } else {
                $parameters = null;
            }
        }

        try {
            $template->update([
                'name' => $request->name,
                'meta_template_name' => $request->meta_template_name,
                'language' => $request->language,
                'category' => $request->category,
                'description' => $request->description,
                'parameters' => $parameters,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('tenant.admin.whatsapp.templates.index')
                ->with('ok', 'Template updated successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate template name
            if (str_contains($e->getMessage(), 'unique constraint') || str_contains($e->getMessage(), 'Unique violation')) {
                return back()
                    ->withInput()
                    ->withErrors(['name' => 'A template with this name and language already exists.']);
            }

            // Handle other database errors
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update template. Please check your input and try again.']);
        } catch (\Throwable $e) {
            // Handle unexpected errors
            Log::error('WhatsApp template update failed', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Remove the specified template.
     */
    public function destroy(WhatsAppTemplate $template): RedirectResponse
    {
        // Ensure template belongs to current tenant
        if ($template->tenant_id !== tenant('id')) {
            abort(404);
        }

        $template->delete();

        return redirect()->route('tenant.admin.whatsapp.templates.index')
            ->with('ok', 'Template deleted successfully.');
    }
}
