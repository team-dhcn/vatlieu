<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Quản lý Kho VLXD'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:rgb(6, 10, 15); font-family: 'Segoe UI', sans-serif; }
        .sidebar { background: linear-gradient(180deg, #1e3a5f, #0d2137); height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; z-index: 1000; overflow-y: auto; }
        .sidebar .nav-link { color: rgba(216, 175, 50, 0.8) !important; padding: 12px 20px; border-radius: 5px; margin: 4px 10px; transition: all 0.3s; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); color: white !important; }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.2) !important; color: white !important; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 30px; min-height: 100vh; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .table thead { background-color:rgb(11, 12, 12); }
        .chip { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; background:rgb(13, 14, 16); color: #1967d2; }
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; margin: 0 2px; }
    </style>
    <script>
        // Check if user is logged in
        const token = localStorage.getItem('token');
        if (!token && window.location.pathname.indexOf('dangnhap') === -1) {
            window.location.href = 'dangnhap';
        }
    </script>
    <script>
        // =====================================================
        // API GATEWAY FETCH INTERCEPTOR
        // API monolith — tự rewrite URL /api_gateway.php/... sang ?route=
        // =====================================================
        const API_BASE = '/vlxd/api_gateway.php';
        const API      = API_BASE; // Tương thích ngược với: fetch(API + '/...')

        function apiUrl(path, params = {}) {
            const route = path.replace(/^\/+/, '');
            const url   = new URL(API_BASE, window.location.origin);
            url.searchParams.set('route', route);
            Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
            return url.toString();
        }

        // Patch window.fetch để tự rewrite URL cũ sang ?route= format
        (function () {
            const _fetch = window.fetch.bind(window);
            window.fetch = function (url, options) {
                if (typeof url === 'string' && url.includes('/api_gateway.php/')) {
                    try {
                        const u    = new URL(url, window.location.origin);
                        const seg  = u.pathname.replace(/.*\/api_gateway\.php\//, '');
                        const newU = new URL(API_BASE, window.location.origin);
                        newU.searchParams.set('route', seg);
                        u.searchParams.forEach((v, k) => { if (k !== 'route') newU.searchParams.set(k, v); });
                        url = newU.toString();
                    } catch (e) { /* giữ nguyên nếu parse lỗi */ }
                }
                return _fetch(url, options);
            };
        })();
</script>
</head>
<body>
