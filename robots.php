<?
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: text/txt');

//$protocol = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$protocol = (CMain::IsHTTPS()) ? "https" : "http";
$host = $_SERVER['SERVER_NAME'];
$adress = $protocol.'://'.$_SERVER['SERVER_NAME'];

?>
User-Agent: *
Disallow: # empty Disallow instruction SHOULD be there
Disallow: /ajax/callback.php
Allow: /search/map.php
Allow: /search/*
Disallow: *MANUFACTURER_ID*
Disallow: *order=desc*
Disallow: *sort=price*
Disallow: *sort=name*
Disallow: /kras/
Disallow: /kem/
#Disallow: /club/group/search/
#Disallow: /club/forum/search/
#Disallow: /communication/forum/search/
#Disallow: /communication/blog/search.php
#Disallow: /*/search/
#Disallow: /*PAGE_NAME=search
Disallow: *?version=*
Disallow: /bitrix/
Disallow: /upload/
Disallow: /e-store/buy.php
Disallow: /club/gallery/tags/
Disallow: /examples/my-components/
Disallow: /examples/download/download_private/
Disallow: /auth/
Disallow: /auth.php
Disallow: /personal/
Disallow: /communication/forum/user/
Disallow: /e-store/paid/detail.php
Disallow: /e-store/affiliates/
Disallow: /club/$
Disallow: /club/messages/
Disallow: /club/log/
Disallow: /content/board/my/
Disallow: /content/links/my/
Disallow: /*PAGE_NAME=user_post
Disallow: /*PAGE_NAME=detail_slide_show
Disallow: /*/slide_show/
Disallow: /*/gallery/*order=*
Disallow: /*?print=
Disallow: /*&print=
Disallow: /*register=yes
Disallow: /*forgot_password=yes
Disallow: /*change_password=yes
Disallow: /*login=yes
Disallow: /*logout=yes
Disallow: /*auth=yes
Disallow: /*action=ADD_TO_COMPARE_LIST
Disallow: /*action=DELETE_FROM_COMPARE_LIST
Disallow: /*action=ADD2BASKET
Disallow: /*action=BUY
Disallow: /*print_course=Y
Disallow: /*bitrix_*=
Disallow: /*backurl=*
Disallow: /*BACKURL=*
Disallow: /*back_url=*
Disallow: /*BACK_URL=*
Disallow: /*back_url_admin=*
Disallow: /*index.php$
Sitemap: <?=$adress?>/sitemap.xml