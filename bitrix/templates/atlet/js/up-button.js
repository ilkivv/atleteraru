$(document).ready(function () {
    var headerHeight = 190;
    var $winH = $(window).height();
    var $scrollT = $(window).scrollTop();
    var $up = $('.j-up-button');
    var $callBack = $('.j-callback-btn');
    var STEP;
    var $winW = $(window).width();

    if ($winW < 1421) {
        STEP = 150;
    } else {
        STEP = 100;
    }

    var callBackBottom = parseInt($callBack.css('bottom'));
    var showBtn = function () {
console.log('show');
        $callBack.css({transform: 'translateY(-' + STEP + 'px'});
        $up.fadeIn();

    };
    var hideBtn = function () {
console.log('show');
        $callBack.css({transform: 'translateY(0'});
        $up.fadeOut();
    };




    $scrollT = $(window).scrollTop();
    if($scrollT > $winH) {
        showBtn();
    } else if ($scrollT < $winH){
        hideBtn();

    }
    if ($scrollT > headerHeight) {
        $('#header').addClass('_fixed');
    } else {
        $('#header').removeClass('_fixed');
    }

    $(window).on('scroll', function () {
        $scrollT = $(window).scrollTop();
        if($scrollT > $winH) {
            showBtn();
        } else if ($scrollT < $winH){
            hideBtn();

        }
        if ($scrollT > headerHeight) {
            $('#header').addClass('_fixed');
        } else {
            $('#header').removeClass('_fixed');
        }
    });

    $up.on('click', function () {
        $('body,html').animate({scrollTop:0},800);
    });
});
