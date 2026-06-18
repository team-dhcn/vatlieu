-- ============================================================
-- Há»‡ thá»‘ng Quáº£n lÃ½ Kho VLXD
-- Database: vatlieu (gá»™p toÃ n bá»™ báº£ng tá»« cÃ¡c microservice)
-- Cháº¡y: mysql -u root -p < database/vatlieu.sql
-- Hoáº·c import qua phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS vatlieu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE vatlieu;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- NgÆ°á»i dÃ¹ng (user-service)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Nguoidung (
    Manv VARCHAR(50) PRIMARY KEY,
    Tendangnhap VARCHAR(100) NOT NULL UNIQUE,
    Matkhau VARCHAR(255) NOT NULL,
    Hovaten VARCHAR(255),
    Email VARCHAR(100),
    Vaitro VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Sáº£n pháº©m & nguyÃªn váº­t liá»‡u (product-service)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Nhacungcap (
    Mancc VARCHAR(50) PRIMARY KEY,
    Tenncc VARCHAR(255) NOT NULL,
    Sdtncc VARCHAR(15),
    Diachincc VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Danhmucsp (
    Madm INT PRIMARY KEY AUTO_INCREMENT,
    Tendm VARCHAR(100) NOT NULL UNIQUE,
    Mota VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Nguyenvatlieu (
    Manvl VARCHAR(50) PRIMARY KEY,
    Tennvl VARCHAR(255) NOT NULL,
    Dvt VARCHAR(50) NOT NULL,
    Giavon DECIMAL(18, 2) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Sanpham (
    Masp VARCHAR(50) PRIMARY KEY,
    Tensp VARCHAR(255) NOT NULL,
    Madm INT,
    Dvt VARCHAR(50) NOT NULL,
    Giaban DECIMAL(18, 2) DEFAULT 0,
    FOREIGN KEY (Madm) REFERENCES Danhmucsp(Madm) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Congthucsanpham (
    Masp VARCHAR(50),
    Manvl VARCHAR(50),
    Soluong DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (Masp, Manvl),
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Manvl) REFERENCES Nguyenvatlieu(Manvl) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- KhÃ¡ch hÃ ng (customer-service)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Loaikhachhang (
    Maloaikh INT PRIMARY KEY AUTO_INCREMENT,
    Tenloaikh VARCHAR(100) NOT NULL,
    Motaloaikh TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Khachhang (
    Makh VARCHAR(50) PRIMARY KEY,
    Tenkh VARCHAR(255) NOT NULL,
    Sdtkh VARCHAR(15),
    Diachikh VARCHAR(255),
    Maloaikh INT,
    FOREIGN KEY (Maloaikh) REFERENCES Loaikhachhang(Maloaikh) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Kho & chá»©ng tá»« (warehouse-service)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Kho (
    Makho VARCHAR(50) PRIMARY KEY,
    Tenkho VARCHAR(100) NOT NULL,
    Diachi TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tonkho_nvl (
    Makho VARCHAR(50),
    Manvl VARCHAR(50),
    Soluongton DECIMAL(18, 2) DEFAULT 0,
    PRIMARY KEY (Makho, Manvl),
    FOREIGN KEY (Makho) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Manvl) REFERENCES Nguyenvatlieu(Manvl) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tonkho_sp (
    Makho VARCHAR(50),
    Masp VARCHAR(50),
    Soluongton DECIMAL(18, 2) DEFAULT 0,
    PRIMARY KEY (Makho, Masp),
    FOREIGN KEY (Makho) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Phieunhap (
    Manhaphang VARCHAR(50) PRIMARY KEY,
    Mancc VARCHAR(50),
    Makho VARCHAR(50),
    Ngaynhaphang DATE NOT NULL,
    Tongtiennhap DECIMAL(18, 2) DEFAULT 0,
    Ghichu TEXT,
    FOREIGN KEY (Mancc) REFERENCES Nhacungcap(Mancc) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (Makho) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Chitiet_Phieunhap (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Manhaphang VARCHAR(50),
    Manvl VARCHAR(50),
    Soluong DECIMAL(18, 2) NOT NULL,
    Dongianhap DECIMAL(18, 2) NOT NULL,
    Thanhtien DECIMAL(18, 2) AS (Soluong * Dongianhap) STORED,
    FOREIGN KEY (Manhaphang) REFERENCES Phieunhap(Manhaphang) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Manvl) REFERENCES Nguyenvatlieu(Manvl) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Phieudieuchuyen (
    Madieuchuyen VARCHAR(50) PRIMARY KEY,
    Khoxuat VARCHAR(50) NOT NULL,
    Khonhap VARCHAR(50) NOT NULL,
    Ngaydieuchuyen DATE NOT NULL,
    Ghichu TEXT,
    Trangthai VARCHAR(50) DEFAULT 'dang_xu_ly',
    FOREIGN KEY (Khoxuat) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (Khonhap) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Chitiet_Phieudieuchuyen (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Madieuchuyen VARCHAR(50),
    Masp VARCHAR(50),
    Soluong DECIMAL(18, 2) NOT NULL,
    FOREIGN KEY (Madieuchuyen) REFERENCES Phieudieuchuyen(Madieuchuyen) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Phieuxuat (
    Maxuathang VARCHAR(50) PRIMARY KEY,
    Makh VARCHAR(50),
    Makho VARCHAR(50),
    Ngayxuat DATE NOT NULL,
    Tongtienxuat DECIMAL(18, 2) DEFAULT 0,
    Ghichu TEXT,
    FOREIGN KEY (Makh) REFERENCES Khachhang(Makh) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (Makho) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Chitiet_Phieuxuat (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Maxuathang VARCHAR(50),
    Masp VARCHAR(50),
    Soluong DECIMAL(18, 2) NOT NULL,
    Dongiaxuat DECIMAL(18, 2) NOT NULL,
    Thanhtien DECIMAL(18, 2) AS (Soluong * Dongiaxuat) STORED,
    FOREIGN KEY (Maxuathang) REFERENCES Phieuxuat(Maxuathang) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;



-- ------------------------------------------------------------
-- Sáº£n xuáº¥t (manufacturing-service)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Lenhsanxuat (
    Malenh VARCHAR(50) PRIMARY KEY,
    Masp VARCHAR(50) NOT NULL,
    Ngaysanxuat DATE NOT NULL,
    Soluongsanxuat DECIMAL(18, 2) NOT NULL,
    Trangthai VARCHAR(50) DEFAULT 'dang_xu_ly',
    Ngaybatdau DATE NULL,
    Ngayketthuc DATE NULL,
    Ghichu TEXT NULL,
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Chitiet_XuatNVL_Sanxuat (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Malenh VARCHAR(50),
    Manvl VARCHAR(50),
    Soluong DECIMAL(18, 2) NOT NULL,
    FOREIGN KEY (Malenh) REFERENCES Lenhsanxuat(Malenh) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Manvl) REFERENCES Nguyenvatlieu(Manvl) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Chitiet_Nhapsanpham_Sanxuat (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Malenh VARCHAR(50),
    Makho VARCHAR(50),
    Masp VARCHAR(50),
    Soluong DECIMAL(18, 2) NOT NULL,
    FOREIGN KEY (Malenh) REFERENCES Lenhsanxuat(Malenh) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (Makho) REFERENCES Kho(Makho) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;






-- Them tai khoan lananh mac dinh de test
INSERT INTO Nguoidung (Manv, Tendangnhap, Matkhau, Hovaten, Email, Vaitro)
VALUES ('NV002', 'lananh', '123456', 'Lan Anh', 'lananh@vlxd.com', 'admin')
ON DUPLICATE KEY UPDATE Tendangnhap='lananh';

