
<div class="manage-logo">
        <img src="{{ plat_logo|e('html_attr') }}" />
</div>
<div class="user-menu" id="main-user-menu">
    <input id="toggle" type="checkbox"></input>
    <label for="toggle" class="toggler">
        <div class="user-data">
            <span></span>
        </div>
        <div class="user-avatar">
            <img src="http://www.gravatar.com/avatar/?d=mp" />
            <div class="hamburger">
                <div class="top-bun"></div>
                <div class="meat"></div>
                <div class="bottom-bun"></div>
            </div>
        </div>
    </label>
    <ul class="menu-list">
        <li class="menu-header">
            <span class="user-name">{{ plat_user_fname|e }} {{ plat_user_lname|e }}</span>
            <span class="user-email">
                <i class='fas fa-user fw-normal'></i>
                {{ plat_user_email|e}}
            </span>
        </li>
        <li class="menu-item" data-menu-action="avoid" data-param="profile" data-menu-id="profile">
            {# <i class='fas fa-address-card'></i>My Profile #}
            <span class="material-icons-outlined lg space-4">portrait</span>My Profile
        </li>
        <li class="menu-item" data-menu-action="avoid" data-param="messages" data-menu-id="messages">
            {# <i class='fas fa-comment-dots'></i>Messages #}
            <span class="material-icons-outlined lg space-4">message</span>Messages
            <span class="badge rounded-pill bg-info" data-max="99">6</span>
        </li>
        <li class="menu-item" data-menu-action="avoid" data-param="notifications" data-menu-id="notifications">
            {# <i class='fas fa-bell'></i>Notifications #}
            <span class="material-icons-outlined lg space-4">notifications</span>Notifications
            <span class="badge rounded-pill bg-danger" data-max="99">16</span>
        </li>
        <li class="menu-item" data-menu-action="avoid" data-param="personalize" data-menu-id="personalize">
            {# <i class='fas fa-swatchbook'></i>Personalize #}
            <span class="material-icons-outlined lg space-4">tune</span>Personalize
        </li>
        <li class="menu-item" data-menu-action="navigate" data-param="{{ plat_admin_url_base|e('html_attr') }}/manage/logout">
            {# <i class='fas fa-sign-out-alt'></i>Sign Out #}
            <span class="material-icons-outlined lg space-4">logout</span>Sign Out
        </li>
    </ul>
</div>