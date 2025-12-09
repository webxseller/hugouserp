<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\Contracts\AuthServiceInterface::class, \App\Services\AuthService::class);
        $this->app->singleton(\App\Services\Contracts\BranchServiceInterface::class, \App\Services\BranchService::class);
        $this->app->singleton(\App\Services\Contracts\ModuleServiceInterface::class, \App\Services\ModuleService::class);
        $this->app->singleton(\App\Services\Contracts\FieldSchemaServiceInterface::class, \App\Services\FieldSchemaService::class);
        $this->app->singleton(\App\Services\Contracts\ProductServiceInterface::class, \App\Services\ProductService::class);
        $this->app->singleton(\App\Services\Contracts\InventoryServiceInterface::class, \App\Services\InventoryService::class);
        $this->app->singleton(\App\Services\Contracts\PurchaseServiceInterface::class, \App\Services\PurchaseService::class);
        $this->app->singleton(\App\Services\Contracts\SaleServiceInterface::class, \App\Services\SaleService::class);
        $this->app->singleton(\App\Services\Contracts\POSServiceInterface::class, \App\Services\POSService::class);
        $this->app->singleton(\App\Services\Contracts\RentalServiceInterface::class, \App\Services\RentalService::class);
        $this->app->singleton(\App\Services\Contracts\MotorcycleServiceInterface::class, \App\Services\MotorcycleService::class);
        $this->app->singleton(\App\Services\Contracts\SparesServiceInterface::class, \App\Services\SparePartsService::class);
        $this->app->singleton(\App\Services\Contracts\WoodServiceInterface::class, \App\Services\WoodService::class);
        $this->app->singleton(\App\Services\Contracts\HRMServiceInterface::class, \App\Services\HRMService::class);
        $this->app->singleton(\App\Services\Contracts\PricingServiceInterface::class, \App\Services\PricingService::class);
        $this->app->singleton(\App\Services\Contracts\DiscountServiceInterface::class, \App\Services\DiscountService::class);
        $this->app->singleton(\App\Services\Contracts\TaxServiceInterface::class, \App\Services\TaxService::class);
        $this->app->singleton(\App\Services\Contracts\ReportServiceInterface::class, \App\Services\ReportService::class);
        $this->app->singleton(\App\Services\Contracts\NotificationServiceInterface::class, \App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\Contracts\PrintingServiceInterface::class, \App\Services\PrintingService::class);
        $this->app->singleton(\App\Services\Contracts\BarcodeServiceInterface::class, \App\Services\BarcodeService::class);
        $this->app->singleton(\App\Services\Contracts\QRServiceInterface::class, \App\Services\QRService::class);
        $this->app->singleton(\App\Services\Contracts\BackupServiceInterface::class, \App\Services\BackupService::class);
        $this->app->singleton(\App\Services\Contracts\UserServiceInterface::class, \App\Services\UserService::class);

        // Repository bindings
        $this->app->singleton(\App\Repositories\Contracts\CustomerRepositoryInterface::class, \App\Repositories\CustomerRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\SupplierRepositoryInterface::class, \App\Repositories\SupplierRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\WarehouseRepositoryInterface::class, \App\Repositories\WarehouseRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\ProductRepositoryInterface::class, \App\Repositories\ProductRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\StockMovementRepositoryInterface::class, \App\Repositories\StockMovementRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\StockLevelRepositoryInterface::class, \App\Repositories\StockLevelRepository::class);

        // HRM
        $this->app->singleton(\App\Repositories\Contracts\HREmployeeRepositoryInterface::class, \App\Repositories\HREmployeeRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\PayrollRepositoryInterface::class, \App\Repositories\PayrollRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\LeaveRequestRepositoryInterface::class, \App\Repositories\LeaveRequestRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\AttendanceRepositoryInterface::class, \App\Repositories\AttendanceRepository::class);

        // Rental
        $this->app->singleton(\App\Repositories\Contracts\PropertyRepositoryInterface::class, \App\Repositories\PropertyRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\RentalUnitRepositoryInterface::class, \App\Repositories\RentalUnitRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\TenantRepositoryInterface::class, \App\Repositories\TenantRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\RentalContractRepositoryInterface::class, \App\Repositories\RentalContractRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\RentalInvoiceRepositoryInterface::class, \App\Repositories\RentalInvoiceRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\RentalPaymentRepositoryInterface::class, \App\Repositories\RentalPaymentRepository::class);

        // Sales / Purchases / Store / POS
        $this->app->singleton(\App\Repositories\Contracts\SaleRepositoryInterface::class, \App\Repositories\SaleRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\SaleItemRepositoryInterface::class, \App\Repositories\SaleItemRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\PurchaseRepositoryInterface::class, \App\Repositories\PurchaseRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\PurchaseItemRepositoryInterface::class, \App\Repositories\PurchaseItemRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\StoreOrderRepositoryInterface::class, \App\Repositories\StoreOrderRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\ReceiptRepositoryInterface::class, \App\Repositories\ReceiptRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\ReturnNoteRepositoryInterface::class, \App\Repositories\ReturnNoteRepository::class);

        // Motorcycle
        $this->app->singleton(\App\Repositories\Contracts\VehicleRepositoryInterface::class, \App\Repositories\VehicleRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\VehicleContractRepositoryInterface::class, \App\Repositories\VehicleContractRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\WarrantyRepositoryInterface::class, \App\Repositories\WarrantyRepository::class);

        // Admin / Core
        $this->app->singleton(\App\Repositories\Contracts\UserRepositoryInterface::class, \App\Repositories\UserRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\BranchRepositoryInterface::class, \App\Repositories\BranchRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\RoleRepositoryInterface::class, \App\Repositories\RoleRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\PermissionRepositoryInterface::class, \App\Repositories\PermissionRepository::class);
        $this->app->singleton(\App\Repositories\Contracts\ModuleRepositoryInterface::class, \App\Repositories\ModuleRepository::class);

        $this->app->tag([
            \App\Services\ReportService::class,
        ], 'reporting');
    }

    public function boot(): void
    {
        //
    }
}
