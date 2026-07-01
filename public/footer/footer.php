<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-links">
            <a href="#">Điều khoản sử dụng</a>
            <a href="#">Thông báo bảo mật</a>
            <a href="#">Công bố quyền riêng tư dữ liệu</a>
            <a href="#" class="privacy-choices">
                Lựa chọn quyền riêng tư của bạn
                <span class="privacy-icon">
                    <svg width="24" height="12" viewBox="0 0 34 18" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 9C0 4.02944 4.02944 0 9 0H25C29.9706 0 34 4.02944 34 9C34 13.9706 29.9706 18 25 18H9C4.02944 18 0 13.9706 0 9Z" fill="#007BFF"/>
                        <path d="M12 9L18 4V14L12 9Z" fill="white"/>
                        <path d="M26 9C26 11.2091 24.2091 13 22 13C19.7909 13 18 11.2091 18 9C18 6.79086 19.7909 5 22 5C24.2091 5 26 6.79086 26 9Z" fill="white"/>
                    </svg>
                </span>
            </a>
        </div>

        <div class="footer-copyright">
            &copy; 1996-2026, TechStore.com, Inc. hoặc các chi nhánh của nó
        </div>
    </div>
</footer>

<style>
    .main-footer {
        background-color: #131a22;
        color: #dddddd;
        padding: 30px 0;
        font-family: Arial, sans-serif;
        font-size: 12px;
        width: 100%;
        margin-top: 50px;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }

    .footer-links {
        margin-bottom: 10px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .footer-links a {
        color: #ffffff;
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-links a:hover {
        text-decoration: underline;
        color: #febd69;
    }

    .footer-copyright {
        color: #cccccc;
        margin-top: 8px;
    }

    .privacy-choices {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .privacy-icon svg {
        vertical-align: middle;
        margin-bottom: 2px;
    }

    @media (max-width: 600px) {
        .footer-links {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>