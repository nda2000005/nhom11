<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

    /* --- SỬA LỖI ICON Ở ĐÂY --- */
    .admin-sidebar-wrapper { 
        font-family: 'Poppins', sans-serif; 
        box-sizing: border-box;
    }
    
    /* Đảm bảo icon luôn dùng đúng font của nó */
    .admin-sidebar-wrapper i, 
    .admin-sidebar-wrapper .fa-solid {
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900;
    }

    /* Các biến màu sắc */
    :root {
        --sidebar-width: 280px;
        --blue-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); /* Xanh dương */
    }

    /* NÚT TOGGLE (HAMBURGER) */
    .admin-toggle-btn {
        position: fixed;
        top: 20px; left: 20px;
        z-index: 9999;
        width: 45px; height: 45px;
        background: #0d47a1;
        color: white;
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        transition: 0.3s;
    }
    .admin-toggle-btn:hover { background: #1565c0; transform: scale(1.05); }

    /* KHUNG SIDEBAR */
    .admin-sidebar {
        position: fixed;
        top: 0; left: -280px;
        width: 280px;
        height: 100vh;
        background: var(--blue-gradient);
        color: #fff;
        z-index: 10000;
        transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex; flex-direction: column;
        box-shadow: 5px 0 25px rgba(0,0,0,0.2);
    }
    .admin-sidebar.show { left: 0; }

    /* HEADER */
    .sidebar-header {
        height: 80px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .brand { font-size: 20px; font-weight: 700; display: flex; gap: 10px; align-items: center; }
    .close-sidebar { cursor: pointer; font-size: 22px; opacity: 0.8; transition: 0.2s; }
    .close-sidebar:hover { opacity: 1; transform: rotate(90deg); }

    /* MENU */
    .sidebar-menu { flex: 1; overflow-y: auto; padding: 20px 15px; }
    .sidebar-menu ul { list-style: none; padding: 0; margin: 0; }
    
    .menu-label {
        font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
        color: #90caf9; font-weight: 700; margin: 20px 10px 10px;
    }

    .menu-link {
        display: flex; align-items: center;
        padding: 12px 20px;
        color: #e3f2fd; text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
        font-size: 14px; font-weight: 500;
        margin-bottom: 5px;
    }
    .menu-link i { width: 25px; font-size: 18px; margin-right: 10px; text-align: center; }

    /* Hover & Active */
    .menu-link:hover { background: rgba(255,255,255,0.15); color: #fff; padding-left: 25px; }
    
    .menu-link.active {
        background: rgba(255,255,255,0.25);
        color: #fff; font-weight: 600;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* FOOTER */
    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        background: rgba(0,0,0,0.1);
        display: flex; gap: 15px; align-items: center;
    }
    .user-avatar {
        width: 40px; height: 40px; background: white; color: #1565c0;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .user-info { display: flex; flex-direction: column; }
    .user-name { font-weight: 600; font-size: 14px; }
    .user-role { font-size: 11px; opacity: 0.8; }

    /* OVERLAY */
    .sidebar-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
        z-index: 9000; opacity: 0; visibility: hidden; transition: 0.3s;
    }
    .sidebar-overlay.show { opacity: 1; visibility: visible; }
    
    /* Button cho trang public */
    .public-link-btn {
        background: rgba(255,255,255,0.1);
        border: 1px dashed rgba(255,255,255,0.3);
        margin-bottom: 15px;
    }
    .public-link-btn:hover {
        background: #fff;
        color: #1e3a8a;
    }
</style>

<div class="admin-sidebar-wrapper">
    
    <button class="admin-toggle-btn" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <aside id="adminSidebar" class="admin-sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <i class="fa-solid fa-shield-halved"></i> <span>AdminPanel</span>
            </div>
            <div class="close-sidebar" onclick="toggleSidebar()">
                <i class="fa-solid fa-xmark"></i>
            </div>
        </div>

        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="/mnopl/public/index.php" target="_blank" class="menu-link public-link-btn">
                        <i class="fa-solid fa-earth-americas"></i> Xem trang chủ
                    </a>
                </li>
            </ul>

            <div class="menu-label">Tổng Quan</div>
            <ul>
                <li><a href="/mnopl/admin/dashboard/index.php" class="menu-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="/mnopl/admin/report/index.php" class="menu-link"><i class="fa-solid fa-file-invoice"></i> Báo cáo</a></li>
            </ul>

            <div class="menu-label">Quản Lý Cửa Hàng</div>
            <ul>
                <li><a href="/mnopl/admin/products/index.php" class="menu-link"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>
                
                <li><a href="/mnopl/admin/order/View/index.php" class="menu-link"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a></li>
                
                <li><a href="/mnopl/admin/inventory/index.php" class="menu-link"><i class="fa-solid fa-warehouse"></i> Kho hàng</a></li>
                
                <li><a href="/mnopl/admin/customers/index.php" class="menu-link"><i class="fa-solid fa-users"></i> Khách hàng</a></li>
            </ul>

            <div class="menu-label">Hệ Thống</div>
            <ul>
                <li><a href="/mnopl/admin/logout.php" class="menu-link" style="color: #ffcdd2;">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
            <div class="user-info">
                <span class="user-name">Administrator</span>
                <span class="user-role">Super Admin</span>
            </div>
        </div>
    </aside>

    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('adminSidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    // Tự động Active menu dựa trên URL hiện tại
    document.addEventListener("DOMContentLoaded", function() {
        const currentUrl = window.location.href;
        const links = document.querySelectorAll('.menu-link');
        
        links.forEach(link => {
            // Lấy đường dẫn trong href
            const href = link.getAttribute('href');
            // Nếu URL hiện tại chứa href đó (và href không phải là trang chủ public)
            if (href && currentUrl.includes(href) && href !== '/mnopl/public/index.php') {
                link.classList.add('active');
            }
        });
    });
</script>