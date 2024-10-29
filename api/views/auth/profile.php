<ion-view title="<?php echo $title;?>">

    <div ng-controller="WordpressAuth" ng-init="slug='<?php echo $slug;?>'; wpAjaxUrl='<?php echo admin_url( 'admin-ajax.php' );?>'">

        <ion-nav-bar class="bar-dark" align-title="center">

            <ion-nav-back-button ripple ripple-dark ripple-hold  class="button-icon icon ion-android-arrow-back custom-back">
            </ion-nav-back-button>

            <ion-nav-buttons side="right">
                <?php
                if($layout != 'tabs-bottom' and $layout != 'tabs-top') {
                    $toggle = ($layout == 'side-right' || $layout == 'side-right-little') ? 'right': 'left';
                    echo '<button ripple ripple-dark ripple-hold  class="button button-icon ion-navicon" menu-toggle="'.$toggle.'"></button>';
                }
                ?>
            </ion-nav-buttons>

        </ion-nav-bar>

        <ion-content padding="false" class="login-theme-2 page-content <?php echo $class;?>">

            <div class="padding">
                <div class="text-box">
                    <?php
                    $user_info = get_userdata($user);
                    ?>
                    <h3>خوش آمدید
                    <?php echo $user_info->display_name;?>
                    </h3>
                    <ion-md-input>
                        <input type="text" readonly="readonly" value="<?php echo $user_info->user_login;?>">
                        <span class="md-highlight"></span>
                        <span class="md-bar"></span>
                        <label>نام کاربری</label>
                    </ion-md-input>

                    <ion-md-input>
                        <input type="text" readonly="readonly" value="<?php echo $user_info->user_email;?>">
                        <span class="md-highlight"></span>
                        <span class="md-bar"></span>
                        <label>ایمیل</label>
                    </ion-md-input>
                    <br />
                    <button style="background-color: #F44336; color: #ffffff;" class="button button-block" ng-click="wpLogout('<?php echo wp_create_nonce("appeto_auth_logout")?>')">
                        <i class="ion-log-out"></i>
                        خروج از حساب کاربری
                    </button>

                </div>
            </div>

        </ion-content>

    </div>



</ion-view>
