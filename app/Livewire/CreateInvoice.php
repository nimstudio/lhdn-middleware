<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateInvoice extends Component
{
    public $documentType = 'invoice';
    public $invoiceNumber;
    public $invoiceDate;
    public $dueDate;
    public $items = [];
    public $customerName;
    public $customerEmail;
    public $customerPhone;
    public $customerStreetAddress;
    public $customerCity;
    public $customerStateId;
    public $customerPostalCode;
    public $customerTin;
    public $customerDocumentType;
    public $customerDocumentNumber;
    public $discountAmount = 0;
    public $notes;
    public $paymentMethod = 'Bank Transfer';

    public $selectedCustomerId;
    public $searchQuery = '';
    public $searchResults = [];
    public $showDropdown = false;

    protected $listeners = ['customerSelected' => 'selectCustomer'];

    public function mount()
    {
        $this->invoiceDate = now()->toDateString();
        $this->dueDate = now()->addDays(30)->toDateString();
        $this->items = [
            [
                'description' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'tax_rate' => 0,
                'item_classification_id' => auth()->user()->company->default_item_classification_id ?? 1
            ]
        ];
        $this->generateInvoiceNumber();
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 2) {
            $this->searchResults = Customer::where('company_id', auth()->id())
                ->where(function($query) {
                    $query->where('name', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('tin', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('phone', 'like', '%' . $this->searchQuery . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
            $this->showDropdown = !empty($this->searchResults);
        } else {
            $this->searchResults = [];
            $this->showDropdown = false;
        }
    }

    public function selectCustomer($customer)
    {
        $this->selectedCustomerId = $customer['id'];
        $this->customerName = $customer['name'];
        $this->customerEmail = $customer['email'];
        $this->customerPhone = $customer['phone'];
        $this->customerStreetAddress = $customer['street_address'];
        $this->customerCity = $customer['city'];
        $this->customerStateId = $customer['state_id'];
        $this->customerPostalCode = $customer['postal_code'];
        $this->customerTin = $customer['tin'];
        $this->customerDocumentType = $customer['document_type'];
        $this->customerDocumentNumber = $customer['document_number'];
        $this->searchQuery = $customer['name'];
        $this->showDropdown = false;
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 0,
            'item_classification_id' => auth()->user()->company->default_item_classification_id ?? 1
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function generateInvoiceNumber()
    {
        $prefix = match($this->documentType) {
            'credit_note' => 'CN',
            'debit_note' => 'DN',
            default => 'INV'
        };

        $latest = Invoice::where('company_id', auth()->user()->company_id)
                        ->where('document_type', $this->documentType)
                        ->where('invoice_number', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->first();

        $number = 1;
        if ($latest) {
            $num = intval(substr($latest->invoice_number, strlen($prefix)));
            $number = $num + 1;
        }

        $this->invoiceNumber = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function updatedDocumentType()
    {
        $this->generateInvoiceNumber();
    }

    public function save($status = 'draft')
    {
        $this->validate([
            'documentType' => 'required|in:invoice,credit_note,debit_note,self_billed_invoice',
            'invoiceNumber' => 'required|string|max:50|unique:invoices,invoice_number',
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date|after:invoice_date',
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:20',
            'customerStreetAddress' => 'required|string|max:255',
            'customerCity' => 'required|string|max:100',
            'customerStateId' => 'required|exists:states,id',
            'customerPostalCode' => 'required|string|max:10',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'items.*.item_classification_id' => 'required|exists:item_classifications,id',
        ]);

        DB::transaction(function () use ($status) {
            // Create or find customer
            $customer = $this->findOrCreateCustomer();

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => auth()->user()->company_id,
                'customer_id' => $customer->id,
                'document_type' => $this->documentType,
                'invoice_number' => $this->invoiceNumber,
                'invoice_date' => $this->invoiceDate,
                'due_date' => $this->dueDate,
                'currency' => 'MYR',
                'subtotal' => collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_price']),
                'tax_amount' => collect($this->items)->sum(fn($item) => ($item['quantity'] * $item['unit_price']) * ($item['tax_rate'] / 100)),
                'discount_amount' => $this->discountAmount,
                'total_amount' => $this->calculateTotal(),
                'invoice_status' => $status === 'pending' ? 'paid' : 'draft',
                'payment_method' => $this->paymentMethod,
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            // Create invoice items
            foreach ($this->items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'],
                    'tax_amount' => ($itemData['quantity'] * $itemData['unit_price']) * ($itemData['tax_rate'] / 100),
                    'discount_amount' => 0,
                    'line_total' => ($itemData['quantity'] * $itemData['unit_price']) * (1 + $itemData['tax_rate'] / 100),
                    'item_classification_id' => $itemData['item_classification_id'],
                    'total_amount' => ($itemData['quantity'] * $itemData['unit_price']) * (1 + $itemData['tax_rate'] / 100),
                ]);
            }

            if ($status === 'pending') {
                // Submit to LHDN
                // This would call the SDK service
            }
        });

        session()->flash('message', 'Invoice created successfully!');
        return redirect()->route('user.invoices.show', $invoice);
    }

    private function findOrCreateCustomer()
    {
        $customer = null;

        // Try to find by selected customer
        if ($this->selectedCustomerId) {
            $customer = Customer::find($this->selectedCustomerId);
        }

        // Try to find by TIN
        if (!$customer && !empty($this->customerTin)) {
            $customer = Customer::where('company_id', auth()->user()->company_id)
                               ->where('tin', $this->customerTin)
                               ->first();
        }

        // Try to find by phone
        if (!$customer) {
            $customer = Customer::where('company_id', auth()->user()->company_id)
                               ->where('phone', $this->customerPhone)
                               ->first();
        }

        // Create new customer
        if (!$customer) {
            $customer = Customer::create([
                'company_id' => auth()->user()->company_id,
                'name' => $this->customerName,
                'email' => $this->customerEmail,
                'phone' => $this->customerPhone,
                'street_address' => $this->customerStreetAddress,
                'city' => $this->customerCity,
                'state_id' => $this->customerStateId,
                'postal_code' => $this->customerPostalCode,
                'country' => 'MYS',
                'tin' => $this->customerTin,
                'document_type' => $this->customerDocumentType,
                'document_number' => $this->customerDocumentNumber,
            ]);
        }

        return $customer;
    }

    private function calculateTotal()
    {
        $subtotal = collect($this->items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
        $tax = collect($this->items)->sum(fn($item) => ($item['quantity'] * $item['unit_price']) * ($item['tax_rate'] / 100));
        return $subtotal + $tax - $this->discountAmount;
    }

    public function render()
    {
        return view('livewire.create-invoice', [
            'states' => \App\Models\State::all(),
            'itemClassifications' => \App\Models\ItemClassification::all(),
        ]);
    }
}