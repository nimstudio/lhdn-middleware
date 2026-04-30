<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    protected $defaultSettings = [
        'template' => 'malaysian',
        'header' => [
            'enabled' => true,
            'logo_position' => 'left',
            'company_info' => true,
            'custom_text' => ''
        ],
        'footer' => [
            'enabled' => true,
            'show_page_numbers' => true,
            'custom_text' => 'Thank you for your business!',
            'show_terms' => true
        ],
        'colors' => [
            'primary' => '#3B82F6',
            'secondary' => '#6B7280',
            'accent' => '#10B981'
        ],
        'layout' => [
            'font_family' => 'Arial',
            'font_size' => 12,
            'line_spacing' => 1.2,
            'margins' => [
                'top' => 20,
                'right' => 15,
                'bottom' => 20,
                'left' => 15
            ]
        ],
        'sections' => [
            'show_customer_details' => true,
            'show_payment_terms' => true,
            'show_notes' => true,
            'show_item_descriptions' => true,
            'show_tax_breakdown' => true
        ]
    ];

    public function generate(Invoice $invoice, array $customSettings = null)
    {
        try {
            $settings = $this->mergeSettings($customSettings, $invoice->company->pdf_settings ?? []);

            $invoice->load(['items', 'company']);

            $template = $settings['template'] ?? 'malaysian';

            $pdf = Pdf::loadView("invoices.templates.{$template}", [
                'invoice' => $invoice,
                'settings' => $settings,
                'company' => $invoice->company
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial',
                'debugKeepTemp' => true,
                'debugCss' => false
            ]);

            return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateAdvanced(Invoice $invoice, array $customSettings = null)
    {
        $settings = $this->mergeSettings($customSettings, $invoice->company->pdf_settings ?? []);

        $invoice->load(['items', 'company']);

        $template = $settings['template'] ?? 'malaysian';

        // Use Browsershot for better CSS support
        return Browsershot::html(view("invoices.templates.{$template}", [
            'invoice' => $invoice,
            'settings' => $settings,
            'company' => $invoice->company
        ])->render())
        ->format('A4')
        ->landscape(false);
    }

    public function generatePreview(Invoice $invoice, array $customSettings = null)
    {
        $settings = $this->mergeSettings($customSettings, $invoice->company->pdf_settings ?? []);

        $invoice->load(['items', 'company']);

        $template = $settings['template'] ?? 'malaysian';

        return view("invoices.templates.{$template}", [
            'invoice' => $invoice,
            'settings' => $settings,
            'company' => $invoice->company
        ]);
    }

    public function getAvailableTemplates()
    {
        return [
            'malaysian' => [
                'name' => 'Malaysian',
                'description' => 'Authentic Malaysian tax invoice format',
                'preview' => 'malaysian-preview.png'
            ],
            'modern' => [
                'name' => 'Modern',
                'description' => 'Clean & contemporary design',
                'preview' => 'modern-preview.png'
            ]
        ];
    }

    public function getDefaultSettings()
    {
        return $this->defaultSettings;
    }

    public function mergeSettings(array $customSettings = null, array $companySettings = [])
    {
        $settings = $this->defaultSettings;

        if (!empty($companySettings)) {
            $settings = $this->deepMerge($settings, $companySettings);
        }

        if (!empty($customSettings)) {
            $settings = $this->deepMerge($settings, $customSettings);
        }

        return $settings;
    }

    protected function deepMerge($default, $settings)
    {
        foreach ($default as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            } elseif (is_array($value) && is_array($settings[$key])) {
                $settings[$key] = $this->deepMerge($value, $settings[$key]);
            }
            // For non-array values, keep the settings value if it exists
        }
        return $settings;
    }

    public function validateSettings(array $settings)
    {
        $errors = [];

        // Validate template
        $availableTemplates = array_keys($this->getAvailableTemplates());
        if (isset($settings['template']) && !in_array($settings['template'], $availableTemplates)) {
            $errors[] = 'Invalid template selected';
        }

        // Validate colors
        if (isset($settings['colors'])) {
            foreach ($settings['colors'] as $colorKey => $colorValue) {
                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorValue)) {
                    $errors[] = "Invalid color format for {$colorKey}";
                }
            }
        }

        // Validate font size
        if (isset($settings['layout']['font_size']) &&
            (!is_numeric($settings['layout']['font_size']) ||
             $settings['layout']['font_size'] < 8 ||
             $settings['layout']['font_size'] > 24)) {
            $errors[] = 'Font size must be between 8 and 24';
        }

        return $errors;
    }
}
