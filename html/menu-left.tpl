<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary bg-cp-blue">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="/images/logo-square.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{$smarty.env.LOGO_NAME}</span>
    </a>

    <div class="sidebar">
        {if false}
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="/images/default-user.png" class="img-profile-sidebar img-circle elevation-2" alt="{cp_user_name()}"/>
                </div>
                <div class="info">
                    <a href="#" class="d-block">{cp_user_name()}</a>
                </div>
            </div>
        {/if}

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="/" class="nav-link {if $menu_actif eq "dashboard"}active{/if}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Tableau de bord</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{$smarty.env.APP_HOST}" target="app_cp" class="nav-link">
                        <i class="nav-icon fas fa-mobile-alt"></i>
                        <p>Application Civicpower</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{$smarty.env.APP_HOST}/settings" target="app_cp" class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Mon compte</p>
                    </a>
                </li>
                <li class="nav-header">CONSULTATIONS</li>
                <li class="nav-item">
                    <a href="/ballot-list" class="nav-link {if $menu_actif eq "ballot-list"}active{/if}">
                        <i class="nav-icon fas fa-list-ol"></i>
                        <p>Mes consultations</p>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/ballot" class="nav-link bg-danger bg-cp-red">
                        <i class="nav-icon fas fa-plus-square"></i>
                        <p>Nouvelle consultation</p>
                    </a>
                </li>
                <li class="nav-header">CONFIGURATION</li>
                {if false}
                    <li class="nav-item">
                        <a href="/profile" class="nav-link {if $menu_actif eq "profile"}active{/if}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Mon compte</p>
                        </a>
                    </li>
                {/if}
                <li class="nav-item">
                    <a href="/asker-list" class="nav-link {if $menu_actif eq "asker"}active{/if}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profils organisateurs</p>
                    </a>
                </li>
                {if cp_user_is_admin()}
                    <li class="nav-header">SUPER ADMIN</li>
                    <li class="nav-item">
                        <a href="/admin-ballot-list" class="nav-link {if $menu_actif eq "admin-ballot-list"}active{/if}">
                            <i class="nav-icon fas fa-list-ol"></i>
                            <p>Admin. consultations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin-user-list" class="nav-link {if $menu_actif eq "admin-user"}active{/if}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Admin. Utilisateurs</p>
                        </a>
                    </li>
                {/if}
            </ul>
        </nav>
    </div>
</aside>
