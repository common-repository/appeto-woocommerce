<ion-view title="<?php echo $title;?>">

    <div ng-controller="WordpressAuth" ng-init="slug='<?php echo $slug;?>'; wpAjaxUrl='<?php echo admin_url( 'admin-ajax.php' );?>'">

        <ion-nav-bar class="bar-dark" align-title="center">

            <ion-nav-back-button ripple ripple-dark ripple-hold  class="button-icon icon ion-android-arrow-back custom-back">
            </ion-nav-back-button>

        </ion-nav-bar>

        <ion-content padding="false" class="login-theme-2 page-content <?php echo $class;?>">

            <div class="padding">
                <div class="text-box">
                    <form method="post" id="appetoWpAuth">
                        <ion-md-input>
                            <input type="text" ng-model="wpLoginInfo.username"  required>
                            <span class="md-highlight"></span>
                            <span class="md-bar"></span>
                            <label>نام کاربری</label>
                        </ion-md-input>

                        <ion-md-input>
                            <input type="email" ng-model="wpLoginInfo.email" required>
                            <span class="md-highlight"></span>
                            <span class="md-bar"></span>
                            <label>ایمیل</label>
                        </ion-md-input>
                        <br />
                        <br />
                        <p>تأیید نام‌نویسی به شما ایمیل خواهد شد.</p>
                        <input type="hidden" ng-model="wpLoginInfo.nonce" ng-init="wpLoginInfo.nonce='<?php echo wp_create_nonce("appeto_auth_register");?>'">
                        <div class="text-center">
                            <a ripple ripple-dark ripple-hold ng-click="wpRegister();" class="button button-balanced button-block">ثبت نام</a>
                        </div>
                    </form>
                    <br />
                    <p class="text-center">
                        آیا اکانت کاربری دارید؟
                        <b><a href="#/nav/<?php echo $slug;?>" style="text-decoration: underline; font-weight: bolder; font-size: 18px">ورود</a></b>
                    </p>
                </div>
            </div>


        </ion-content>

    </div>



</ion-view>
