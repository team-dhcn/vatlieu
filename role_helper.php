<?php
/**
 * Role-Based Access Control Helper
 * 3 Roles: Admin (admin), Staff (staff), Guest (guest)
 */

// Define role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_GUEST', 'guest');

// All available roles
define('ALL_ROLES', [ROLE_ADMIN, ROLE_STAFF, ROLE_GUEST]);

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get current user role
 */
function getCurrentRole() {
    if (!isLoggedIn()) {
        return ROLE_GUEST;
    }
    return $_SESSION['user']['role'] ?? ROLE_GUEST;
}

/**
 * Get current user info
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if current user is Admin
 */
function isAdmin() {
    return getCurrentRole() === ROLE_ADMIN;
}

/**
 * Check if current user is Staff
 */
function isStaff() {
    return getCurrentRole() === ROLE_STAFF;
}

/**
 * Check if user has a specific role
 */
function hasRole($role) {
    return getCurrentRole() === $role;
}

/**
 * Check if user has any of the given roles
 */
function hasAnyRole($roles) {
    $currentRole = getCurrentRole();
    return in_array($currentRole, (array)$roles);
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: dangnhap.php');
        exit;
    }
}

/**
 * Require specific role - redirect to error if not authorized
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này. Yêu cầu role: ' . htmlspecialchars($role));
    }
}

/**
 * Require any of the given roles
 */
function requireAnyRole($roles) {
    requireLogin();
    
    if (!hasAnyRole($roles)) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

/**
 * Get role display name (Vietnamese)
 */
function getRoleName($role) {
    $roleNames = [
        ROLE_ADMIN => 'Quản trị viên',
        ROLE_STAFF => 'Nhân viên',
        ROLE_GUEST => 'Khách'
    ];
    return $roleNames[$role] ?? 'Không xác định';
}

/**
 * Get role badge HTML
 */
function getRoleBadge($role) {
    $colors = [
        ROLE_ADMIN => 'bg-red-600',
        ROLE_STAFF => 'bg-blue-600',
        ROLE_GUEST => 'bg-gray-600'
    ];
    $color = $colors[$role] ?? 'bg-gray-600';
    return '<span class="px-2 py-1 text-xs font-semibold text-white ' . $color . ' rounded">' 
        . htmlspecialchars(getRoleName($role)) . '</span>';
}

/**
 * Get permission level (higher = more access)
 * Admin >= Staff >= Guest
 */
function getRoleLevel($role) {
    $levels = [
        ROLE_ADMIN => 3,
        ROLE_STAFF => 2,
        ROLE_GUEST => 1
    ];
    return $levels[$role] ?? 0;
}

/**
 * Check if user has at least the required role level
 */
function hasRoleLevel($requiredRole) {
    return getRoleLevel(getCurrentRole()) >= getRoleLevel($requiredRole);
}

/**
 * Log role action for audit
 */
function logRoleAction($action, $details = '') {
    if (!isLoggedIn()) {
        return;
    }
    
    $user = getCurrentUser();
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user['id'] ?? 'unknown',
        'role' => getCurrentRole(),
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // In production, save to database
    error_log(json_encode($logData));
}

/**
 * Display authorized content based on role
 */
function showIfRole($role, $content) {
    if (hasRole($role)) {
        echo $content;
    }
}

/**
 * Display authorized content based on any role
 */
function showIfAnyRole($roles, $content) {
    if (hasAnyRole($roles)) {
        echo $content;
    }
}
