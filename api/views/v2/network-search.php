<ion-view title="جستجو" cache-view="false">

    <ion-content padding="false" ng-controller="WoocommerceNetworkSearchCtrl" class="<?php echo " woocommerce category ";?> "
                 ng-init="localUrl='<?php echo $extra->localUrl;?>';
                 localCk='<?php echo $extra->localCk;?>';
                 localCs='<?php echo $extra->localCs;?>';
                 sites_id='<?php echo implode(",", $extra->sites);?>';
                 q='';
                 currency_symbol='<?php echo get_woocommerce_currency_symbol();?>';
                 "
        >
        <div class="list list-inset pdding">
            <div class="row">
                <div class="col-75 wooNetworkSearchBox">
                    <label class="item item-input">
                        <input type="text" ng-model="searchQuery" ng-init="searchQuery=''" />
                    </label>
                    <button type="button" class="button button-icon icon ion-search wooDoSearchBtn" ng-click="wooInnerSearch()"></button>
                </div>
                <div class="col-25">
                    <select class="wooNetworkSearch" ng-model="showType">
                        <option value="p">نمایش محصول</option>
                        <option value="s">نمایش فروشگاه</option>
                    </select>
                </div>
            </div>
        </div>

        <div ng-show="products.length <= 0 && showNotFound">
            <div class="transparent woocommerce-product padding-content" style="text-align: center">محصولی یافت نشد.</div>
        </div>

        <div ng-show="products.length > 0 && !showNotFound" style="margin-top: 15px">

            <div class="row" ng-repeat="baseProduct in products" ng-show="showType == 'p'">
                <div class="col col-50" ng-repeat="product in baseProduct.products">
                    <a  ng-click="setProductSite(product.site, product.appLink);" style="text-decoration: none">
                        <div class="card product">
                            <div class="imageBox" ng-if="product.img" style="background: url('{{ product.img }}') no-repeat center center;
													  -webkit-background-size: cover;
													  -moz-background-size: cover;
													  -o-background-size: cover;
													  background-size: cover;
													  position: relative;">
                                <p style="visibility: hidden">&nbsp;</p>
                                <img ng-if="product.siteImage" ng-src="{{ product.siteImage }}" style="width: 42px; height: 42px; position: absolute; top: 10px; left: 10px;"/>
                            </div>
                            <div class="item item-body" style="background-color: #ffffff;">
                                <h3 class="garage-title" style="color: #222222 !important;">
                                    {{ product.title }}
                                </h3>
                                <span style="color: #222222 !important; text-align: justify">
                                    <i class="ion-pricetag second-color"></i> <span class="second-color">{{ product.price }} {{ currency_symbol }}</span>
                                    <br ng-if="product.regular_price!=product.price && product.regular_price!=''" /><i class="ion-pricetag first-color" ng-if="product.regular_price!=product.price && product.regular_price!=''"></i> <span ng-if="product.regular_price!=product.price && product.regular_price!=''" style="text-decoration: line-through" class="first-color">{{ product.regular_price }} {{ currency_symbol }}</span>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="list no-shadow" style="margin-top: 15px" ng-show="showType == 's'">
                <div  ng-repeat="site in sites" class="item woo-category-box cards-shadow woo-st-{{ site.id }}" ng-class="site.image != '' ? 'item-thumbnail-right' : ''"  ng-click="switchSite('network-<?php echo $extra->theme;?>', site.applink, site.id, site.siteurl, site.name)" >
                    <img ng-src="{{ site.image }}" ng-if="site.image != ''" />
                    <h2>{{ site.name }}</h2>
                </div>
            </div>



        </div>

    </ion-content>
</ion-view>