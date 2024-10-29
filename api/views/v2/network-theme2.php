<?php
$current_blog_details = get_blog_details( array( 'blog_id' => $extra->id ) );
$blog_id = $extra->id;
$ck = get_option("appeto_ck_".$blog_id, "");
$cs = get_option("appeto_cs_".$blog_id, "");
$localCk = get_option("appeto_localCk_".$blog_id, "");
$localCs = get_option("appeto_localCs_".$blog_id, "");
$woocommerce_api = $current_blog_details->siteurl."/";
$currency = get_woocommerce_currency_symbol();
$hashKey = get_option('appeto_secure_key_woo');
$ck = base64_encode(appeto_encode_this_key($ck, $hashKey));
$cs = base64_encode(appeto_encode_this_key($cs, $hashKey));
?>
<ion-view cache-view="false">

    <div ng-controller="WoocommerceCategoriesCtrl"
         ng-init="network=true; wooCurrentNetworkSiteUrl='<?php echo  $woocommerce_api ?>'; wooCurrentNetworkSiteName='<?php echo $current_blog_details->blogname?>'; wooCurrentNetworkSiteId=<?php echo $blog_id;?>; localUrl = '<?php echo  $woocommerce_api ?>'; ck='<?php echo  $ck ?>'; cs='<?php echo  $cs ?>'; content_classes=''; layout=''; localCs='<?php echo  $localCs ?>'; localCk='<?php echo  $localCk ?>';  load_categories();">

        <ion-nav-bar class="bar-dark card-bar" align-title="center">

            <ion-nav-back-button ripple ripple-dark ripple-hold  class="button-icon icon ion-android-arrow-back custom-back">
            </ion-nav-back-button>

            <ion-nav-buttons side="right">
                <button ripple ripple-dark ripple-hold ng-click="doSearch();" class="button button-icon wooSearchIconTop" ng-class="isSearchOpen ? 'ion-android-close' : 'ion-ios-search-strong'"></button>
                <button ripple ripple-dark ripple-hold class="button button-icon ion-android-cart woocommerce-card">
                    <span class="woocommerce-card-count second-color-bg"><i>0</i></span>
                </button>
            </ion-nav-buttons>

        </ion-nav-bar>

        <ion-content padding="false">

            <div class="parallax-header color-header">

                <h3><?php echo $current_blog_details->blogname;?></h3>

            </div>

            <div class="transparent padding-content" ng-if="categories.length <= 0">
                <h4 class="text-center">در حال دریافت اطلاعات</h4>
            </div>


            <div class="card-to-header" ng-show="categories.length > 0">
                <div class="list no-shadow" style="margin-top: 0">
                    <a  ng-repeat="category in categories" href="#/nav/woocommerce-v2/category/{{ category.applink }}" class="item woo-category-box cards-shadow woo-ct-{{ category.id }}" ng-class="category.image != '' ? 'item-thumbnail-right' : ''" >
                        <img ng-src="{{ category.image }}" ng-if="category.image != ''" />
                        <h2>{{ category.name }}</h2>
                        <span class="badge badge-assertive" ng-class="category.image != '' ? '' : 'no-img'">{{ category.count }}</span>
                    </a>
                </div>
            </div>


            <div ng-controller="WoocommerceCtrl" ng-init="network=true; wooCurrentNetworkSiteUrl='<?php echo  $woocommerce_api ?>'; per_page=10; newProductsLoader=true; baseShowUrl='<?php echo  $woocommerce_api ?>/?appeto_api=woocommerce'; ck='<?php echo  $ck ?>'; cs='<?php echo  $cs ?>'; content_classes=''; layout=''; localCs='<?php echo  $localCs ?>'; localCk='<?php echo  $localCk ?>'; localUrl='<?php echo  $woocommerce_api ?>'; currency_symbol='<?php echo  $currency ?>';">


                <div class="woocommerce wooNewProducts">
                    <div class="row">
                        <div class="col col-100">
                            <h4>جدیدترین محصولات
                             <span ng-show="products.length <= 0" style="font-size: 12px" > -
                                 در حال دریافت اطلاعات
                             </span>
                            </h4>
                        </div>
                    </div>
                    <ion-scroll direction="x" class="wide-as-needed" ng-show="products.length > 0">
                        <div class="row">
                            <div class="col col-40" ng-repeat="product in newProducts">
                                <a href="#/nav/woocommerce-v2/product/{{  product.appLink }}" style="text-decoration: none">
                                    <div class="card product">
                                        <div class="imageBox" ng-if="product.images[0].src" style="background: url('{{ product.images[0].src }}') no-repeat center center;
                                                -webkit-background-size: cover;
                                                -moz-background-size: cover;
                                                -o-background-size: cover;
                                                background-size: cover;">
                                            <p style="visibility: hidden">&nbsp;</p>
                                        </div>
                                        <div class="item item-body">
                                            <h3 class="garage-title">
                                                {{ product.title }}
                                            </h3>
                                            <span style="text-align: justify">
                                                <i class="ion-pricetag second-color"></i> <span class="second-color">{{ product.price }} {{ currency_symbol }}</span>
                                                <br ng-if="product.regular_price!=product.price && product.regular_price!=''" /><i class="ion-pricetag first-color" ng-if="product.regular_price!=product.price && product.regular_price!=''"></i> <span ng-if="product.regular_price!=product.price && product.regular_price!=''" style="text-decoration: line-through" class="first-color">{{ product.regular_price }} {{ currency_symbol }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </ion-scroll>
                </div>

            </div>


        </ion-content>


    </div>

</ion-view>