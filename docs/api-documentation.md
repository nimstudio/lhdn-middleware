# API Documentation

## 🔌 **LHDN MyInvois Integration API**

This document outlines the API integration with LHDN's MyInvois system and the internal service APIs.

---

## 🏗️ **MyInvois SDK Service**

### **Service Overview**
The `MyInvoisSdkService` handles all interactions with LHDN's MyInvois API system, including authentication, token management, and TIN validation.

### **Service Methods**

#### **getClient(LhdnCredential $credentials): MyInvoisClient**
Creates and configures a MyInvois client with stored credentials.

**Parameters:**
- `$credentials` (LhdnCredential): Company's LHDN credentials

**Returns:**
- `MyInvoisClient`: Configured client instance

**Usage:**
```php
$client = $myInvoisService->getClient($credentials);
```

#### **authenticate(LhdnCredential $credentials): array**
Authenticates with LHDN and retrieves access token.

**Parameters:**
- `$credentials` (LhdnCredential): Company's LHDN credentials

**Returns:**
```php
[
    'access_token' => 'string',
    'token_type' => 'Bearer',
    'expires_at' => 'Carbon\Carbon'
]
```

**Usage:**
```php
$authData = $myInvoisService->authenticate($credentials);
```

#### **ensureValidToken(LhdnCredential $credentials): bool**
Ensures the stored token is valid, refreshes if necessary.

**Parameters:**
- `$credentials` (LhdnCredential): Company's LHDN credentials

**Returns:**
- `bool`: True if token is valid, false if authentication failed

**Usage:**
```php
$isValid = $myInvoisService->ensureValidToken($credentials);
```

---

## 🔍 **TIN Validation Service**

### **Service Overview**
The `TinValidationService` handles TIN validation logic, including LHDN integration and uniqueness checks.

### **Service Methods**

#### **validateCompanyTin(Company $company): array**
Validates a company's TIN with LHDN system.

**Parameters:**
- `$company` (Company): Company instance to validate

**Returns:**
```php
[
    'success' => 'bool',
    'message' => 'string',
    'tin_status' => 'valid|invalid|pending',
    'updated_tin' => 'string|null' // If TIN was updated
]
```

**Success Response:**
```php
[
    'success' => true,
    'message' => 'TIN verified successfully',
    'tin_status' => 'valid'
]
```

**Error Response:**
```php
[
    'success' => false,
    'message' => 'TIN not found in LHDN system. Please verify your BRN.',
    'tin_status' => 'invalid'
]
```

**Usage:**
```php
$result = $tinService->validateCompanyTin($company);
if ($result['success']) {
    // TIN is valid
} else {
    // Handle validation error
}
```

#### **hasValidTin(Company $company): bool**
Checks if company has a valid TIN.

**Parameters:**
- `$company` (Company): Company instance

**Returns:**
- `bool`: True if TIN is valid

**Usage:**
```php
$hasValidTin = $tinService->hasValidTin($company);
```

#### **requiresTinValidation(Company $company): bool**
Determines if company requires TIN validation.

**Parameters:**
- `$company` (Company): Company instance

**Returns:**
- `bool`: True if TIN validation is required

**Usage:**
```php
$requiresValidation = $tinService->requiresTinValidation($company);
```

---

## 📄 **PDF Generation Service**

### **Service Overview**
The `InvoicePdfService` handles PDF generation for invoices with customizable templates.

### **Service Methods**

#### **generatePdf(Invoice $invoice): string**
Generates PDF for an invoice.

**Parameters:**
- `$invoice` (Invoice): Invoice instance

**Returns:**
- `string`: PDF content as string

**Usage:**
```php
$pdfContent = $pdfService->generatePdf($invoice);
```

#### **generatePreview(Invoice $invoice): string**
Generates PDF preview for an invoice.

**Parameters:**
- `$invoice` (Invoice): Invoice instance

**Returns:**
- `string`: PDF preview content

**Usage:**
```php
$previewContent = $pdfService->generatePreview($invoice);
```

#### **getPdfSettings(Company $company): array**
Retrieves PDF settings for a company.

**Parameters:**
- `$company` (Company): Company instance

**Returns:**
```php
[
    'logo_url' => 'string|null',
    'primary_color' => 'string',
    'secondary_color' => 'string',
    'template' => 'string',
    'font_family' => 'string'
]
```

**Usage:**
```php
$settings = $pdfService->getPdfSettings($company);
```

---

## 🔢 **Invoice Number Generator Service**

### **Service Overview**
The `InvoiceNumberGenerator` handles unique invoice number generation.

### **Service Methods**

#### **generate(Company $company): string**
Generates next invoice number for a company.

**Parameters:**
- `$company` (Company): Company instance

**Returns:**
- `string`: Generated invoice number

**Usage:**
```php
$invoiceNumber = $generator->generate($company);
```

#### **getNextNumber(Company $company): int**
Gets the next sequential number for a company.

**Parameters:**
- `$company` (Company): Company instance

**Returns:**
- `int`: Next sequential number

**Usage:**
```php
$nextNumber = $generator->getNextNumber($company);
```

---

## 🔤 **Number to Words Service**

### **Service Overview**
The `NumberToWordsService` converts numbers to words for invoice display.

### **Service Methods**

#### **convertToWords(float $number, string $currency = 'MYR'): string**
Converts a number to words.

**Parameters:**
- `$number` (float): Number to convert
- `$currency` (string): Currency code (default: 'MYR')

**Returns:**
- `string`: Number in words

**Usage:**
```php
$words = $numberService->convertToWords(1234.56, 'MYR');
// Returns: "One Thousand Two Hundred Thirty-Four Ringgit and Fifty-Six Sen"
```

---

## 🌐 **LHDN MyInvois API Integration**

### **Authentication Endpoint**
```
POST https://api.myinvois.hasil.gov.my/oauth/token
```

**Request:**
```json
{
    "grant_type": "client_credentials",
    "client_id": "your_client_id",
    "client_secret": "your_client_secret"
}
```

**Response:**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
}
```

### **TIN Search Endpoint**
```
GET https://api.myinvois.hasil.gov.my/v1.0/taxpayer/search/tin
```

**Parameters:**
- `idType`: 'BRN' (Business Registration Number)
- `idValue`: Company registration number
- `fileType`: '2' (for companies)

**Request:**
```http
GET /v1.0/taxpayer/search/tin?idType=BRN&idValue=202301234567&fileType=2
Authorization: Bearer {access_token}
```

**Response:**
```json
{
    "tin": "C12345678901",
    "name": "Company Name Sdn Bhd",
    "status": "active"
}
```

### **Invoice Submission Endpoint**
```
POST https://api.myinvois.hasil.gov.my/v1.0/invoice
```

**Request:**
```json
{
    "invoice": {
        "invoiceNumber": "INV-001",
        "invoiceDate": "2024-01-01",
        "dueDate": "2024-01-31",
        "seller": {
            "tin": "C12345678901",
            "name": "Seller Company Sdn Bhd"
        },
        "buyer": {
            "tin": "C98765432109",
            "name": "Buyer Company Sdn Bhd"
        },
        "items": [
            {
                "description": "Product Name",
                "quantity": 1,
                "unitPrice": 100.00,
                "taxRate": 6,
                "taxAmount": 6.00,
                "totalAmount": 106.00
            }
        ],
        "subtotal": 100.00,
        "taxAmount": 6.00,
        "totalAmount": 106.00
    }
}
```

**Response:**
```json
{
    "submissionId": "SUB123456789",
    "status": "submitted",
    "message": "Invoice submitted successfully"
}
```

---

## 🔒 **Error Handling**

### **Common Error Responses**

#### **Authentication Errors**
```json
{
    "error": "invalid_client",
    "error_description": "Client authentication failed"
}
```

#### **Validation Errors**
```json
{
    "error": "validation_failed",
    "message": "TIN not found in LHDN system",
    "details": {
        "field": "tin",
        "code": "TIN_NOT_FOUND"
    }
}
```

#### **Rate Limiting**
```json
{
    "error": "rate_limit_exceeded",
    "message": "Too many requests. Please try again later.",
    "retry_after": 60
}
```

### **Error Handling in Services**

#### **MyInvoisSdkService Error Handling**
```php
try {
    $authData = $this->authenticate($credentials);
    return $authData;
} catch (Exception $e) {
    Log::channel('myinvois')->error('Authentication failed', [
        'company_id' => $credentials->company_id,
        'error' => $e->getMessage()
    ]);
    
    throw new AuthenticationException('LHDN authentication failed: ' . $e->getMessage());
}
```

#### **TinValidationService Error Handling**
```php
try {
    $sdkTin = $taxpayerService->searchTaxPayerTin('', 'BRN', $company->registration_number, '2');
    
    if (empty($sdkTin)) {
        return [
            'success' => false,
            'message' => 'TIN not found in LHDN system. Please verify your BRN.',
            'tin_status' => 'invalid'
        ];
    }
    
    // Process TIN validation...
    
} catch (Exception $e) {
    Log::channel('myinvois')->error('TIN validation failed', [
        'company_id' => $company->id,
        'error' => $e->getMessage()
    ]);
    
    return [
        'success' => false,
        'message' => 'TIN validation failed: ' . $e->getMessage(),
        'tin_status' => 'invalid'
    ];
}
```

---

## 📊 **Logging & Monitoring**

### **Log Channels**

#### **MyInvois Log Channel**
```php
// config/logging.php
'myinvois' => [
    'driver' => 'daily',
    'path' => storage_path('logs/myinvois.log'),
    'level' => env('LOG_LEVEL', 'info'),
    'days' => 14,
    'replace_placeholders' => true,
],
```

#### **Logging Examples**
```php
// Authentication request
Log::channel('myinvois')->info('Auth request to MyInvois', [
    'company_id' => $credentials->company_id,
    'mode' => $credentials->mode,
    'client_id_prefix' => substr($credentials->client_id, 0, 6),
]);

// TIN validation
Log::channel('myinvois')->info('TIN validation request', [
    'company_id' => $company->id,
    'registration_number' => $company->registration_number,
    'current_tin' => $company->tin_number,
]);

// Error logging
Log::channel('myinvois')->error('TIN validation failed', [
    'company_id' => $company->id,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

---

## 🔧 **Configuration**

### **Environment Variables**
```bash
# LHDN API Configuration
LHDN_PROD_BASE_URL=https://api.myinvois.hasil.gov.my
LHDN_UAT_BASE_URL=https://uat-api.myinvois.hasil.gov.my
LHDN_API_TIMEOUT=30
LHDN_API_RETRY_ATTEMPTS=3
LHDN_LOG_CHANNEL=myinvois
LHDN_LOG_LEVEL=info
```

### **Service Configuration**
```php
// config/lhdn.php
'lhdn' => [
    'api' => [
        'base_urls' => [
            'production' => env('LHDN_PRODUCTION_URL', 'https://api.myinvois.hasil.gov.my'),
            'sandbox' => env('LHDN_SANDBOX_URL', 'https://api-sandbox.myinvois.hasil.gov.my'),
        ],
        'timeout' => env('LHDN_API_TIMEOUT', 30),
        'retry_attempts' => env('LHDN_API_RETRY_ATTEMPTS', 3),
    ],
    'logging' => [
        'channel' => env('LHDN_LOG_CHANNEL', 'myinvois'),
        'level' => env('LHDN_LOG_LEVEL', 'info'),
    ],
],
```

### **Service Configuration**
```php
// config/services.php
'myinvois' => [
    'sandbox_url' => env('MYINVOIS_SANDBOX_URL'),
    'production_url' => env('MYINVOIS_PRODUCTION_URL'),
    'timeout' => env('MYINVOIS_TIMEOUT', 30),
    'retry_attempts' => env('MYINVOIS_RETRY_ATTEMPTS', 3),
],
```

---

## 🧪 **Testing**

### **Unit Testing Examples**

#### **MyInvoisSdkService Test**
```php
public function test_authenticate_returns_valid_token()
{
    $credentials = LhdnCredential::factory()->create([
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'mode' => 'sandbox'
    ]);
    
    $service = new MyInvoisSdkService();
    $result = $service->authenticate($credentials);
    
    $this->assertArrayHasKey('access_token', $result);
    $this->assertArrayHasKey('token_type', $result);
    $this->assertArrayHasKey('expires_at', $result);
}
```

#### **TinValidationService Test**
```php
public function test_validate_company_tin_returns_success()
{
    $company = Company::factory()->create([
        'tin_number' => 'C12345678901',
        'registration_number' => '202301234567'
    ]);
    
    $service = new TinValidationService();
    $result = $service->validateCompanyTin($company);
    
    $this->assertTrue($result['success']);
    $this->assertEquals('valid', $result['tin_status']);
}
```

---

## 📚 **API Reference Summary**

### **Service Methods Quick Reference**

| Service | Method | Purpose |
|---------|--------|---------|
| MyInvoisSdkService | `getClient()` | Get configured client |
| MyInvoisSdkService | `authenticate()` | Authenticate with LHDN |
| MyInvoisSdkService | `ensureValidToken()` | Ensure valid token |
| TinValidationService | `validateCompanyTin()` | Validate company TIN |
| TinValidationService | `hasValidTin()` | Check TIN validity |
| TinValidationService | `requiresTinValidation()` | Check if validation required |
| InvoicePdfService | `generatePdf()` | Generate invoice PDF |
| InvoicePdfService | `generatePreview()` | Generate PDF preview |
| InvoiceNumberGenerator | `generate()` | Generate invoice number |
| NumberToWordsService | `convertToWords()` | Convert number to words |

### **LHDN API Endpoints**

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/oauth/token` | POST | Authentication |
| `/v1.0/taxpayer/search/tin` | GET | Search TIN |
| `/v1.0/invoice` | POST | Submit invoice |
| `/v1.0/invoice/{id}` | GET | Get invoice status |

---

*This API documentation provides comprehensive information about all service methods, LHDN integration, error handling, and testing approaches for the LHDN Middleware SaaS platform.*



