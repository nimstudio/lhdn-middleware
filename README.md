# LHDN Middleware SaaS

A comprehensive Laravel 11-based SaaS application that enables Malaysian companies to manage invoices and submit them to LHDN (Malaysian Inland Revenue Board) through the MyInvois system.

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Node.js 18+
- Composer 2.0+

### **Installation**
```bash
# Clone repository
git clone https://github.com/your-repo/lhdn-middleware.git
cd lhdn-middleware

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## 📚 **Documentation**

### **Complete Documentation**
- **[System Overview](docs/system-overview.md)** - Comprehensive system capabilities and features
- **[Technical Architecture](docs/technical-architecture.md)** - Technical implementation details
- **[User Flow](docs/user-flow.md)** - Complete user journey and workflows
- **[API Documentation](docs/api-documentation.md)** - Service APIs and LHDN integration
- **[Deployment Guide](docs/deployment-guide.md)** - Production deployment instructions

### **Quick Reference**
- **[System Overview](docs/system-overview.md#-whats-implemented)** - Current features
- **[User Journey](docs/user-flow.md#-complete-user-journey)** - User workflow
- **[API Reference](docs/api-documentation.md#-api-reference-summary)** - Service methods

## 🏢 **System Overview**

### **Core Features**
- **Multi-tenant SaaS Architecture** - Secure company data isolation
- **Invoice Management** - Complete invoice lifecycle management
- **Customer Management** - Customer database with search and filtering
- **PDF Generation** - Professional invoice PDFs with company branding
- **LHDN Integration** - Direct submission to LHDN MyInvois system
- **TIN Validation** - Automatic TIN validation with LHDN
- **Subscription Management** - Plan-based access control
- **Settings & Customization** - Comprehensive configuration options

### **User Journey**
1. **Registration** → Plan selection → Admin approval
2. **Company Setup** → Mandatory company profile creation
3. **LHDN Credentials** → Optional MyInvois API integration
4. **Core Features** → Invoice and customer management
5. **LHDN Submission** → Direct invoice submission to LHDN

### **Security Features**
- **Multi-tenant Data Isolation** - Company-scoped data access
- **TIN Uniqueness Validation** - Prevents duplicate TIN numbers
- **Comprehensive Access Control** - Subscription, company, and TIN validation guards
- **Secure Token Management** - LHDN API token handling
- **Activity Logging** - Complete audit trail

## 🛠️ **Technology Stack**

### **Backend**
- **Laravel 11** - PHP framework
- **MySQL 8.0+** - Database
- **Redis** - Caching and queues
- **Spatie Packages** - Media library, activity log, permissions

### **Frontend**
- **Blade + TailwindCSS** - User interface
- **Alpine.js** - Interactive components
- **Vite** - Asset compilation

### **Integrations**
- **MyInvois SDK** - LHDN API integration
- **DomPDF** - PDF generation
- **Filament 3** - Admin panel

## 🔧 **Development**

### **Local Development**
```bash
# Start development servers
npm run dev          # Vite dev server
php artisan serve    # Laravel dev server
```

### **Testing**
```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=TinValidationTest
```

### **Code Quality**
```bash
# Code formatting
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

## 📊 **System Architecture**

```
┌─────────────────────────────────────────────────────────────┐
│                     USER INTERFACE                          │
├─────────────────────────────────────────────────────────────┤
│  Landing Pages        │  User App (/app)   │  Admin Panel   │
│  (Blade + Tailwind)   │  (Custom Blade)    │  (/admin)      │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  - Authentication (Laravel Sanctum)                         │
│  - Email Verification                                        │
│  - Subscription Guard (subscription.paid)                   │
│  - Company Guard (company.required)                         │
│  - TIN Validation Guard (tin.verified)                      │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     BUSINESS LOGIC                          │
├─────────────────────────────────────────────────────────────┤
│  - Invoice Management Service                                │
│  - TIN Validation Service                                    │
│  - MyInvois SDK Service                                      │
│  - PDF Generation Service                                    │
└─────────────────────────────────────────────────────────────┘
```

## 🔐 **Security & Compliance**

### **Data Protection**
- **Multi-tenant Isolation** - All data scoped by company
- **TIN Uniqueness** - Prevents duplicate TIN numbers across companies
- **Secure API Integration** - Encrypted LHDN API communication
- **Activity Logging** - Complete audit trail for compliance

### **Access Control**
- **Subscription-based Access** - Plan-based feature access
- **Company Validation** - Mandatory company profile
- **TIN Verification** - LHDN TIN validation enforcement
- **Role-based Permissions** - Granular access control

## 🚀 **Deployment**

### **Production Deployment**
See [Deployment Guide](docs/deployment-guide.md) for complete production deployment instructions.

### **Environment Requirements**
- **Server**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ with InnoDB
- **Cache**: Redis 6.0+
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **SSL**: Let's Encrypt or commercial SSL

### **Quick Deployment**
```bash
# Production deployment
./deploy.sh

# Or manual deployment
git pull origin main
composer install --optimize-autoloader --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
```

## 📈 **Monitoring & Maintenance**

### **Logging**
- **Application Logs** - Laravel application logs
- **MyInvois Logs** - LHDN API interaction logs
- **Activity Logs** - User action audit trail
- **Error Tracking** - Comprehensive error logging

### **Performance**
- **Caching** - Redis caching for performance
- **Queue Processing** - Background job processing
- **Database Optimization** - Optimized queries and indexes
- **CDN Integration** - Content delivery network

## 🔮 **Roadmap**

### **Planned Features**
- **API Access** - RESTful API for third-party integrations
- **Advanced Reporting** - Detailed analytics and reports
- **Multi-currency Support** - Support for multiple currencies
- **Recurring Invoices** - Automated recurring invoice generation
- **Payment Integration** - Direct payment processing
- **Mobile App** - Native mobile application

### **Technical Improvements**
- **Microservices** - Service decomposition for scalability
- **Event Sourcing** - Event-driven architecture
- **Advanced Caching** - Distributed caching strategy
- **Real-time Updates** - WebSocket integration

## 🤝 **Contributing**

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

### **Code Standards**
- Follow PSR-12 coding standards
- Write comprehensive tests
- Document new features
- Update documentation

## 📞 **Support**

### **Documentation**
- **[System Overview](docs/system-overview.md)** - Complete system documentation
- **[User Flow](docs/user-flow.md)** - User journey and workflows
- **[API Documentation](docs/api-documentation.md)** - Service APIs
- **[Deployment Guide](docs/deployment-guide.md)** - Production deployment

### **Issues & Support**
- **GitHub Issues** - Bug reports and feature requests
- **Documentation** - Comprehensive documentation
- **Community** - Developer community support

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- **Laravel Framework** - PHP web framework
- **MyInvois SDK** - LHDN API integration
- **Spatie Packages** - Laravel packages
- **TailwindCSS** - CSS framework
- **Filament** - Admin panel framework

---

**LHDN Middleware SaaS** - Empowering Malaysian businesses with seamless invoice management and LHDN integration.

*For detailed information, please refer to the comprehensive documentation in the `docs/` directory.*
