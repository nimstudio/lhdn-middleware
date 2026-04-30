<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfSettingsController extends Controller
{
    protected $pdfService;

    public function __construct(InvoicePdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $company = Auth::user()->company;
        $templates = $this->pdfService->getAvailableTemplates();
        $currentSettings = $company->pdf_settings ?? $this->pdfService->getDefaultSettings();

        return view('user-app.settings.pdf', compact('company', 'templates', 'currentSettings'));
    }

    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $validated = $request->validate([
            'pdf_settings' => 'required|array',
            'pdf_settings.template' => 'required|string|in:malaysian,modern',
            'pdf_settings.header.enabled' => 'boolean',
            'pdf_settings.header.logo_position' => 'string|in:left,center,right',
            'pdf_settings.header.company_info' => 'boolean',
            'pdf_settings.header.custom_text' => 'nullable|string|max:500',
            'pdf_settings.footer.enabled' => 'boolean',
            'pdf_settings.footer.show_page_numbers' => 'boolean',
            'pdf_settings.footer.custom_text' => 'nullable|string|max:500',
            'pdf_settings.footer.show_terms' => 'boolean',
            'pdf_settings.colors.primary' => 'string|regex:/^#[0-9A-Fa-f]{6}$/',
            'pdf_settings.colors.secondary' => 'string|regex:/^#[0-9A-Fa-f]{6}$/',
            'pdf_settings.colors.accent' => 'string|regex:/^#[0-9A-Fa-f]{6}$/',
            'pdf_settings.layout.font_family' => 'string|in:Arial,Times,Helvetica,Georgia',
            'pdf_settings.layout.font_size' => 'integer|min:8|max:24',
            'pdf_settings.layout.line_spacing' => 'numeric|min:1|max:2',
            'pdf_settings.layout.margins.top' => 'integer|min:10|max:50',
            'pdf_settings.layout.margins.right' => 'integer|min:10|max:50',
            'pdf_settings.layout.margins.bottom' => 'integer|min:10|max:50',
            'pdf_settings.layout.margins.left' => 'integer|min:10|max:50',
            'pdf_settings.sections.show_customer_details' => 'boolean',
            'pdf_settings.sections.show_payment_terms' => 'boolean',
            'pdf_settings.sections.show_notes' => 'boolean',
            'pdf_settings.sections.show_item_descriptions' => 'boolean',
            'pdf_settings.sections.show_tax_breakdown' => 'boolean',
        ]);

        // Validate settings using the service
        $errors = $this->pdfService->validateSettings($validated['pdf_settings']);
        if (!empty($errors)) {
            return redirect()->back()->withErrors(['pdf_settings' => $errors])->withInput();
        }

        // Use the service's mergeSettings method to ensure proper merging
        $mergedSettings = $this->pdfService->mergeSettings($validated['pdf_settings'], []);

        $company->update([
            'pdf_settings' => $mergedSettings
        ]);

        return redirect()->route('user.settings.pdf')->with('success', 'PDF settings updated successfully!');
    }

    public function preview(Request $request)
    {
        $company = Auth::user()->company;
        $invoice = $company->invoices()->with('items')->latest()->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'No invoices found for preview. Create an invoice first.');
        }

        $customSettings = $request->get('settings');

        return $this->pdfService->generatePreview($invoice, $customSettings);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,svg|max:2048'
        ]);

        $company = Auth::user()->company;

        // Clear existing logo
        $company->clearMediaCollection('pdf_logo');

        // Add new logo
        $company->addMediaFromRequest('logo')
            ->toMediaCollection('pdf_logo');

        return redirect()->back()->with('success', 'Logo uploaded successfully!');
    }

    public function removeLogo()
    {
        $company = Auth::user()->company;
        $company->clearMediaCollection('pdf_logo');

        return redirect()->back()->with('success', 'Logo removed successfully!');
    }

    public function reset()
    {
        $company = Auth::user()->company;
        $company->update([
            'pdf_settings' => $this->pdfService->getDefaultSettings()
        ]);

        return redirect()->back()->with('success', 'PDF settings reset to defaults!');
    }
}
