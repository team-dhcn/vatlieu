<?php
namespace Controllers;

use Models\Category;
use Models\Material;
use Models\Product;
use Models\Supplier;
use Models\Formula;

class ProductController extends BaseController {
    private $categoryModel;
    private $materialModel;
    private $productModel;
    private $supplierModel;
    private $formulaModel;

    public function __construct() {
        $this->categoryModel = new Category();
        $this->materialModel = new Material();
        $this->productModel = new Product();
        $this->supplierModel = new Supplier();
        $this->formulaModel = new Formula();
    }

    // --- Product Actions ---
    public function getProducts() {
        $data = $this->productModel->getAllWithCategory();
        $this->jsonResponse(true, 'Products retrieved', ['products' => $data]);
    }

    public function getProduct($params) {
        $data = $this->productModel->getByIdWithCategory($params['id']);
        if ($data) $this->jsonResponse(true, 'Product found', $data);
        else $this->jsonResponse(false, 'Product not found', null, 404);
    }

    public function createProduct() {
        $body = $this->getBody();
        if ($this->productModel->create($body)) {
            $this->jsonResponse(true, 'Product created', null, 201);
        }
        $this->jsonResponse(false, 'Create failed', null, 400);
    }

    public function updateProduct($params) {
        $body = $this->getBody();
        if ($this->productModel->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Product updated');
        }
        $this->jsonResponse(false, 'Update failed', null, 400);
    }

    public function deleteProduct($params) {
        if ($this->productModel->delete($params['id'])) {
            $this->jsonResponse(true, 'Product deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }

    // --- Category Actions ---
    public function getCategories() {
        $data = $this->categoryModel->getAll('Madm');
        $this->jsonResponse(true, 'Categories retrieved', ['categories' => $data]);
    }

    public function getCategory($params) {
        $data = $this->categoryModel->getById($params['id']);
        if ($data) $this->jsonResponse(true, 'Category found', $data);
        else $this->jsonResponse(false, 'Category not found', null, 404);
    }

    public function createCategory() {
        $body = $this->getBody();
        if ($this->categoryModel->create($body)) {
            $this->jsonResponse(true, 'Category created', null, 201);
        }
        $this->jsonResponse(false, 'Create failed', null, 400);
    }

    public function updateCategory($params) {
        $body = $this->getBody();
        if ($this->categoryModel->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Category updated');
        }
        $this->jsonResponse(false, 'Update failed', null, 400);
    }

    public function deleteCategory($params) {
        if ($this->categoryModel->delete($params['id'])) {
            $this->jsonResponse(true, 'Category deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }

    // --- Material Actions ---
    public function getMaterials() {
        $data = $this->materialModel->getAll('Manvl');
        $this->jsonResponse(true, 'Materials retrieved', ['materials' => $data]);
    }

    public function getMaterial($params) {
        $data = $this->materialModel->getById($params['id']);
        if ($data) $this->jsonResponse(true, 'Material found', $data);
        else $this->jsonResponse(false, 'Material not found', null, 404);
    }

    public function createMaterial() {
        $body = $this->getBody();
        if ($this->materialModel->create($body)) {
            $this->jsonResponse(true, 'Material created', null, 201);
        }
        $this->jsonResponse(false, 'Create failed', null, 400);
    }

    public function updateMaterial($params) {
        $body = $this->getBody();
        if ($this->materialModel->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Material updated');
        }
        $this->jsonResponse(false, 'Update failed', null, 400);
    }

    public function deleteMaterial($params) {
        if ($this->materialModel->delete($params['id'])) {
            $this->jsonResponse(true, 'Material deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }

    // --- Supplier Actions ---
    public function getSuppliers() {
        $data = $this->supplierModel->getAll('Mancc');
        $this->jsonResponse(true, 'Suppliers retrieved', ['suppliers' => $data]);
    }

    public function getSupplier($params) {
        $data = $this->supplierModel->getById($params['id']);
        if ($data) $this->jsonResponse(true, 'Supplier found', $data);
        else $this->jsonResponse(false, 'Supplier not found', null, 404);
    }

    public function createSupplier() {
        $body = $this->getBody();
        if ($this->supplierModel->create($body)) {
            $this->jsonResponse(true, 'Supplier created', null, 201);
        }
        $this->jsonResponse(false, 'Create failed', null, 400);
    }

    public function updateSupplier($params) {
        $body = $this->getBody();
        if ($this->supplierModel->update($params['id'], $body)) {
            $this->jsonResponse(true, 'Supplier updated');
        }
        $this->jsonResponse(false, 'Update failed', null, 400);
    }

    public function deleteSupplier($params) {
        if ($this->supplierModel->delete($params['id'])) {
            $this->jsonResponse(true, 'Supplier deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }

    // --- Formula Actions ---
    public function getFormulas() {
        if (isset($_GET['Masp'])) {
            $data = $this->formulaModel->getByProduct($_GET['Masp']);
            $this->jsonResponse(true, 'Formulas retrieved', ['formulas' => $data]);
        }
        $data = $this->formulaModel->getAllDetailed();
        $this->jsonResponse(true, 'All formulas retrieved', ['formulas' => $data]);
    }

    public function createFormula() {
        $body = $this->getBody();
        if ($this->formulaModel->createOrUpdate($body)) {
            $this->jsonResponse(true, 'Formula saved');
        }
        $this->jsonResponse(false, 'Save failed', null, 400);
    }

    public function deleteFormula($params) {
        if ($this->formulaModel->deleteDetailed($params['id'])) {
            $this->jsonResponse(true, 'Formula deleted');
        }
        $this->jsonResponse(false, 'Delete failed', null, 400);
    }
}
