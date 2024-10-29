<div class='wrap' style="background-color: #ffffff; padding: 20px">
    <h2>تنظیمات حساب کاربری وردپرس</h2><br/>
    <p>در صورتی که از افزونه حساب کاربری وردپرس در اپلیکیشن خود استفاده میکنید و نیاز دارید که صفحات سایت خود که نیاز به ورود دارند در افزونه صفحه وب یا مرورگر داخلی اپلیکیشن نمایش دهید، تنظیمات زیر را وارد فایل
     .htaccess
    سایت خود کنید. ( برای این کار کد زیر را به آخر فایل اضافه کنید. )</p>
    <textarea rows="4" style="width: 100%; text-align: left; direction: ltr" readonly="readonly">&lt;IfModule mod_headers.c&gt;
    Header unset X-Frame-Options
    Header always unset X-Frame-Options
&lt;/IfModule&gt;</textarea>
</div>