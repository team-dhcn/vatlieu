<?php
namespace Controllers;

use Core\Database;
use PDO;
use Exception;

class ManufacturingController extends BaseController {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getProductionOrders() {
        $stmt = $this->pdo->query("SELECT ls.*, sp.Tensp FROM Lenhsanxuat ls
            LEFT JOIN Sanpham sp ON ls.Masp = sp.Masp
            ORDER BY ls.Ngaysanxuat DESC");
        $this->jsonResponse(true, 'Production orders retrieved', ['orders' => $stmt->fetchAll()]);
    }

    public function getOrderDetails($params) {
        $id = $params['id'];
        $stmt = $this->pdo->prepare("SELECT ls.*, sp.Tensp FROM Lenhsanxuat ls
            LEFT JOIN Sanpham sp ON ls.Masp = sp.Masp
            WHERE ls.Malenh = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) $this->jsonResponse(false, 'Order not found', null, 404);

        $stmtNvl = $this->pdo->prepare("SELECT ctx.Manvl, ctx.Soluong, nvl.Tennvl, nvl.Dvt 
            FROM Chitiet_XuatNVL_Sanxuat ctx
            JOIN Nguyenvatlieu nvl ON ctx.Manvl = nvl.Manvl
            WHERE ctx.Malenh = ?");
        $stmtNvl->execute([$id]);
        $consumed = $stmtNvl->fetchAll();

        $stmtSp = $this->pdo->prepare("SELECT ctn.Masp, ctn.Soluong, ctn.Makho, k.Tenkho, sp.Tensp, sp.Dvt
            FROM Chitiet_Nhapsanpham_Sanxuat ctn
            JOIN Sanpham sp ON ctn.Masp = sp.Masp
            JOIN Kho k ON ctn.Makho = k.Makho
            WHERE ctn.Malenh = ?");
        $stmtSp->execute([$id]);
        $produced = $stmtSp->fetchAll();

        $this->jsonResponse(true, 'Order details retrieved', [
            'order' => $order,
            'consumed' => $consumed,
            'produced' => $produced
        ]);
    }

    public function createProductionOrder() {
        $body = $this->getBody();
        $malenh = $body['Malenh'] ?? ('LSX' . time());
        $masp   = $body['Masp'];
        $slsx   = (float)$body['Soluongsanxuat'];

        $stmtRecipe = $this->pdo->prepare("SELECT c.Manvl, c.Soluong as DinhMuc, nvl.Tennvl, 
                                    IFNULL(SUM(tk.Soluongton), 0) as TonKho
                                    FROM Congthucsanpham c
                                    JOIN Nguyenvatlieu nvl ON c.Manvl = nvl.Manvl
                                    LEFT JOIN Tonkho_nvl tk ON c.Manvl = tk.Manvl
                                    WHERE c.Masp = ?
                                    GROUP BY c.Manvl");
        $stmtRecipe->execute([$masp]);
        $recipeItems = $stmtRecipe->fetchAll();

        $missingItems = [];
        foreach ($recipeItems as $item) {
            $required = $item['DinhMuc'] * $slsx;
            if ($item['TonKho'] < $required) {
                $missingItems[] = [
                    'Manvl' => $item['Manvl'], 'Tennvl' => $item['Tennvl'],
                    'Required' => $required, 'Available' => (float)$item['TonKho'],
                    'Missing' => $required - $item['TonKho']
                ];
            }
        }

        if (!empty($missingItems)) {
            $this->jsonResponse(false, 'Không đủ nguyên vật liệu', ['missing' => $missingItems], 400);
        }

        $stmt = $this->pdo->prepare("INSERT INTO Lenhsanxuat (Malenh, Masp, Ngaysanxuat, Soluongsanxuat, Trangthai, Ghichu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$malenh, $masp, $body['Ngaysanxuat'] ?? date('Y-m-d'), $slsx, 'cho_xu_ly', $body['Ghichu'] ?? null]);
        $this->jsonResponse(true, 'Production order created', ['id' => $malenh], 201);
    }

    public function completeProduction() {
        $body  = $this->getBody();
        $malenh = $body['Malenh'];
        $makho  = $body['Makho'];

        $stmt = $this->pdo->prepare("SELECT * FROM Lenhsanxuat WHERE Malenh = ?");
        $stmt->execute([$malenh]);
        $order = $stmt->fetch();
        if (!$order) $this->jsonResponse(false, 'Lệnh không tìm thấy', null, 404);
        if ($order['Trangthai'] === 'hoan_thanh') $this->jsonResponse(false, 'Đã hoàn thành trước đó', null, 400);

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("UPDATE Lenhsanxuat SET Trangthai='hoan_thanh', Ngayketthuc=? WHERE Malenh=?")
                ->execute([date('Y-m-d'), $malenh]);

            $stmtRecipe = $this->pdo->prepare("SELECT Manvl, Soluong FROM Congthucsanpham WHERE Masp = ?");
            $stmtRecipe->execute([$order['Masp']]);
            foreach ($stmtRecipe->fetchAll() as $item) {
                $req = $item['Soluong'] * $order['Soluongsanxuat'];
                $this->pdo->prepare("INSERT INTO Chitiet_XuatNVL_Sanxuat (Malenh, Manvl, Soluong) VALUES (?, ?, ?)")->execute([$malenh, $item['Manvl'], $req]);
                $this->pdo->prepare("UPDATE Tonkho_nvl SET Soluongton = Soluongton - ? WHERE Manvl = ?")->execute([$req, $item['Manvl']]);
            }

            $sl = $order['Soluongsanxuat'];
            $chk = $this->pdo->prepare("SELECT 1 FROM Tonkho_sp WHERE Makho = ? AND Masp = ?");
            $chk->execute([$makho, $order['Masp']]);
            if ($chk->fetchColumn()) {
                $this->pdo->prepare("UPDATE Tonkho_sp SET Soluongton = Soluongton + ? WHERE Makho = ? AND Masp = ?")
                    ->execute([$sl, $makho, $order['Masp']]);
            } else {
                $this->pdo->prepare("INSERT INTO Tonkho_sp (Makho, Masp, Soluongton) VALUES (?, ?, ?)")
                    ->execute([$makho, $order['Masp'], $sl]);
            }

            $this->pdo->prepare("INSERT INTO Chitiet_Nhapsanpham_Sanxuat (Malenh, Makho, Masp, Soluong) VALUES (?, ?, ?, ?)")->execute([$malenh, $makho, $order['Masp'], $sl]);
            $this->pdo->commit();
            $this->jsonResponse(true, 'Sản xuất hoàn thành');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi: ' . $e->getMessage(), null, 400);
        }
    }

    public function updateOrder($params) {
        $id = $params['id'];
        $body = $this->getBody();
        
        $fields = [];
        $args = [];
        
        // Danh sách các trường cho phép cập nhật
        $allowedFields = ['Masp', 'Ngaysanxuat', 'Soluongsanxuat', 'Trangthai', 'Ngaybatdau', 'Ngayketthuc', 'Ghichu'];
        
        foreach ($allowedFields as $field) {
            if (isset($body[$field])) {
                $fields[] = "$field = ?";
                $args[] = $body[$field];
                
                // Logic bổ trợ: Nếu chuyển sang đang sản xuất mà chưa có ngày bắt đầu thì tự điền
                if ($field === 'Trangthai' && $body[$field] === 'dang_san_xuat' && !isset($body['Ngaybatdau'])) {
                    $fields[] = "Ngaybatdau = ?";
                    $args[] = date('Y-m-d');
                }
            }
        }
        
        if (empty($fields)) {
            $this->jsonResponse(false, 'No data to update');
        }
        
        $sql = "UPDATE Lenhsanxuat SET " . implode(', ', $fields) . " WHERE Malenh = ?";
        $args[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($args)) {
            $this->jsonResponse(true, 'Order updated successfully');
        } else {
            $this->jsonResponse(false, 'Failed to update order');
        }
    }

    public function deleteOrder($params) {
        $id = $params['id'];
        $role = $this->getUserRole();
        
        // Nếu là admin thì được xóa mọi trạng thái, nếu không phải admin thì không được xóa lệnh đã hoàn thành
        $sql = "DELETE FROM Lenhsanxuat WHERE Malenh = ?";
        $args = [$id];
        
        if ($role !== 'admin') {
            $sql .= " AND Trangthai != 'hoan_thanh'";
        }
        
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($args)) {
            if ($stmt->rowCount() > 0) {
                $this->jsonResponse(true, 'Order deleted');
            } else {
                $msg = ($role === 'admin') ? 'Order not found' : 'Cannot delete completed order (Admin only)';
                $this->jsonResponse(false, $msg);
            }
        } else {
            $this->jsonResponse(false, 'Failed to delete order');
        }
    }
}


