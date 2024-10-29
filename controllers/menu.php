<?php
class appeto_admin_menu
{
    static function settings()
    {
        $path = plugin_dir_path(dirname(__FILE__));
        include($path.'views/settings.php');
    }

    static function push()
    {
        $path = plugin_dir_path(dirname(__FILE__));
        include($path.'views/push.php');
    }

    static function networkWoo() {
        $path = plugin_dir_path(dirname(__FILE__));
        include($path.'views/wooNetwork.php');
    }

    static function auth()
    {
        $path = plugin_dir_path(dirname(__FILE__));
        include($path.'views/auth.php');
    }

    static function appetoWooPage() {
        appeto_change_location("admin.php?page=wc-settings&tab=settings_tab_appeto");
    }
    static function appetoJsonApi() {
        appeto_change_location("options-general.php?page=json-api");
    }
    static function appetoWpCors() {
        appeto_change_location("options-general.php?page=wp-cors");
    }

    public static function appeto_add_menu_item()
    {
        add_menu_page("اپتو", "اپلیکیشن", '', 'appeto/controllers/menu.php', '',   plugins_url('assets/img/appeto.png', dirname(__FILE__)), 99);

        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'تنظیمات',
            'تنظیمات',
            'manage_options', // permissions
            'appeto-settings', // page-name (used in the URL)
            'appeto_admin_menu::settings' // clicking callback function
        );

        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'ارسال نوتیفیکیشن',
            'ارسال نوتیفیکیشن',
            'manage_options', // permissions
            'appeto-push', // page-name (used in the URL)
            'appeto_admin_menu::push' // clicking callback function
        );
        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'ووکامرس',
            'ووکامرس',
            'manage_options', // permissions
            'appeto-woo', // page-name (used in the URL)
            'appeto_admin_menu::appetoWooPage' // clicking callback function
        );
        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'وردپرس',
            'وردپرس',
            'manage_options', // permissions
            'appeto-json-api', // page-name (used in the URL)
            'appeto_admin_menu::appetoJsonApi' // clicking callback function
        );
        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'حساب کاربری وردپرس',
            'حساب کاربری وردپرس',
            'manage_options', // permissions
            'appeto-auth-settings', // page-name (used in the URL)
            'appeto_admin_menu::auth' // clicking callback function
        );
        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'دسترسی ها',
            'دسترسی ها',
            'manage_options', // permissions
            'appeto-wp-cors', // page-name (used in the URL)
            'appeto_admin_menu::appetoWpCors' // clicking callback function
        );
        add_submenu_page(
            'appeto/controllers/menu.php', // Menu page to attach to #options-general.php
            'ووکامرس شبکه ای',
            'ووکامرس شبکه ای',
            'manage_options', // permissions
            'appeto-network-woo', // page-name (used in the URL)
            'appeto_admin_menu::networkWoo' // clicking callback function
        );
    }
}