<div class="topbar">
    <div class="topbar-left">
        <span class="page-title">My <span>Profile</span></span>
    </div>
    <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleTheme()">
            <i class="fa-solid fa-moon" id="themeIcon"></i>
            <span id="themeText">Dark</span>
        </button>
        <div class="user-chip">
            <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($username) ?></div>
                <div class="user-role"><?= htmlspecialchars($role_display) ?></div>
            </div>
        </div>
    </div>
</div>
