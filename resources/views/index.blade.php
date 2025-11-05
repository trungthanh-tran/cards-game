<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống mua thẻ game</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .wallet-balance {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
        }
        
        .cart-btn {
            position: relative;
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffd93d;
            color: #333;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Auth Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 100%;
            max-width: 450px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .switch-auth {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .switch-auth a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        /* Cards Grid */
        .section-title {
            color: white;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card-category {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card-category:hover {
            transform: translateY(-5px);
        }
        
        .card-category img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .card-category h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .card-category p {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .denominations {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .denomination-btn {
            padding: 8px 15px;
            background: #f0f0f0;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .denomination-btn:hover {
            background: #e0e0e0;
        }
        
        .denomination-btn.selected {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: #667eea;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .quantity-control button {
            width: 35px;
            height: 35px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
        
        .quantity-control input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }
        
        .add-to-cart-btn {
            width: 100%;
            padding: 12px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
        }
        
        .add-to-cart-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Cart Sidebar */
        .cart-sidebar {
            position: fixed;
            right: -400px;
            top: 0;
            width: 400px;
            max-width: 90vw;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 20px rgba(0,0,0,0.2);
            transition: right 0.3s;
            z-index: 1001;
            display: flex;
            flex-direction: column;
        }
        
        .cart-sidebar.active {
            right: 0;
        }
        
        .cart-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-header h2 {
            font-size: 22px;
        }
        
        .close-cart {
            font-size: 28px;
            cursor: pointer;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .cart-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .cart-item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .cart-item-name {
            font-weight: bold;
            color: #333;
        }
        
        .cart-item-price {
            color: #ff6b6b;
            font-weight: bold;
        }
        
        .cart-item-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cart-item-quantity button {
            width: 30px;
            height: 30px;
            border: none;
            background: #667eea;
            color: white;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .remove-item {
            color: #ff6b6b;
            cursor: pointer;
            font-size: 20px;
        }
        
        .cart-footer {
            padding: 20px;
            border-top: 2px solid #e0e0e0;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        /* Wallet Tab */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 12px;
            background: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            color: #667eea;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .qr-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        
        .qr-code {
            width: 250px;
            height: 250px;
            margin: 20px auto;
            background: #f0f0f0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .bank-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        
        .bank-info p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        
        /* Orders */
        .orders-list {
            background: white;
            border-radius: 15px;
            padding: 20px;
        }
        
        .order-item {
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-number {
            font-weight: bold;
            color: #667eea;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .order-status.completed {
            background: #d4edda;
            color: #155724;
        }
        
        .order-cards {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .card-info {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .header-content {
                justify-content: center;
            }
            
            .cards-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-sidebar {
                width: 100%;
            }
            
            .modal-content {
                padding: 30px 20px;
            }
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: white;
            font-size: 18px;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <header id="header">
        <div class="header-content">
            <div class="logo">🎮 Game Card Shop</div>
             <div class="user-info">
                <div class="wallet-balance">
                    ðŸ’° Sá»‘ dÆ°: <span id="balance">0</span> VNÄ
                </div>
                <button class="cart-btn" onclick="toggleCart()">
                    ðŸ›’ Giá» hÃ ng
                    <span class="cart-count" id="cart-count">0</span>
                </button>
                <button class="btn" style="width: auto; padding: 10px 20px;" onclick="logout()">ÄÄƒng xuáº¥t</button>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('cards')">Mua thẻ</button>
            <button class="tab-btn" onclick="switchTab('wallet')">Ví của tôi</button>
            <button class="tab-btn" onclick="switchTab('orders')">Đơn hàng</button>
        </div>

        <!-- Cards Tab -->
        <div id="cards-tab" class="tab-content active">
            <h2 class="section-title">Danh sách thẻ game</h2>
            <div id="cards-grid" class="cards-grid"></div>
        </div>

        <!-- Wallet Tab -->
        <div id="wallet-tab" class="tab-content">
            <div class="qr-container">
                <h2>Nạp tiền vào ví</h2>
                <div class="form-group">
                    <label>Số tiền cần nạp (VNĐ)</label>
                    <input type="number" id="deposit-amount" placeholder="Nhập số tiền" min="10000" step="1000">
                </div>
                <button class="btn" onclick="generateQR()">Tạo mã QR</button>
                
                <div id="qr-result" class="hidden">
                    <div class="qr-code">
                        <img id="qr-image" src="" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div class="bank-info">
                        <p><strong>Ngân hàng:</strong> <span id="bank-name"></span></p>
                        <p><strong>Số tài khoản:</strong> <span id="bank-account"></span></p>
                        <p><strong>Chủ tài khoản:</strong> <span id="account-holder"></span></p>
                        <p><strong>Nội dung CK:</strong> <span id="transfer-content"></span></p>
                        <p><strong>Số tiền:</strong> <span id="transfer-amount"></span> VNĐ</p>
                    </div>
                    <button class="btn" onclick="confirmDeposit()">Xác nhận đã chuyển khoản</button>
                </div>
            </div>
            
            <div class="orders-list" style="margin-top: 30px;">
                <h3>Lịch sử giao dịch</h3>
                <div id="transactions-list"></div>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content">
            <div class="orders-list">
                <h2>Đơn hàng của tôi</h2>
                <div id="orders-list"></div>
            </div>
        </div>
    </div>

    <!-- Auth Modal -->
    <div id="auth-modal" class="modal active">
        <div class="modal-content">
            <span class="close-modal" onclick="closeAuthModal()">&times;</span>
            
            <!-- Login Form -->
            <div id="login-form">
                <h2 style="margin-bottom: 30px; text-align: center; color: #667eea;">Đăng nhập</h2>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="login-email" placeholder="Nhập email">
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" id="login-password" placeholder="Nhập mật khẩu">
                </div>
                <button class="btn" onclick="login()">Đăng nhập</button>
                <div class="switch-auth">
                    Chưa có tài khoản? <a href="#" onclick="showRegister()">Đăng ký ngay</a>
                </div>
            </div>

            <!-- Register Form -->
            <div id="register-form" class="hidden">
                <h2 style="margin-bottom: 30px; text-align: center; color: #667eea;">Đăng ký</h2>
                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" id="register-name" placeholder="Nhập họ tên">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="register-email" placeholder="Nhập email">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" id="register-phone" placeholder="Nhập số điện thoại">
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" id="register-password" placeholder="Nhập mật khẩu">
                </div>
                <div class="form-group">
                    <label>Xác nhận mật khẩu</label>
                    <input type="password" id="register-password-confirm" placeholder="Nhập lại mật khẩu">
                </div>
                <button class="btn" onclick="register()">Đăng ký</button>
                <div class="switch-auth">
                    Đã có tài khoản? <a href="#" onclick="showLogin()">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <h2>Giỏ hàng</h2>
            <span class="close-cart" onclick="toggleCart()">&times;</span>
        </div>
        <div class="cart-items" id="cart-items">
            <div class="empty-cart">Giỏ hàng trống</div>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Tổng cộng:</span>
                <span id="cart-total">0 VNĐ</span>
            </div>
            <button class="checkout-btn" onclick="checkout()">Thanh toán</button>
        </div>
    </div>

    <div id="alert-container"></div>

    <script>
        const API_URL = '/api'; // Thay đổi URL API của bạn
        let token = localStorage.getItem('token');
        let currentUser = null;
        let cart = { items: [], total: 0, count: 0 };
        let categories = [];
        let qrData = null;

        // Auth functions
        async function login() {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            try {
                // BƯỚC 1: Lấy CSRF cookie trước (fix lỗi 419)
                await fetch(`${API_URL.replace('/api', '')}/sanctum/csrf-cookie`, {
                    credentials: 'include'
                });

                // BƯỚC 2: Gửi request login
                const response = await fetch(`${API_URL}/login`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();
                
                if (response.ok) {
                    token = data.token;
                    localStorage.setItem('token', token);
                    currentUser = data.user;
                    closeAuthModal();
                    await init();
                    showAlert('Đăng nhập thành công!', 'success');
                } else {
                    showAlert(data.message || 'Đăng nhập thất bại', 'error');
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('Lỗi kết nối', 'error');
            }
        }

        async function register() {
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const phone = document.getElementById('register-phone').value;
            const password = document.getElementById('register-password').value;
            const password_confirmation = document.getElementById('register-password-confirm').value;

            try {
                // BƯỚC 1: Lấy CSRF cookie trước (fix lỗi 419)
                await fetch(`${API_URL.replace('/api', '')}/sanctum/csrf-cookie`, {
                    credentials: 'include'
                });

                // BƯỚC 2: Gửi request register
                const response = await fetch(`${API_URL}/register`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    body: JSON.stringify({ name, email, phone, password, password_confirmation })
                });

                const data = await response.json();
                
                if (response.ok) {
                    token = data.token;
                    localStorage.setItem('token', token);
                    currentUser = data.user;
                    closeAuthModal();
                    await init();
                    showAlert('Đăng ký thành công!', 'success');
                } else {
                    showAlert(data.errors ? Object.values(data.errors)[0][0] : 'Đăng ký thất bại', 'error');
                }
            } catch (error) {
                console.error('Register error:', error);
                showAlert('Lỗi kết nối', 'error');
            }
        }

        function logout() {
            localStorage.removeItem('token');
            token = null;
            currentUser = null;
            document.getElementById('auth-modal').classList.add('active');
            document.getElementById('header').classList.add('hidden');
        }

        function showLogin() {
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('login-form').classList.remove('hidden');
        }

        function showRegister() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        }

        function closeAuthModal() {
            document.getElementById('auth-modal').classList.remove('active');
            document.getElementById('header').classList.remove('hidden');
        }

        // Init
        async function init() {
            await loadUser();
            await loadCategories();
            await loadCart();
            updateCartUI();
        }

        async function loadUser() {
            try {
                const response = await fetch(`${API_URL}/me`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                currentUser = data;
                document.getElementById('balance').textContent = formatMoney(data.wallet.balance);
            } catch (error) {
                console.error('Error loading user:', error);
            }
        }

        async function loadCategories() {
            try {
                const response = await fetch(`${API_URL}/cards/categories`);
                categories = await response.json();
                renderCategories();
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        function renderCategories() {
            const grid = document.getElementById('cards-grid');
            grid.innerHTML = categories.map(cat => `
                <div class="card-category">
                    <img src="${cat.image || 'https://via.placeholder.com/280x150?text=' + cat.name}" alt="${cat.name}">
                    <h3>${cat.name}</h3>
                    <p>${cat.description || ''}</p>
                    <div class="denominations">
                        ${cat.denominations.map(den => `
                            <button class="denomination-btn" data-cat-id="${cat.id}" data-den-id="${den.id}" 
                                onclick="selectDenomination(${cat.id}, ${den.id})">
                                ${formatMoney(den.value)} 
                                <small>(${den.stock} thẻ)</small>
                            </button>
                        `).join('')}
                    </div>
                    <div class="quantity-control" id="quantity-${cat.id}" style="display: none;">
                        <button onclick="changeQuantity(${cat.id}, -1)">-</button>
                        <input type="number" id="qty-${cat.id}" value="1" min="1" max="50">
                        <button onclick="changeQuantity(${cat.id}, 1)">+</button>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="add-to-cart-btn" id="add-btn-${cat.id}" style="display: none; flex: 1;" 
                            onclick="addToCart(${cat.id})">
                            🛒 Thêm vào giỏ
                        </button>
                        <button class="add-to-cart-btn" id="buy-now-btn-${cat.id}" 
                            style="display: none; flex: 1; background: linear-gradient(135deg, #ff6b6b, #ee5a6f);" 
                            onclick="buyNow(${cat.id})">
                            ⚡ Mua ngay
                        </button>
                    </div>
                </div>
            `).join('');
        }

        let selectedDenominations = {};

        function selectDenomination(catId, denId) {
            // Deselect all in category
            document.querySelectorAll(`[data-cat-id="${catId}"]`).forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // Select clicked
            event.target.classList.add('selected');
            selectedDenominations[catId] = denId;
            
            // Show quantity and buttons
            document.getElementById(`quantity-${catId}`).style.display = 'flex';
            document.getElementById(`add-btn-${catId}`).style.display = 'block';
            document.getElementById(`buy-now-btn-${catId}`).style.display = 'block';
        }

        function changeQuantity(catId, delta) {
            const input = document.getElementById(`qty-${catId}`);
            let value = parseInt(input.value) + delta;
            if (value < 1) value = 1;
            if (value > 50) value = 50;
            input.value = value;
        }

        async function addToCart(catId) {
            const denId = selectedDenominations[catId];
            const qty = parseInt(document.getElementById(`qty-${catId}`).value);
            
            if (!denId) return;

            try {
                const response = await fetch(`${API_URL}/cart/add`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ denomination_id: denId, quantity: qty })
                });

                const data = await response.json();
                
                if (response.ok) {
                    cart = data.cart;
                    updateCartUI();
                    showAlert('Đã thêm vào giỏ hàng!', 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Lỗi kết nối', 'error');
            }
        }

        async function buyNow(catId) {
            const denId = selectedDenominations[catId];
            const qty = parseInt(document.getElementById(`qty-${catId}`).value);
            
            if (!denId) return;

            try {
                // Step 1: Add to cart
                const addResponse = await fetch(`${API_URL}/cart/add`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ denomination_id: denId, quantity: qty })
                });

                if (!addResponse.ok) {
                    const data = await addResponse.json();
                    showAlert(data.message, 'error');
                    return;
                }

                cart = await addResponse.json().then(d => d.cart);
                updateCartUI();

                // Step 2: Show confirmation
                const denomination = categories
                    .find(c => c.id === catId)
                    .denominations
                    .find(d => d.id === denId);
                
                const totalAmount = denomination.price * qty;

                if (confirm(`Xác nhận mua:\n\n${denomination.category_name || 'Thẻ'} - ${formatMoney(denomination.value)} VNĐ\nSố lượng: ${qty}\nTổng tiền: ${formatMoney(totalAmount)} VNĐ\n\nBạn có chắc chắn muốn thanh toán?`)) {
                    // Step 3: Checkout
                    await checkout();
                }
            } catch (error) {
                showAlert('Lỗi kết nối', 'error');
            }
        }

        async function loadCart() {
            try {
                const response = await fetch(`${API_URL}/cart`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                cart = await response.json();
                updateCartUI();
            } catch (error) {
                console.error('Error loading cart:', error);
            }
        }

        function updateCartUI() {
            document.getElementById('cart-count').textContent = cart.count;
            
            const cartItems = document.getElementById('cart-items');
            if (cart.items.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart">Giỏ hàng trống</div>';
            } else {
                cartItems.innerHTML = cart.items.map(item => `
                    <div class="cart-item">
                        <div class="cart-item-header">
                            <div class="cart-item-name">${item.category_name} - ${formatMoney(item.value)}</div>
                            <div class="cart-item-price">${formatMoney(item.price * item.quantity)}</div>
                        </div>
                        <div class="cart-item-controls">
                            <div class="cart-item-quantity">
                                <button onclick="updateCartItem(${item.denomination_id}, ${item.quantity - 1})">-</button>
                                <span>${item.quantity}</span>
                                <button onclick="updateCartItem(${item.denomination_id}, ${item.quantity + 1})">+</button>
                            </div>
                            <span class="remove-item" onclick="removeCartItem(${item.denomination_id})">🗑️</span>
                        </div>
                    </div>
                `).join('');
            }
            
            document.getElementById('cart-total').textContent = formatMoney(cart.total) + ' VNĐ';
        }

        async function updateCartItem(denId, qty) {
            try {
                const response = await fetch(`${API_URL}/cart/update`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ denomination_id: denId, quantity: qty })
                });

                cart = await response.json();
                updateCartUI();
            } catch (error) {
                showAlert('Lỗi cập nhật giỏ hàng', 'error');
            }
        }

        async function removeCartItem(denId) {
            try {
                const response = await fetch(`${API_URL}/cart/remove/${denId}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                cart = await response.json();
                updateCartUI();
                showAlert('Đã xóa khỏi giỏ hàng', 'success');
            } catch (error) {
                showAlert('Lỗi xóa sản phẩm', 'error');
            }
        }

        function toggleCart() {
            document.getElementById('cart-sidebar').classList.toggle('active');
        }

        async function checkout() {
            if (cart.items.length === 0) {
                showAlert('Giỏ hàng trống', 'error');
                return;
            }

            if (confirm(`Bạn có chắc muốn thanh toán ${formatMoney(cart.total)} VNĐ?`)) {
                try {
                    const response = await fetch(`${API_URL}/orders/checkout`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ payment_method: 'wallet' })
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
                        showAlert('Đặt hàng thành công!', 'success');
                        cart = { items: [], total: 0, count: 0 };
                        updateCartUI();
                        toggleCart();
                        await loadUser();
                        switchTab('orders');
                        await loadOrders();
                    } else {
                        showAlert(data.message, 'error');
                    }
                } catch (error) {
                    showAlert('Lỗi thanh toán', 'error');
                }
            }
        }

        // Wallet functions
        async function generateQR() {
            const amount = document.getElementById('deposit-amount').value;
            
            if (!amount || amount < 10000) {
                showAlert('Số tiền tối thiểu 10,000 VNĐ', 'error');
                return;
            }

            try {
                const response = await fetch(`${API_URL}/wallet/deposit/qr`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ amount: parseFloat(amount) })
                });

                qrData = await response.json();
                
                document.getElementById('qr-image').src = qrData.qr_url;
                document.getElementById('bank-name').textContent = qrData.qr_data.bank_name;
                document.getElementById('bank-account').textContent = qrData.qr_data.bank_account;
                document.getElementById('account-holder').textContent = qrData.qr_data.account_holder;
                document.getElementById('transfer-content').textContent = qrData.qr_data.content;
                document.getElementById('transfer-amount').textContent = formatMoney(qrData.qr_data.amount);
                
                document.getElementById('qr-result').classList.remove('hidden');
            } catch (error) {
                showAlert('Lỗi tạo QR code', 'error');
            }
        }

        async function confirmDeposit() {
            if (!qrData) return;

            try {
                const response = await fetch(`${API_URL}/wallet/deposit/confirm`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reference_code: qrData.reference_code,
                        amount: qrData.qr_data.amount
                    })
                });

                const data = await response.json();
                
                if (response.ok) {
                    showAlert('Nạp tiền thành công!', 'success');
                    document.getElementById('qr-result').classList.add('hidden');
                    document.getElementById('deposit-amount').value = '';
                    await loadUser();
                    await loadTransactions();
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Lỗi xác nhận nạp tiền', 'error');
            }
        }

        async function loadTransactions() {
            try {
                const response = await fetch(`${API_URL}/wallet/transactions`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                const list = document.getElementById('transactions-list');
                if (data.data.length === 0) {
                    list.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Chưa có giao dịch nào</p>';
                } else {
                    list.innerHTML = data.data.map(trans => `
                        <div class="order-item">
                            <div class="order-header">
                                <div>
                                    <strong>${trans.type === 'deposit' ? '💰 Nạp tiền' : trans.type === 'withdraw' ? '💸 Rút tiền' : '🛒 Mua hàng'}</strong>
                                    <p style="color: #999; font-size: 14px; margin-top: 5px;">${formatDate(trans.created_at)}</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 18px; font-weight: bold; color: ${trans.type === 'deposit' ? '#28a745' : '#dc3545'};">
                                        ${trans.type === 'deposit' ? '+' : '-'}${formatMoney(trans.amount)} VNĐ
                                    </div>
                                    <div style="font-size: 14px; color: #666;">Số dư: ${formatMoney(trans.balance_after)}</div>
                                </div>
                            </div>
                            ${trans.description ? `<p style="margin-top: 10px; color: #666;">${trans.description}</p>` : ''}
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading transactions:', error);
            }
        }

        // Orders functions
        async function loadOrders() {
            try {
                const response = await fetch(`${API_URL}/orders`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const data = await response.json();
                
                const list = document.getElementById('orders-list');
                if (data.data.length === 0) {
                    list.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Chưa có đơn hàng nào</p>';
                } else {
                    list.innerHTML = data.data.map(order => `
                        <div class="order-item">
                            <div class="order-header">
                                <div class="order-number">#${order.order_number}</div>
                                <span class="order-status ${order.status}">${order.status === 'completed' ? 'Hoàn thành' : 'Đang xử lý'}</span>
                            </div>
                            <p style="color: #666; margin: 10px 0;">Ngày đặt: ${formatDate(order.created_at)}</p>
                            <p style="font-weight: bold; color: #ff6b6b; margin-bottom: 10px;">Tổng tiền: ${formatMoney(order.total_amount)} VNĐ</p>
                            
                            <div class="order-cards">
                                <strong style="display: block; margin-bottom: 10px;">Thông tin thẻ:</strong>
                                ${order.items.map(item => `
                                    <div class="card-info">
                                        <p><strong>${item.denomination.category.name}</strong> - ${formatMoney(item.denomination.value)}</p>
                                        <p>Serial: <code>${item.card.serial}</code></p>
                                        <p>Code: <code>${item.card.code}</code></p>
                                        <p>Hạn dùng: ${formatDate(item.card.expiry_date)}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading orders:', error);
            }
        }

        // Tab switching
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(`${tab}-tab`).classList.add('active');
            
            if (tab === 'wallet') {
                loadTransactions();
            } else if (tab === 'orders') {
                loadOrders();
            }
        }

        // Utility functions
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showAlert(message, type) {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alert.style.position = 'fixed';
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.style.minWidth = '300px';
            alert.style.animation = 'slideIn 0.3s ease';
            
            container.appendChild(alert);
            
            setTimeout(() => {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }

        // Initialize app
        if (token) {
            init();
        } else {
            document.getElementById('auth-modal').classList.add('active');
        }
    </script>
</body>
</html>