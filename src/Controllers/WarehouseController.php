<?php
namespace Controllers;

use Core\Database;
use PDO;
use Exception;

class WarehouseController extends BaseController {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    // ===== WAREHOUSES =====
    public function getWarehouses() {
        $stmt = $this->pdo->query("SELECT Makho, Tenkho, Diachi FROM Kho ORDER BY Makho");
        $this->jsonResponse(true, 'Warehouses retrieved', ['warehouses' => $stmt->fetchAll()]);
    }

    public function createWarehouse() {
        $body = $this->getBody();
        $stmt = $this->pdo->prepare("INSERT INTO Kho (Makho, Tenkho, Diachi) VALUES (?, ?, ?)");
        $stmt->execute([$body['Makho'], $body['Tenkho'], $body['Diachi'] ?? '']);
        $this->jsonResponse(true, 'Warehouse created', ['id' => $body['Makho']], 201);
    }

    // ===== INVENTORY =====
    public function getInventory() {
        // Tồn kho sản phẩm
        $sp = $this->pdo->query("SELECT tk.Makho, k.Tenkho, tk.Masp, sp.Tensp, sp.Dvt, tk.Soluongton
            FROM Tonkho_sp tk
            JOIN Kho k ON tk.Makho = k.Makho
            JOIN Sanpham sp ON tk.Masp = sp.Masp
            ORDER BY k.Tenkho, sp.Tensp")->fetchAll();
        // Tồn kho NVL
        $nvl = $this->pdo->query("SELECT tk.Makho, k.Tenkho, tk.Manvl, nvl.Tennvl, nvl.Dvt, tk.Soluongton
            FROM Tonkho_nvl tk
            JOIN Kho k ON tk.Makho = k.Makho
            JOIN Nguyenvatlieu nvl ON tk.Manvl = nvl.Manvl
            ORDER BY k.Tenkho, nvl.Tennvl")->fetchAll();
        $this->jsonResponse(true, 'Inventory retrieved', ['products' => $sp, 'materials' => $nvl]);
    }

    // ===== IMPORT RECEIPTS =====
    public function getImportReceipts() {
        $stmt = $this->pdo->query("SELECT pn.*, ncc.Tenncc, k.Tenkho,
            (SELECT COUNT(*) FROM Chitiet_Phieunhap WHERE Manhaphang = pn.Manhaphang) as SoMatHang
            FROM Phieunhap pn
            LEFT JOIN Nhacungcap ncc ON pn.Mancc = ncc.Mancc
            LEFT JOIN Kho k ON pn.Makho = k.Makho
            ORDER BY pn.Ngaynhaphang DESC");
        $this->jsonResponse(true, 'Import receipts retrieved', ['receipts' => $stmt->fetchAll()]);
    }

    public function getImportReceipt($params) {
        $id = $params['id'];
        $stmt = $this->pdo->prepare("SELECT pn.*, ncc.Tenncc, k.Tenkho FROM Phieunhap pn
            LEFT JOIN Nhacungcap ncc ON pn.Mancc = ncc.Mancc
            LEFT JOIN Kho k ON pn.Makho = k.Makho
            WHERE pn.Manhaphang = ?");
        $stmt->execute([$id]);
        $receipt = $stmt->fetch();
        if (!$receipt) $this->jsonResponse(false, 'Receipt not found', null, 404);

        $stmtD = $this->pdo->prepare("SELECT ct.*, nvl.Tennvl, nvl.Dvt FROM Chitiet_Phieunhap ct
            LEFT JOIN Nguyenvatlieu nvl ON ct.Manvl = nvl.Manvl
            WHERE ct.Manhaphang = ?");
        $stmtD->execute([$id]);
        $receipt['details'] = $stmtD->fetchAll();
        $this->jsonResponse(true, 'Receipt details retrieved', ['receipt' => $receipt]);
    }

    public function createImportReceipt() {
        $body = $this->getBody();
        $maPN   = $body['Manhaphang'] ?? ('PN' . time());
        $mancc  = $body['Mancc'];
        $makho  = $body['Makho'];
        $ngay   = $body['Ngaynhaphang'] ?? date('Y-m-d');
        $ghichu = $body['Ghichu'] ?? '';
        $details = $body['details'] ?? [];

        if (empty($details)) $this->jsonResponse(false, 'Vui lòng thêm chi tiết phiếu nhập', null, 400);

        $this->pdo->beginTransaction();
        try {
            $tongTien = 0;
            foreach ($details as $d) $tongTien += ($d['Soluong'] ?? 0) * ($d['Dongianhap'] ?? 0);
            
            $stmt = $this->pdo->prepare("INSERT INTO Phieunhap (Manhaphang, Mancc, Makho, Ngaynhaphang, Tongtiennhap, Ghichu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$maPN, $mancc, $makho, $ngay, $tongTien, $ghichu]);

            $stmtDet = $this->pdo->prepare("INSERT INTO Chitiet_Phieunhap (Manhaphang, Manvl, Soluong, Dongianhap) VALUES (?, ?, ?, ?)");
            $stmtChk = $this->pdo->prepare("SELECT 1 FROM Tonkho_nvl WHERE Makho = ? AND Manvl = ?");
            $stmtUpd = $this->pdo->prepare("UPDATE Tonkho_nvl SET Soluongton = Soluongton + ? WHERE Makho = ? AND Manvl = ?");
            $stmtIns = $this->pdo->prepare("INSERT INTO Tonkho_nvl (Makho, Manvl, Soluongton) VALUES (?, ?, ?)");

            foreach ($details as $d) {
                $stmtDet->execute([$maPN, $d['Manvl'], $d['Soluong'], $d['Dongianhap'] ?? 0]);
                $stmtChk->execute([$makho, $d['Manvl']]);
                if ($stmtChk->fetchColumn()) {
                    $stmtUpd->execute([$d['Soluong'], $makho, $d['Manvl']]);
                } else {
                    $stmtIns->execute([$makho, $d['Manvl'], $d['Soluong']]);
                }
            }
            $this->pdo->commit();
            $this->jsonResponse(true, 'Import receipt created', ['id' => $maPN], 201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi tạo phiếu nhập: ' . $e->getMessage(), null, 400);
        }
    }

    public function deleteImportReceipt($params) {
        $id = $params['id'];
        $this->pdo->beginTransaction();
        try {
            $phieu = $this->pdo->prepare("SELECT Makho FROM Phieunhap WHERE Manhaphang = ?");
            $phieu->execute([$id]);
            $phieuRow = $phieu->fetch();
            if ($phieuRow) {
                $ct = $this->pdo->prepare("SELECT Manvl, Soluong FROM Chitiet_Phieunhap WHERE Manhaphang = ?");
                $ct->execute([$id]);
                foreach ($ct->fetchAll() as $row) {
                    $this->pdo->prepare("UPDATE Tonkho_nvl SET Soluongton = GREATEST(0, Soluongton - ?) WHERE Makho = ? AND Manvl = ?")
                        ->execute([$row['Soluong'], $phieuRow['Makho'], $row['Manvl']]);
                }
            }
            $this->pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM Phieunhap WHERE Manhaphang = ?")->execute([$id]);
            $this->pdo->commit();
            $this->jsonResponse(true, 'Import receipt deleted');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi xóa phiếu: ' . $e->getMessage(), null, 400);
        }
    }

    // ===== EXPORT RECEIPTS =====
    public function getExportReceipts() {
        $stmt = $this->pdo->query("SELECT px.*, kh.Tenkh, k.Tenkho,
            (SELECT COUNT(*) FROM Chitiet_Phieuxuat WHERE Maxuathang = px.Maxuathang) as SoMatHang
            FROM Phieuxuat px
            LEFT JOIN Khachhang kh ON px.Makh = kh.Makh
            LEFT JOIN Kho k ON px.Makho = k.Makho
            ORDER BY px.Ngayxuat DESC");
        $this->jsonResponse(true, 'Export receipts retrieved', ['receipts' => $stmt->fetchAll()]);
    }

    public function getExportReceipt($params) {
        $id = $params['id'];
        $stmt = $this->pdo->prepare("SELECT px.*, kh.Tenkh, k.Tenkho FROM Phieuxuat px
            LEFT JOIN Khachhang kh ON px.Makh = kh.Makh
            LEFT JOIN Kho k ON px.Makho = k.Makho
            WHERE px.Maxuathang = ?");
        $stmt->execute([$id]);
        $receipt = $stmt->fetch();
        if (!$receipt) $this->jsonResponse(false, 'Export receipt not found', null, 404);

        $stmtD = $this->pdo->prepare("SELECT ct.*, sp.Tensp, sp.Dvt FROM Chitiet_Phieuxuat ct
            LEFT JOIN Sanpham sp ON ct.Masp = sp.Masp
            WHERE ct.Maxuathang = ?");
        $stmtD->execute([$id]);
        $receipt['details'] = $stmtD->fetchAll();
        $this->jsonResponse(true, 'Export receipt details', ['receipt' => $receipt]);
    }

    public function createExportReceipt() {
        $body    = $this->getBody();
        $mapx    = $body['Maxuathang'] ?? ('PX' . time());
        $makh    = $body['Makh'];
        $makho   = $body['Makho'];
        $ngay    = $body['Ngayxuat'] ?? date('Y-m-d');
        $ghichu  = $body['Ghichu'] ?? '';
        $details = $body['details'] ?? [];

        if (empty($details)) $this->jsonResponse(false, 'Vui lòng thêm chi tiết phiếu xuất', null, 400);

        $this->pdo->beginTransaction();
        try {
            $tongTien = 0;
            foreach ($details as $d) {
                $tongTien += ($d['Soluong'] ?? 0) * ($d['Dongiaxuat'] ?? 0);
                $chk = $this->pdo->prepare("SELECT Soluongton FROM Tonkho_sp WHERE Makho = ? AND Masp = ?");
                $chk->execute([$makho, $d['Masp']]);
                $ton = $chk->fetchColumn();
                if ($ton === false || $ton < $d['Soluong']) throw new Exception("Sản phẩm {$d['Masp']} không đủ tồn kho ({$ton}).");
            }

            $stmt = $this->pdo->prepare("INSERT INTO Phieuxuat (Maxuathang, Makh, Makho, Ngayxuat, Tongtienxuat, Ghichu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$mapx, $makh, $makho, $ngay, $tongTien, $ghichu]);

            foreach ($details as $d) {
                $this->pdo->prepare("INSERT INTO Chitiet_Phieuxuat (Maxuathang, Masp, Soluong, Dongiaxuat) VALUES (?, ?, ?, ?)")
                    ->execute([$mapx, $d['Masp'], $d['Soluong'], $d['Dongiaxuat'] ?? 0]);
                $this->pdo->prepare("UPDATE Tonkho_sp SET Soluongton = Soluongton - ? WHERE Makho = ? AND Masp = ?")
                    ->execute([$d['Soluong'], $makho, $d['Masp']]);
            }
            $this->pdo->commit();
            $this->jsonResponse(true, 'Export receipt created', ['id' => $mapx], 201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi tạo phiếu xuất: ' . $e->getMessage(), null, 400);
        }
    }

    // ===== TRANSFERS =====
    public function createTransfer() {
        $body    = $this->getBody();
        $madc    = $body['Madieuchuyen'] ?? ('DC' . time());
        $details = $body['details'] ?? [];

        if (empty($details)) {
            $this->jsonResponse(false, 'Vui lòng thêm chi tiết phiếu điều chuyển', null, 400);
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO Phieudieuchuyen (Madieuchuyen, Khoxuat, Khonhap, Ngaydieuchuyen, Ghichu, Trangthai) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $madc,
                $body['Khoxuat'],
                $body['Khonhap'],
                $body['Ngaydieuchuyen'] ?? date('Y-m-d'),
                $body['Ghichu'] ?? '',
                'dang_xu_ly',
            ]);

            $stmtDet = $this->pdo->prepare(
                "INSERT INTO Chitiet_Phieudieuchuyen (Madieuchuyen, Masp, Soluong) VALUES (?, ?, ?)"
            );
            foreach ($details as $d) {
                $stmtDet->execute([$madc, $d['Masp'], $d['Soluong']]);
            }

            $this->pdo->commit();
            $this->jsonResponse(true, 'Transfer created', ['id' => $madc], 201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi tạo phiếu: ' . $e->getMessage(), null, 400);
        }
    }

    public function getTransfers() {
        $stmt = $this->pdo->query("SELECT p.Madieuchuyen, kx.Tenkho as TenKhoxuat, kn.Tenkho as TenKhonhap, 
            p.Ngaydieuchuyen, p.Ghichu, p.Trangthai,
            (SELECT COUNT(*) FROM Chitiet_Phieudieuchuyen WHERE Madieuchuyen = p.Madieuchuyen) as SoMatHang
            FROM Phieudieuchuyen p
            JOIN Kho kx ON p.Khoxuat = kx.Makho
            JOIN Kho kn ON p.Khonhap = kn.Makho
            ORDER BY p.Ngaydieuchuyen DESC");
        $this->jsonResponse(true, 'Transfers retrieved', ['transfers' => $stmt->fetchAll()]);
    }

    public function getTransfer($params) {
        $id = $params['id'];
        $stmt = $this->pdo->prepare("SELECT p.*, kx.Tenkho as TenKhoxuat, kn.Tenkho as TenKhonhap 
            FROM Phieudieuchuyen p
            JOIN Kho kx ON p.Khoxuat = kx.Makho
            JOIN Kho kn ON p.Khonhap = kn.Makho
            WHERE p.Madieuchuyen = ?");
        $stmt->execute([$id]);
        $master = $stmt->fetch();
        if (!$master) $this->jsonResponse(false, 'Transfer not found', null, 404);
        $stmtD = $this->pdo->prepare("SELECT c.*, sp.Tensp, sp.Dvt FROM Chitiet_Phieudieuchuyen c 
            LEFT JOIN Sanpham sp ON c.Masp = sp.Masp WHERE c.Madieuchuyen = ?");
        $stmtD->execute([$id]);
        $master['details'] = $stmtD->fetchAll();
        $this->jsonResponse(true, 'Transfer details retrieved', ['transfer' => $master]);
    }

    public function executeTransfer($params) {
        $id = $params['id'];
        $stmt = $this->pdo->prepare("SELECT * FROM Phieudieuchuyen WHERE Madieuchuyen = ?");
        $stmt->execute([$id]);
        $phieu = $stmt->fetch();
        if (!$phieu) $this->jsonResponse(false, 'Phiếu không tồn tại', null, 404);
        if ($phieu['Trangthai'] === 'da_thuc_hien') $this->jsonResponse(false, 'Phiếu đã thực hiện', null, 409);

        $this->pdo->beginTransaction();
        try {
            $stmtD = $this->pdo->prepare("SELECT Masp, Soluong FROM Chitiet_Phieudieuchuyen WHERE Madieuchuyen = ?");
            $stmtD->execute([$id]);
            foreach ($stmtD->fetchAll() as $d) {
                // Logic trừ kho xuất, cộng kho nhập... (viết gọn)
                $this->pdo->prepare("UPDATE Tonkho_sp SET Soluongton = Soluongton - ? WHERE Makho = ? AND Masp = ?")
                    ->execute([$d['Soluong'], $phieu['Khoxuat'], $d['Masp']]);
                // Cộng kho nhập...
                $this->pdo->prepare("UPDATE Tonkho_sp SET Soluongton = Soluongton + ? WHERE Makho = ? AND Masp = ?")
                    ->execute([$d['Soluong'], $phieu['Khonhap'], $d['Masp']]);
            }
            $this->pdo->prepare("UPDATE Phieudieuchuyen SET Trangthai = 'da_thuc_hien' WHERE Madieuchuyen = ?")->execute([$id]);
            $this->pdo->commit();
            $this->jsonResponse(true, 'Điều chuyển thành công');
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(false, 'Lỗi: ' . $e->getMessage(), null, 400);
        }
    }
}
