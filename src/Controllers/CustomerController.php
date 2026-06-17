<?php
namespace Controllers;

use Models\CustomerType;
use Models\Customer;

class CustomerController extends BaseController {
    
    // CUSTOMER TYPES
    public function getCustomerTypes() {
        $model = new CustomerType();
        $this->jsonResponse(true, 'Customer types retrieved', ['types' => $model->getAll('Maloaikh')]);
    }

    public function getCustomerType($params) {
        $model = new CustomerType();
        $row = $model->getById($params['id']);
        if ($row) {
            $this->jsonResponse(true, 'Type found', $row);
        } else {
            $this->jsonResponse(false, 'Type not found', null, 404);
        }
    }

    public function createCustomerType() {
        $body = $this->getBody();
        if (empty($body['Tenloaikh'])) {
            $this->jsonResponse(false, 'Tenloaikh is required', null, 400);
        }
        $model = new CustomerType();
        if ($model->create($body)) {
            $this->jsonResponse(true, 'Customer type created', ['id' => $model->getLastInsertId()], 201);
        } else {
            $this->jsonResponse(false, 'Failed to create customer type', null, 500);
        }
    }

    public function updateCustomerType($params) {
        $body = $this->getBody();
        if (empty($body['Tenloaikh'])) {
            $this->jsonResponse(false, 'Tenloaikh is required', null, 400);
        }
        $model = new CustomerType();
        if ($model->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Customer type updated');
        } else {
            $this->jsonResponse(false, 'Failed to update customer type', null, 500);
        }
    }

    public function deleteCustomerType($params) {
        $model = new CustomerType();
        if ($model->delete($params['id'])) {
            $this->jsonResponse(true, 'Customer type deleted');
        } else {
            $this->jsonResponse(false, 'Failed to delete customer type', null, 500);
        }
    }

    // CUSTOMERS
    public function getCustomers() {
        $model = new Customer();
        $this->jsonResponse(true, 'Customers retrieved', ['customers' => $model->getAllWithTypes()]);
    }

    public function getCustomer($params) {
        $model = new Customer();
        $row = $model->getByIdWithType($params['id']);
        if ($row) {
            $this->jsonResponse(true, 'Customer found', $row);
        } else {
            $this->jsonResponse(false, 'Customer not found', null, 404);
        }
    }

    public function createCustomer() {
        $body = $this->getBody();
        if (empty($body['Makh']) || empty($body['Tenkh'])) {
            $this->jsonResponse(false, 'Makh and Tenkh are required', null, 400);
        }
        
        try {
            $model = new Customer();
            if ($model->create($body)) {
                $this->jsonResponse(true, 'Customer created', ['id' => $body['Makh']], 201);
            } else {
                $this->jsonResponse(false, 'Failed to create customer', null, 500);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                $this->jsonResponse(false, "Mã khách hàng '{$body['Makh']}' đã tồn tại.", null, 409);
            } else {
                $this->jsonResponse(false, 'Lỗi cơ sở dữ liệu: ' . $e->getMessage(), null, 500);
            }
        }
    }

    public function updateCustomer($params) {
        $body = $this->getBody();
        if (empty($body['Tenkh'])) {
            $this->jsonResponse(false, 'Tenkh is required', null, 400);
        }
        $model = new Customer();
        if ($model->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Customer updated');
        } else {
            $this->jsonResponse(false, 'Failed to update customer', null, 500);
        }
    }

    public function deleteCustomer($params) {
        $model = new Customer();
        if ($model->delete($params['id'])) {
            $this->jsonResponse(true, 'Customer deleted');
        } else {
            $this->jsonResponse(false, 'Failed to delete customer', null, 500);
        }
    }
}
