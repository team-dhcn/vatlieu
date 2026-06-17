<?php
/**
 * Đăng ký tất cả route API (kiến trúc monolith)
 */
use Core\Router;
use Controllers\UserController;
use Controllers\ProductController;
use Controllers\WarehouseController;
use Controllers\CustomerController;
use Controllers\ManufacturingController;

function registerApiRoutes(Router $router): void {
    // Auth & users
    $router->add('POST',   '/auth/login', [UserController::class, 'login']);
    $router->add('GET',    '/users',      [UserController::class, 'getUsers']);
    $router->add('GET',    '/users/{id}', [UserController::class, 'getUser']);
    $router->add('POST',   '/users',      [UserController::class, 'createUser']);
    $router->add('PUT',    '/users/{id}', [UserController::class, 'updateUser']);
    $router->add('DELETE', '/users/{id}', [UserController::class, 'deleteUser']);

    // Products
    $router->add('GET',    '/products',      [ProductController::class, 'getProducts']);
    $router->add('GET',    '/products/{id}', [ProductController::class, 'getProduct']);
    $router->add('POST',   '/products',      [ProductController::class, 'createProduct']);
    $router->add('PUT',    '/products/{id}', [ProductController::class, 'updateProduct']);
    $router->add('DELETE', '/products/{id}', [ProductController::class, 'deleteProduct']);

    // Categories
    $router->add('GET',    '/categories',      [ProductController::class, 'getCategories']);
    $router->add('GET',    '/categories/{id}', [ProductController::class, 'getCategory']);
    $router->add('POST',   '/categories',      [ProductController::class, 'createCategory']);
    $router->add('PUT',    '/categories/{id}', [ProductController::class, 'updateCategory']);
    $router->add('DELETE', '/categories/{id}', [ProductController::class, 'deleteCategory']);

    // Materials
    $router->add('GET',    '/materials',      [ProductController::class, 'getMaterials']);
    $router->add('GET',    '/materials/{id}', [ProductController::class, 'getMaterial']);
    $router->add('POST',   '/materials',      [ProductController::class, 'createMaterial']);
    $router->add('PUT',    '/materials/{id}', [ProductController::class, 'updateMaterial']);
    $router->add('DELETE', '/materials/{id}', [ProductController::class, 'deleteMaterial']);

    // Suppliers
    $router->add('GET',    '/suppliers',      [ProductController::class, 'getSuppliers']);
    $router->add('GET',    '/suppliers/{id}', [ProductController::class, 'getSupplier']);
    $router->add('POST',   '/suppliers',      [ProductController::class, 'createSupplier']);
    $router->add('PUT',    '/suppliers/{id}', [ProductController::class, 'updateSupplier']);
    $router->add('DELETE', '/suppliers/{id}', [ProductController::class, 'deleteSupplier']);

    // Formulas
    $router->add('GET',    '/formulas',      [ProductController::class, 'getFormulas']);
    $router->add('POST',   '/formulas',      [ProductController::class, 'createFormula']);
    $router->add('DELETE', '/formulas/{id}', [ProductController::class, 'deleteFormula']);

    // Warehouses & inventory
    $router->add('GET',  '/warehouses', [WarehouseController::class, 'getWarehouses']);
    $router->add('POST', '/warehouses', [WarehouseController::class, 'createWarehouse']);
    $router->add('GET',  '/inventory',  [WarehouseController::class, 'getInventory']);

    // Import receipts
    $router->add('GET',    '/import-receipts',      [WarehouseController::class, 'getImportReceipts']);
    $router->add('GET',    '/import-receipts/{id}', [WarehouseController::class, 'getImportReceipt']);
    $router->add('POST',   '/import-receipts',      [WarehouseController::class, 'createImportReceipt']);
    $router->add('DELETE', '/import-receipts/{id}', [WarehouseController::class, 'deleteImportReceipt']);

    // Export receipts
    $router->add('GET',  '/export-receipts',      [WarehouseController::class, 'getExportReceipts']);
    $router->add('GET',  '/export-receipts/{id}', [WarehouseController::class, 'getExportReceipt']);
    $router->add('POST', '/export-receipts',      [WarehouseController::class, 'createExportReceipt']);

    // Transfers
    $router->add('GET',  '/transfers',              [WarehouseController::class, 'getTransfers']);
    $router->add('GET',  '/transfers/{id}',         [WarehouseController::class, 'getTransfer']);
    $router->add('POST', '/transfers',              [WarehouseController::class, 'createTransfer']);
    $router->add('POST', '/transfers/{id}/execute', [WarehouseController::class, 'executeTransfer']);

    // Customers
    $router->add('GET',    '/customer-types',      [CustomerController::class, 'getCustomerTypes']);
    $router->add('GET',    '/customer-types/{id}', [CustomerController::class, 'getCustomerType']);
    $router->add('POST',   '/customer-types',      [CustomerController::class, 'createCustomerType']);
    $router->add('PUT',    '/customer-types/{id}', [CustomerController::class, 'updateCustomerType']);
    $router->add('DELETE', '/customer-types/{id}', [CustomerController::class, 'deleteCustomerType']);

    $router->add('GET',    '/customers',      [CustomerController::class, 'getCustomers']);
    $router->add('GET',    '/customers/{id}', [CustomerController::class, 'getCustomer']);
    $router->add('POST',   '/customers',      [CustomerController::class, 'createCustomer']);
    $router->add('PUT',    '/customers/{id}', [CustomerController::class, 'updateCustomer']);
    $router->add('DELETE', '/customers/{id}', [CustomerController::class, 'deleteCustomer']);

    // Manufacturing
    $router->add('GET',    '/production-orders',              [ManufacturingController::class, 'getProductionOrders']);
    $router->add('GET',    '/production-orders/{id}',         [ManufacturingController::class, 'getOrderDetails']);
    $router->add('GET',    '/production-orders/{id}/details', [ManufacturingController::class, 'getOrderDetails']);
    $router->add('POST',   '/production-orders',              [ManufacturingController::class, 'createProductionOrder']);
    $router->add('PUT',    '/production-orders/{id}',         [ManufacturingController::class, 'updateOrder']);
    $router->add('DELETE', '/production-orders/{id}',         [ManufacturingController::class, 'deleteOrder']);
    $router->add('POST',   '/complete-production',            [ManufacturingController::class, 'completeProduction']);
}
