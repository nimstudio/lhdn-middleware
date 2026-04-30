# User Flow Documentation

## 🚀 **Complete User Journey**

This document outlines the complete user journey through the LHDN Middleware SaaS platform, from initial registration to full system utilization.

---

## 📋 **User Registration & Onboarding**

### **1. Initial Registration**
```
Landing Page → Sign Up → Email Verification → Plan Selection
```

**Steps:**
1. **Landing Page**: User visits homepage, views pricing and features
2. **Sign Up**: User creates account with email and password
3. **Email Verification**: User verifies email address
4. **Plan Selection**: User selects subscription plan (Starter/Business/Enterprise)

**Guards Applied:**
- `auth`: User must be logged in
- `verified`: Email must be verified

### **2. Subscription Process**
```
Plan Selection → Payment Proof Upload → Admin Review → Approval
```

**Steps:**
1. **Plan Selection**: User selects from available plans
2. **Payment Proof**: User uploads payment proof (bank transfer, receipt)
3. **Admin Review**: Admin reviews payment proof and company details
4. **Approval**: Admin approves subscription and activates account

**Guards Applied:**
- `subscription.paid`: Active subscription required for app access

---

## 🏢 **Company Setup (Mandatory)**

### **3. Company Profile Creation**
```
Dashboard → Company Setup → Form Completion → Validation → Save
```

**Required Fields:**
- Company Name
- Registration Number (SSM) - **Unique across system**
- Tax Identification Number (TIN) - **Unique across system**
- Business Type (MSIC) - **Mandatory selection**
- Address (Line 1, Line 2, City, State, Postcode)
- Contact Information (Phone, Email)

**Validation Process:**
1. **Form Validation**: All required fields validated
2. **Uniqueness Check**: TIN and registration number uniqueness validated
3. **TIN Validation** (if LHDN credentials exist):
   - Automatic LHDN TIN validation using BRN
   - TIN comparison and conflict resolution
   - Status update based on validation results

**Guards Applied:**
- `company.required`: Company profile required for core features

**User Experience:**
- **Success**: Redirected to company show page with success message
- **Error**: Stay on edit page with validation errors and preserved input
- **TIN Conflict**: Clear error message with guidance to contact support

---

## 🔐 **LHDN Credentials Setup (Optional)**

### **4. LHDN MyInvois Integration**
```
Company Setup → LHDN Credentials → API Configuration → Authentication Test
```

**Configuration Steps:**
1. **Access Credentials**: Navigate to LHDN Credentials section
2. **API Details**: Enter Client ID, Client Secret, and Mode (Sandbox/Production)
3. **Authentication**: System automatically tests credentials with LHDN
4. **Token Management**: Access tokens stored and managed automatically

**Validation Process:**
1. **Immediate Authentication**: Credentials tested upon save
2. **Token Storage**: Access tokens stored securely
3. **TIN Validation Trigger**: Company TIN automatically validated with LHDN
4. **Status Update**: Credential status updated (active/invalid)

**User Experience:**
- **Success**: Credentials saved and verified, TIN validation triggered
- **Failure**: Clear error message, credentials marked as invalid
- **Test Connection**: Manual test connection feature available

---

## 📊 **Core Features Access**

### **5. Dashboard Overview**
```
Login → Dashboard → Feature Overview → Quick Actions
```

**Dashboard Components:**
- **Company Status**: TIN validation status, credential status
- **Quick Stats**: Invoice counts, recent activity
- **Recent Invoices**: Latest invoices with status indicators
- **Quick Actions**: Create invoice, add customer, view reports
- **Onboarding Widget**: Hidden once company setup complete

**Status Indicators:**
- **TIN Status**: Valid (green), Invalid (red), Pending (yellow)
- **Credential Status**: Active (green), Invalid (red), Expired (yellow)
- **Subscription Status**: Active, Expiring, Expired

### **6. Customer Management**
```
Customers → Customer List → Add/Edit Customer → Save
```

**Features:**
- **Customer Database**: Store and manage customer information
- **Search & Filter**: Advanced search and filtering capabilities
- **Bulk Actions**: Import/export, bulk updates
- **Customer Details**: Name, email, phone, address, TIN (optional)

**Guards Applied:**
- `company.required`: Company profile required
- `tin.verified`: TIN validation required (if LHDN credentials exist)

### **7. Invoice Management**
```
Invoices → Create Invoice → Customer Selection → Line Items → Save
```

**Invoice Creation Process:**
1. **Basic Information**: Invoice number, date, due date
2. **Customer Selection**: Select from existing customers or create new
3. **Line Items**: Add products/services with quantities and prices
4. **Tax Calculation**: Automatic tax calculation based on company settings
5. **Total Calculation**: Automatic total calculation
6. **Save**: Invoice saved with draft status

**Invoice Features:**
- **Auto-numbering**: Configurable invoice number generation
- **Tax Calculation**: Automatic tax calculation
- **PDF Generation**: Professional PDF generation
- **Status Management**: Draft, sent, paid, overdue, cancelled
- **Payment Tracking**: Mark as paid with payment method
- **LHDN Submission**: Submit to LHDN (if credentials configured)

**Guards Applied:**
- `company.required`: Company profile required
- `tin.verified`: TIN validation required (if LHDN credentials exist)

---

## 📄 **PDF Generation & Customization**

### **8. PDF Settings & Generation**
```
Settings → PDF Settings → Customize Template → Preview → Save
```

**PDF Customization:**
- **Company Logo**: Upload and manage company logo
- **Color Scheme**: Customize colors and branding
- **Template Layout**: Adjust layout and formatting
- **Preview Mode**: Real-time PDF preview
- **Batch Generation**: Generate multiple PDFs

**PDF Features:**
- **Professional Templates**: Multiple template options
- **Company Branding**: Logo and color customization
- **Multiple Formats**: A4, Letter, custom sizes
- **Preview Mode**: Preview before generation
- **Download/Email**: Direct download or email options

---

## 🔄 **LHDN Integration Workflow**

### **9. Invoice Submission to LHDN**
```
Invoice Created → LHDN Submission → Status Tracking → Response Handling
```

**Submission Process:**
1. **Pre-submission Checks**:
   - Company TIN must be valid
   - LHDN credentials must be active
   - Invoice must be complete
2. **Submission**: Invoice submitted to LHDN via MyInvois SDK
3. **Status Tracking**: Submission status tracked and updated
4. **Response Handling**: LHDN response processed and stored

**Status Flow:**
- **Pending**: Invoice queued for submission
- **Submitted**: Successfully submitted to LHDN
- **Approved**: Approved by LHDN
- **Rejected**: Rejected by LHDN with error details

**Error Handling:**
- **Validation Errors**: Clear error messages for validation failures
- **API Errors**: Detailed error messages for API failures
- **Retry Logic**: Automatic retry for transient failures
- **Manual Retry**: Manual retry option for failed submissions

---

## ⚙️ **Settings & Configuration**

### **10. System Settings**
```
Settings → Profile Settings → Company Settings → Invoice Settings → Save
```

**Settings Categories:**
1. **Profile Settings**: User profile information
2. **Company Settings**: Company information and preferences
3. **Invoice Settings**: Invoice numbering, tax rates, defaults
4. **PDF Settings**: PDF template customization
5. **LHDN Credentials**: API credential management

**Configuration Options:**
- **Invoice Numbering**: Prefix, suffix, format customization
- **Tax Rates**: Default tax rates and calculations
- **PDF Templates**: Logo, colors, layout customization
- **API Credentials**: LHDN credential management and testing

---

## 🚨 **Error Handling & User Guidance**

### **11. Common Error Scenarios**

#### **TIN Validation Errors**
- **Duplicate TIN**: "This TIN number is already registered with another company"
- **LHDN Conflict**: "TIN returned by LHDN is already used by another company"
- **Invalid BRN**: "TIN not found in LHDN system. Please verify your BRN"

#### **Credential Errors**
- **Authentication Failed**: "LHDN authentication failed: [error details]"
- **Invalid Credentials**: "Invalid client ID or client secret"
- **Connection Failed**: "Could not connect to LHDN. Please check your credentials"

#### **Invoice Errors**
- **Validation Failed**: Form validation errors with specific field guidance
- **Submission Failed**: LHDN submission errors with retry options
- **PDF Generation Failed**: PDF generation errors with troubleshooting

### **12. User Guidance System**
- **Onboarding Widget**: Step-by-step guidance for new users
- **Status Indicators**: Clear visual indicators for system status
- **Error Messages**: User-friendly error messages with actionable guidance
- **Help Documentation**: Contextual help and documentation
- **Support Contact**: Easy access to support for complex issues

---

## 🔒 **Security & Access Control**

### **13. Access Control Flow**
```
Authentication → Email Verification → Subscription Check → Company Check → TIN Check
```

**Guard Hierarchy:**
1. **Authentication** (`auth`): User must be logged in
2. **Email Verification** (`verified`): Email must be verified
3. **Subscription** (`subscription.paid`): Active subscription required
4. **Company** (`company.required`): Company profile required
5. **TIN Validation** (`tin.verified`): TIN must be validated (if LHDN credentials exist)

**Access Levels:**
- **Public**: Landing page, authentication
- **Authenticated**: Basic app access
- **Subscribed**: Full app access
- **Company Setup**: Invoice and customer management
- **TIN Verified**: LHDN submission features

### **14. Data Security**
- **Multi-tenant Isolation**: All data scoped by company
- **Input Validation**: Comprehensive validation on all inputs
- **SQL Injection Protection**: Eloquent ORM with parameterized queries
- **XSS Protection**: Blade templating with automatic escaping
- **Token Security**: Secure storage and management of LHDN tokens

---

## 📈 **Analytics & Reporting**

### **15. Dashboard Analytics**
- **Invoice Statistics**: Total invoices, revenue, recent activity
- **Customer Statistics**: Total customers, recent additions
- **LHDN Statistics**: Submission success rate, error rates
- **Usage Statistics**: Feature usage, API usage

### **16. Activity Tracking**
- **User Actions**: All user actions logged with timestamps
- **Company Scoping**: Activity logs scoped by company
- **API Calls**: LHDN API calls logged separately
- **Error Tracking**: Comprehensive error logging and tracking

---

## 🎯 **User Experience Optimization**

### **17. Responsive Design**
- **Mobile First**: Optimized for mobile devices
- **Tablet Support**: Full tablet functionality
- **Desktop Optimization**: Enhanced desktop experience
- **Cross-browser**: Compatible with all modern browsers

### **18. Performance Optimization**
- **Fast Loading**: Optimized page load times
- **Efficient Queries**: Optimized database queries
- **Caching**: Strategic caching for performance
- **CDN Integration**: Content delivery network for static assets

### **19. Accessibility**
- **WCAG Compliance**: Web Content Accessibility Guidelines
- **Keyboard Navigation**: Full keyboard navigation support
- **Screen Reader**: Screen reader compatibility
- **Color Contrast**: Proper color contrast ratios

---

## 🔮 **Future Enhancements**

### **20. Planned User Experience Improvements**
- **Mobile App**: Native mobile application
- **Advanced Reporting**: Detailed analytics and reports
- **Bulk Operations**: Enhanced bulk import/export
- **API Access**: RESTful API for third-party integrations
- **Payment Integration**: Direct payment processing
- **Recurring Invoices**: Automated recurring invoice generation

### **21. Advanced Features**
- **Multi-currency Support**: Support for multiple currencies
- **Advanced PDF Templates**: More template options
- **Workflow Automation**: Automated invoice workflows
- **Integration Hub**: Third-party service integrations
- **Advanced Analytics**: Business intelligence features

---

*This user flow documentation provides a comprehensive guide to the complete user journey through the LHDN Middleware SaaS platform, ensuring users understand each step and the system's capabilities.*



