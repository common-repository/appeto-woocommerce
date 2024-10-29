<?php
$appeto_check_active_controllers = explode(',', get_option('json_api_controllers', 'core'));
if(!in_array('appeto', $appeto_check_active_controllers)) {
    ?>
    <div class="error">
        <p>
            لطفا کنترل های اپتو را از بخش تنظیمات -> json api فعال کنید.
        </p>
    </div>
<?php
}
?>
<div class='wrap'>
    <h2>تنظیمات نوتیفیکیشن</h2><br/>
    <ul class='subsubsub'>
<!--        <li class='all'><a href='admin.php?page=appeto-settings' <?php /*if(!isset($_GET["setting"])) {echo 'class="current"';}*/?>>تنظیمات نوتیفیکیشن چشمک</a> |</li>
-->
<!--        <li class='active'><a href='admin.php?page=appeto-settings&setting=pushe' <?php /*if(isset($_GET["setting"]) and $_GET["setting"] == "pushe") {echo 'class="current"';}*/?>>تنظیمات نوتیفیکیشن پوشه</a> |</li>
--><!--        <li class='active'><a href='admin.php?page=appeto-settings&setting=iOS' <?php /*if(isset($_GET["setting"]) and $_GET["setting"] == "iOS") {echo 'class="current"';}*/?>>تنظیمات نوتیفیکیشن iOS </a></li>
-->    </ul>
    <br />
    <br />
    <hr />
    <?php
    if(isset($_GET["setting"]) and $_GET["setting"] == "iOS") {

        if(isset($_POST['save_appeto_onesignal']) and isset($_GET['_nonce'])) {
            if(wp_verify_nonce($_GET['_nonce'], 'appeto_save_settings')) {
                update_option('appeto_onesignal_token', sanitize_text_field($_POST['appeto_onesignal_token']));
                update_option('appeto_onesignal_app_id', sanitize_text_field($_POST['appeto_onesignal_app_id']));
                echo '<div class="updated">
                        <strong>
                        تغییرات ذخیره شد.
                        </strong>
                    </div>';
            }
        }

        $appeto_onesignal_token = esc_attr(get_option('appeto_onesignal_token', ''));
        $appeto_onesignal_app_id = esc_attr(get_option('appeto_onesignal_app_id', ''));
    ?>
        <div class="updated">
            <p>
                برای دریافت اطلاعات این بخش از پنل اپتو، اپلیکیشن خود را انتخاب کنید و در افزونه "نوتفیکیشن iOS" اطلاعات را پیدا کرده و در این بخش وارد کنید.
            </p>
        </div>

        <form action="admin.php?page=appeto-settings&setting=iOS&_nonce=<?php echo wp_create_nonce('appeto_save_settings')?>" method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="appeto_onesignal_token">Api Key</label>
                        </th>
                        <td>
                            <input type="text" value="<?php echo $appeto_onesignal_token;?>" id="appeto_onesignal_token" name="appeto_onesignal_token" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="appeto_cheshmak_token">Auth Key</label>
                        </th>
                        <td>
                            <input type="text" value="<?php echo $appeto_onesignal_app_id;?>" id="appeto_onesignal_app_id" name="appeto_onesignal_app_id" class="regular-text">
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="save_appeto_onesignal" id="submit" class="button button-primary" value="ذخیره‌ی تغییرات">
            </p>
        </form>
    <?php
    }
    else if(isset($_GET["setting"]) and $_GET["setting"] == "pushe") {
        if(isset($_POST['save_appeto_pushe']) and isset($_GET['_nonce'])) {
            if(wp_verify_nonce($_GET['_nonce'], 'appeto_save_settings')) {
                update_option('appeto_pushe_notify_key', sanitize_text_field($_POST['appeto_pushe_notify_key']));
                update_option('appeto_pushe_package', sanitize_text_field($_POST['appeto_pushe_package']));
                echo '<div class="updated">
                        <strong>
                        تغییرات ذخیره شد.
                        </strong>
                    </div>';
            }
        }
        $appeto_pushe_notify_key = esc_attr(get_option('appeto_pushe_notify_key', ''));
        $appeto_pushe_package = esc_attr(get_option('appeto_pushe_package', ''));
    ?>
        <div class="updated">
            <p>
                لطفا برای استفاده از نوتیفیکیشن پوشه در سایت وردپرسی خود از لینک زیر توکن مورد نظر را دریافت کنید و در بخش زیر کپی کنید. مشاهده توکن شما: <br /><a href="http://panel.pushe.co/profile" target="_blank">http://panel.pushe.co/profile</a>
            </p>
        </div>
        <form action="admin.php?page=appeto-settings&setting=pushe&_nonce=<?php echo wp_create_nonce('appeto_save_settings')?>" method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="appeto_pushe_package">اسم پکیج برنامه</label>
                        </th>
                        <td>
                            <input type="text" value="<?php echo $appeto_pushe_package;?>" id="appeto_pushe_package" name="appeto_pushe_package"  class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="appeto_pushe_notify_key">توکن برای ارسال نوتیفیکیشن</label>
                        </th>
                        <td>
                            <input type="text" value="<?php echo $appeto_pushe_notify_key;?>" id="appeto_pushe_notify_key" name="appeto_pushe_notify_key"  class="regular-text">
                        </td>
                    </tr>
                </tbody>

            </table>
            <p class="submit">
                <input type="submit" name="save_appeto_pushe" id="submit" class="button button-primary" value="ذخیره‌ی تغییرات">
            </p>

        </form>

    <?php
    }
    else {
        if(isset($_POST['save_appeto_pushe']) and isset($_GET['_nonce'])) {
            if(wp_verify_nonce($_GET['_nonce'], 'appeto_save_settings')) {
                update_option('appeto_pushe_notify_key', sanitize_text_field($_POST['appeto_pushe_notify_key']));
                update_option('appeto_pushe_package', sanitize_text_field($_POST['appeto_pushe_package']));
                echo '<div class="updated">
                        <strong>
                        تغییرات ذخیره شد.
                        </strong>
                    </div>';
            }
        }
        $appeto_pushe_notify_key = esc_attr(get_option('appeto_pushe_notify_key', ''));
        $appeto_pushe_package = esc_attr(get_option('appeto_pushe_package', ''));
        ?>
        <div class="updated">
            <p>
                لطفا برای استفاده از نوتیفیکیشن پوش پل در سایت وردپرسی خود از لینک زیر توکن مورد نظر را دریافت کنید و در بخش زیر کپی کنید. مشاهده توکن شما: <br /><a href="https://console.push-pole.com/" target="_blank">https://console.push-pole.com/</a>
            </p>
        </div>
        <form action="admin.php?page=appeto-settings&setting=pushe&_nonce=<?php echo wp_create_nonce('appeto_save_settings')?>" method="post">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="appeto_pushe_package">اسم پکیج برنامه</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_pushe_package;?>" id="appeto_pushe_package" name="appeto_pushe_package"  class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="appeto_pushe_notify_key">توکن برای ارسال نوتیفیکیشن پوشه</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_pushe_notify_key;?>" id="appeto_pushe_notify_key" name="appeto_pushe_notify_key"  class="regular-text">
                    </td>
                </tr>
                </tbody>

            </table>
            <p class="submit">
                <input type="submit" name="save_appeto_pushe" id="submit" class="button button-primary" value="ذخیره‌ی تغییرات">
            </p>

        </form>

        <?php
    }
?>
</div>