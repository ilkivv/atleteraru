$(document).ready(function(){

    // Tabs
    $('.j-tabs').each(function(){
        new Tabs(this);
    });

    // Carousel
    $('.j-carousel').each(function(){
        var cmp = $(this);
        cmp.carouFredSel({
            items: {
                visible: "variable"
            },
            scroll : {
                items           : 1,
                duration        : 200
            },
            auto: {
                play: false
            },
            prev: {
                button: ".j-carousel-prev-" + cmp.data('suffix')
            },
            next: {
                button: ".j-carousel-next-" + cmp.data('suffix')
            }
        });
    });

    // Media
    enquire.register("only screen and (min-width : 1421px)",{
        match: function() {
            $('.j-carousel').trigger('updateSizes');
            $.event.trigger({type: "recalculatePosition"});
        },
        unmatch: function() {
            $('.j-carousel').trigger('updateSizes');
            $.event.trigger({type: "recalculatePosition"});
        }
    });
    enquire.register("only screen and (min-width : 1656px)",{
        match: function() {
            $('.j-carousel').trigger('updateSizes');
            $.event.trigger({type: "recalculatePosition"});
        },
        unmatch: function() {
            $('.j-carousel').trigger('updateSizes');
            $.event.trigger({type: "recalculatePosition"});
        }
    });

    // Taste chooser
    $('.j-choose-taste').each(function(){
        new TasteChooser(this);
    });

    // Ajax popup
    if($('.j-quantity').length > 0) {
        new AjaxPopup();
    }

    // Auth
    if($('.j-auth').length > 0) {
        new Auth();
    }

    // Discrete input
    $('.j-discrete-input').each(function(){
        new DiscreteInput($(this));
    });

    // Spoiler
    $('.j-spoiler').each(function(){
        new Spoiler($(this));
    });

    // Change password
    $('.j-change-pass, .j-delete-profile').on("click", function(e){
        e.preventDefault();
        $(this).next().toggleClass('opened');
    });
    $('.j-cancel-pass').on("click", function(e){
        e.preventDefault();
        $(this).parents('.j-change-password-popup').toggleClass('opened');
    });
    $('.j-cancel-profile').on("click", function(e){
        e.preventDefault();
        $(this).parents('.j-delete-profile-popup').toggleClass('opened');
    });
    $(document).on("click", function(e){
        var target = $(e.target);
        if(!target.is('.j-change-password-popup') && !target.is('.j-change-pass') && target.parents('.j-change-password-popup').length == 0) {
            $('.j-change-password-popup').removeClass('opened');
        }
        if(!target.is('.j-delete-profile-popup') && !target.is('.j-delete-profile') && target.parents('.j-delete-profile-popup').length == 0) {
            $('.j-delete-profile-popup').removeClass('opened');
        }
    });

    // Profile
    $('.j-profile-field').keyup("change", function(){
        if(!$('.j-cancel-profile').hasClass('vis')) {
            $('.j-cancel-profile').addClass('vis');
        }
    });

    // Activate bonus card
    $('.j-bonus-field').on("keyup", function(){
        if($(this).val() != '') {
            $('.j-bonus-activating').fadeIn(300);
        } else {
            $('.j-bonus-activating').fadeOut(300);
        }
    });

    // Basket
    $('.j-basket-delete').on("click", function(e){
        e.preventDefault();
        $(this).parents('tr').fadeOut(300, function(){
            $(this).remove();
        });
    });

    // Hash tabs
    var hash = window.location.hash;
    var tab = $('[rel="' + hash + '"] a');
    if(tab.length > 0) {
        tab.trigger("click");
    }

    // Comments
    if($('.j-comment-form-container').length > 0) {
        new Comments();
    }
    
	$('.to-basket').on('click',function(){
		var container_id = $(this).attr('id').replace('_buy_link','');
    	var params = {
    			quantity:$('#'+container_id+'_quantity').val(),
    			offer_prop:$('#'+container_id+'_prop').data('id'),
    			offer_prop_value: $('#'+container_id+'_prop').data('value'),
    			itemId:$('#'+container_id).attr('rel'),
    			section:$('#'+container_id).data('value'),
    			data:'json'
    	};
    	$.post('/e-store/buy.php',params,function(resp){
    		if (resp.submitOn) {
    			if (resp.quantity) {
    				$('#'+container_id).find('.in-basket').html('<a href="/personal/cart/">В корзине ('+resp.quantity+')</a> <span class="j-remove-item">Убрать 1</span>');
    				$('#'+container_id).find('.in-basket').show();
    			}
    			if (!$('#'+container_id).find('.in-basket').length) {
    				if ($('.j-flash-message-container')) {
    					FlashMessage.Show('Товар добавлен в корзину');
    				}
    			}  
    		} else {
    			if (resp.text) {
					//$('#'+container_id).find('.j-discrete-input').val(resp.quantity);
    				$('#'+container_id).find('.j-error').html(resp.text).show();
    				if (!$('#'+container_id).find('.in-basket').length) {
        				if ($('.j-flash-message-container')) {
        					FlashMessage.Show(resp.text);
        				}
        			}
    			}
    		}
    		$.post('/e-store/basket.php',{},function(response){$('#smallbasket').html(response);});
    	});
    	//document.location = '/e-store/'+params.section+'/'+params.itemId+'/?action=BUY&id='+params.offer_prop_value;
    	return false;
    });
	$(document).on('click', '.j-remove-item', function(){
		var container_id = $(this).parents('.catalog-item').attr('id');
		var params = {
    			quantity:$('#'+container_id+'_quantity').val(),
    			offer_prop:$('#'+container_id+'_prop').data('id'),
    			offer_prop_value: $('#'+container_id+'_prop').data('value'),
    			itemId:$('#'+container_id).attr('rel'),
    			section:$('#'+container_id).data('value'),
    			data:'json'
    	};
		$.post('/e-store/remove.php',params,function(resp){
			$.post('/e-store/basket.php',{},function(response){$('#smallbasket').html(response);});
			if (resp.submitOn) {
    			if (resp.quantity && resp.quantity >0) {
    				$('#'+container_id).find('.in-basket').html('<a href="/personal/cart/">В корзине ('+resp.quantity+')</a> <span class="j-remove-item">Убрать 1</span>');
    				$('#'+container_id).find('.in-basket').show();
    			}else {
    				$('#'+container_id).find('.in-basket').hide();
    			}
    		}
		});
	});
    
    // Shop block
    var shopBlockInfo = $('.j-shops-block .shops-info');
    var shopBlockCount = $('.j-shops-block .products-count');
    $('[rel="shops-product-tab"]').on("click", function(){
        if(shopBlockInfo.length > 0) {
            var shopBlockInfoHeight = new Array();
            var shopBlockCountHeight = new Array();
            shopBlockInfo.each(function(){
                shopBlockInfoHeight.push($(this).height());
            });
            shopBlockInfo.height(Math.max.apply(Math, shopBlockInfoHeight));
            shopBlockCount.each(function(){
                shopBlockCountHeight.push($(this).height());
            });
            shopBlockCount.height(Math.max.apply(Math, shopBlockCountHeight));
        }
    });

    // Ajax form
    bindAjaxForm();
    
    $(window).load(function(){
    	$('.j-choose-taste-link').each(function(){
			var inBasket = $(this).parents('.catalog-item').find('.in-basket');
			inBasket.hide();
			if (SKU_IN_BASKET[$(this).data('value')] >0) {
				inBasket.show().html('<a href="/personal/cart/">В корзине (' + SKU_IN_BASKET[$(this).data('value')] + ')</a> <span class="j-remove-item">Убрать 1</span>');
			}	
    	});
    });
});

/* Auth */
Auth = function () {
    this.container = $('.j-auth-container');
    this.link = this.container.find('.j-auth');
    this.popup = this.container.find('.j-auth-popup');
    this.auth = this.container.find('.j-auth-block');
    this.recover = this.container.find('.j-recover-block');
    this.showRecover = this.container.find('.j-show-recover');
    this.cancelRecover = this.container.find('.j-cancel-recover');
    this.successRecover = this.container.find('.j-success-recover');
    this.getPass = this.container.find('.j-get-pass');
    this.success = this.container.find('.j-success-block');

    this.init();
};

Auth.prototype.init = function () {
    var cmp = this;

    $(document).on("click", function(e){
        var target = $(e.target);
        if(!target.is('.j-auth-popup') && !target.is('.j-auth') && target.parents('.j-auth-popup').length == 0) {
            cmp.popup.removeClass('opened');
        }
    });

    cmp.link.on("click", function(e){
        e.preventDefault();

        if(!cmp.popup.hasClass('opened')) {
            cmp.auth.show();
            cmp.recover.hide();
            cmp.success.hide();
        }
        cmp.popup.toggleClass('opened');

    });

    cmp.showRecover.on("click", function(e){
        e.preventDefault();

        cmp.auth.hide();
        cmp.recover.fadeIn(300);
    });

    cmp.cancelRecover.on("click", function(e){
        e.preventDefault();

        cmp.recover.hide();
        cmp.auth.fadeIn(300);
    });

    cmp.successRecover.on("click", function(e){
        e.preventDefault();

        cmp.popup.removeClass('opened');
    });

    cmp.getPass.on("click", function(e){
        e.preventDefault();

        cmp.recover.hide();
        cmp.success.fadeIn(300);
    });
}
/* /Auth */

/* Tabs */
Tabs = function (container) {
    this.container = $(container);
    this.tabs = this.container.find('.j-tabs-links a');
    this.tabBlock = this.container.find('.j-tabs-body');

    this.hash = window.location.hash;
    
    this.init();
};

Tabs.prototype.init = function () {
    var cmp = this;

    cmp.tabs.click(function(e){
        e.preventDefault();
        if(!$(this).hasClass('active')) {
            cmp.tabs.removeClass('active');
            $(this).addClass('active');
            cmp.tabBlock.hide();
            $('#' + $(this).attr('rel')).fadeIn(300);
        }
    });
    
    if(cmp.hash) {
    	cmp.hash = cmp.hash.replace('#', '');
    	var activeTab = cmp.tabs.filter('[rel="' + cmp.hash + '"]');
    	if(activeTab.length > 0) {
    		activeTab.trigger('click');
    	}
    }
}
/* /Tabs */

/* Spoiler */
Spoiler = function (container) {
    this.container = $(container);
    this.link = this.container.find('.j-spoiler-link');
    this.body = this.container.find('.j-spoiler-body');

    this.init();
};

Spoiler.prototype.init = function () {
    var cmp = this;

    cmp.link.on("click", function(){
        cmp.container.toggleClass('opened');
    });
}
/* /Spoiler */

/* Discrete input */
DiscreteInput = function (input) {
    this.input = $(input);

    this.input
        .wrap('<div class="discrete-input"></div>')
        .before('<span class="discrete-dec"></span>')
        .after('<span class="discrete-inc"></span>');

    this.container = this.input.parent();
    this.dec = this.input.prev();
    this.inc = this.input.next();

    if(this.input.hasClass('disabled')) {
        this.container.addClass('disabled');
    }

    if(this.input.data('big')) {
        this.container.addClass('big-discrete-input');
    }

    this.init();
};

DiscreteInput.prototype.init = function () {
    var cmp = this;

    cmp.dec.on("click", function(){
        if(!cmp.input.hasClass('disabled')) {
            var val = parseInt(cmp.input.val(), 10) - 1;
            cmp.input.val(val < 1 ? 1 : val);
            cmp.input.trigger('change');
        }
    });

    cmp.inc.on("click", function(){
        if(!cmp.input.hasClass('disabled')) {
            cmp.input.val(parseInt(cmp.input.val(), 10) + 1);
            cmp.input.trigger('change');
        }
    });

    cmp.input.attr('disabled', 'disabled');

    cmp.input.on("keydown", function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    }).on("keyup", function (e) {
            if (cmp.input.val().length == 0) {
                cmp.input.val(1);
            } else {
                var val = parseInt(cmp.input.val(), 10);
                if(isNaN(val) || val <= 0) {
                    cmp.input.val(1);
                } else {
                    cmp.input.val(val);
                }
            }

        });
}
/* /Discrete input */

/* Taste chooser */
TasteChooser = function (container) {
    this.container = $(container);
    this.link = this.container.find('.j-choose-taste-link');
    this.list = this.container.find('.j-choose-taste-list');
    this.input = this.container.find('.j-choose-taste-input');
    this.items = this.list.find('div.item');

    this.init();
};

TasteChooser.prototype.init = function () {
    var cmp = this;

    $(document).on("closeSelect", function(){
        cmp.container.removeClass('opened');
    });

    $(document).on("click", function(e){
        if(!$(e.target).is('.j-choose-taste') && $(e.target).parents('.j-choose-taste').length == 0) {
            cmp.container.removeClass('opened');
        }
    });

    cmp.link.on("click", function(e){
        e.preventDefault();
        if(!cmp.container.hasClass('disabled')) {
            if(cmp.container.hasClass('opened')) {
                cmp.container.removeClass('opened');
            } else {
                $.event.trigger({type: "closeSelect"});
                cmp.container.addClass('opened');
            }
        }
    });

    cmp.items.filter('[data-value="' + cmp.link.data('value') + '"]').hide();
    cmp.list.height((cmp.items.length - 1) * 31);

    cmp.items.on("click", function(e){
        cmp.items.show();
        $(this).hide();
        var dataVal = $(this).data('value');
        cmp.link.data('value', dataVal);
        $('span', cmp.link).text($(this).text());
        cmp.container.removeClass('opened');
        cmp.input.val(dataVal);
        if (cmp.container.hasClass('j-brand-selector')) {
        	cmp.changeBrand();
        } else {
        	cmp.changePrice();
        }
    });    	

    cmp.list.jScrollPane({autoReinitialise: true});
}
TasteChooser.prototype.changeBrand = function () {
	var cmp = this;
	$('.j-filter-form input[name="brand"]').val(cmp.link.data('value'));
	$('.j-filter-form').submit();
}

TasteChooser.prototype.changePrice = function () {
	var cmp = this;
	var objid = window['obbx_'+cmp.link.attr('id').replace('_prop',"").replace('bx_',"")];
	if (objid) {
		$.each(objid,function(i,elem){
			if(elem.ID == cmp.link.data('value')) {
				$('#'+cmp.link.attr('id').replace('_prop',"")).find('.in-basket').hide();
				if (SKU_IN_BASKET[elem.ID] >0) {
					$('#'+cmp.link.attr('id').replace('_prop',"")).find('.in-basket').show().html('<a href="/personal/basket/">В корзине ('+SKU_IN_BASKET[elem.ID]+')</a> <span class="j-remove-item">Убрать 1</span>');
				}
				$('#'+cmp.link.attr('id').replace('_prop',"")).find('.product-price strong,.new-price').html(elem.PRICE.PRINT_VALUE);
			}
		});
	}
}
/* /Taste chooser */

/* Ajax popup */
AjaxPopup = function () {
    this.container = $(this.getHtml());
    this.parent = $('#main');
    this.closeButton = this.container.find('.j-close-popup');
    this.contentBlock = this.container.find('.j-ajax-popup-content');
    this.linksCollection = $('.j-quantity');
    this.url = '/e-store/store.php?ID=';
    this.init();
};

AjaxPopup.prototype.init = function () {
    var cmp = this;

    var counter = 1;
    cmp.linksCollection.each(function(){
        $(this).attr('data-gid', counter);
        counter++;
    });

    cmp.parent.append(cmp.container);

    $(document).on("recalculatePosition", function(){
            if(cmp.container.hasClass('opened')) {
                cmp.recalculatePosition();
            }
        }).on("click", function(e){
            var target = $(e.target)
            if(!target.is('.j-ajax-popup') && !target.is('.j-quantity') && target.parents('.j-ajax-popup').length == 0) {
                cmp.close();
            }
        });

    cmp.linksCollection.on("click", function(e){
        e.preventDefault();

        var handler = $(this);
        if(cmp.container.hasClass('opened')) {
            cmp.close();
            if(cmp.container.data('gid') != handler.data('gid')) {
                setTimeout(function(){
                    cmp.open(handler);
                }, 300);
            }
        } else {
            cmp.open(handler);
        }
    });

    cmp.closeButton.on("click", function(e){
        e.preventDefault();
        cmp.close();
    });
}

AjaxPopup.prototype.recalculatePosition = function () {
    var cmp = this;

    var handler = cmp.linksCollection.filter('[data-gid="' + cmp.container.data('gid') + '"]');
    var x = parseInt(handler.offset().left, 10) - parseInt(cmp.parent.offset().left, 10) + parseInt(handler.width() / 2, 10) - parseInt(cmp.container.width() / 2, 10);
    var y = parseInt(handler.offset().top, 10) - parseInt(cmp.parent.offset().top, 10) - parseInt(cmp.container.height(), 10) - 15;

    if(x + parseInt(cmp.container.width() / 2, 10) > parseInt(cmp.parent.width(), 10)) {
        x = 0;
        cmp.close();
    }

    if(x + parseInt(cmp.container.width(), 10) > parseInt(cmp.parent.width(), 10)) {
        x =  parseInt(cmp.parent.width(), 10) - parseInt(cmp.container.width(), 10);
        cmp.container.addClass('right').removeClass('left');
    } else if(x < 0) {
        x = 0;
        cmp.container.removeClass('right').addClass('left');
    } else {
        cmp.container.removeClass('right').removeClass('left');
    }

    cmp.container.css({left: x + 'px', top: y + 'px'});
}

AjaxPopup.prototype.close = function () {
    var cmp = this;

    cmp.container.hide();
    cmp.container.removeClass('opened');
    setTimeout(function(){
        cmp.container.show();
    }, 300);
}

AjaxPopup.prototype.open = function (handler) {
    var cmp = this;
    
    cmp.contentBlock.html($('#tmp').html());  // Temp
    $.post(cmp.url+handler.parents('.catalog-item').attr('rel'),{},function(content){
    	cmp.contentBlock.html(content);
    	var slider = cmp.container.find('.j-shops-slider');
        if(slider.length > 0) {
            slider.carouFredSel({
                items: {
                    visible: 2,
                    height: "auto"
                },
                scroll : {
                    items           : 1,
                    duration        : 200
                },
                auto: {
                    play: false
                },
                prev: {
                    button: ".j-shop-prev"
                },
                next: {
                    button: ".j-shop-next"
                }
            });
        }

        var x = parseInt(handler.offset().left, 10) - parseInt(cmp.parent.offset().left, 10) + parseInt(handler.width() / 2, 10) - parseInt(cmp.container.width() / 2, 10);
        var y = parseInt(handler.offset().top, 10) - parseInt(cmp.parent.offset().top, 10) - parseInt(cmp.container.height(), 10) - 15;

        if(x + parseInt(cmp.container.width(), 10) > parseInt(cmp.parent.width(), 10)) {
            x =  parseInt(cmp.parent.width(), 10) - parseInt(cmp.container.width(), 10);
            cmp.container.addClass('right').removeClass('left');
        } else if(x < 0) {
            x = 0;
            cmp.container.removeClass('right').addClass('left');
        } else {
            cmp.container.removeClass('right').removeClass('left');
        }

        cmp.container.data('gid', handler.data('gid'));
        cmp.container.css({left: x + 'px', top: y + 'px'});

        cmp.container.addClass('opened');
    	
    });
    
}

AjaxPopup.prototype.getHtml = function () {
    var cmp = this;

    return '<div class="ajax-popup j-ajax-popup"><a class="close-popup j-close-popup" title="Закрыть" href="#">Закрыть</a><div class="ajax-popup-content j-ajax-popup-content"></div></div>';
}
/* Ajax popup */

/* Comments */
Comments = function () {
    this.container = $('.j-comment-form-container');
    this.form = $('.j-comment-form');
    this.closeButton = $('.j-hide-comment-form');
    this.formShowLink = $('.j-comment-form-link');
    this.replyCollection = $('.j-comment-form-reply');
    this.hiddenInput = $('.j-comment-id');

    this.init();
};

Comments.prototype.init = function () {
    var cmp = this;

    cmp.formShowLink.on("click", function(e) {
        e.preventDefault();

        cmp.formShowLink.hide();
        cmp.form.hide();
        if(cmp.container.find('.j-comment-form').length == 0) {
            cmp.container.append(cmp.form);
        }
        cmp.hiddenInput.val(-1);
        cmp.form.fadeIn(300);
    });

    cmp.closeButton.on("click", function(e) {
        e.preventDefault();

        cmp.form.hide();
        cmp.formShowLink.fadeIn(300);
    });

    cmp.replyCollection.on("click", function(e){
        e.preventDefault();

        cmp.form.hide();
        $(this).parents('.comment').after(cmp.form);
        cmp.hiddenInput.val($(this).data('id'));
        cmp.formShowLink.fadeIn(300);
        cmp.form.fadeIn(300);
    });
}
/* Comments */

/* Flash message */
FlashMessage = function () {};

FlashMessage.Show = function (txt) {
    var cmp = this;
    cmp.flashMessageContainer = $('.j-flash-message-container');
    if(cmp.flashMessageContainer.length == 0) return false;

    cmp.flashMessage = $('.j-flash-message');
    if(cmp.flashMessage.length > 0) {
        cmp.flashMessage.fadeOut(300, function(){
            cmp.flashMessage.remove();
            cmp.PrepareContent(txt);
        });
    } else {
        cmp.PrepareContent(txt);
    }
}

FlashMessage.PrepareContent = function(txt) {
    var cmp = this;

    cmp.flashMessageContainer.append('<div class="flash-message j-flash-message"><span class="flash-message-close j-flash-message-close">Закрыть</span><div>' + txt + '</div></div>')
    cmp.flashMessage = cmp.flashMessageContainer.find('.j-flash-message');
    cmp.flashMessageClose = cmp.flashMessageContainer.find('.j-flash-message-close');
    cmp.flashMessage.fadeIn(300);
    cmp.flashMessageClose.on("click", function(){
        cmp.flashMessage.fadeOut(300, function(){
            cmp.flashMessage.remove();
        });
    });
}
/* /Flash message */


/* Ajax form */
function bindAjaxForm () {
    var options = {
        success : onAjaxSubmitForm,
        dataType : 'json'
    };
    $('form.ajaxform').ajaxForm(options);

}

onAjaxSubmitForm = function(response, statusText, xhr, form) {
    $('#wait').html('');
    $('.j-error').html('');
    if (statusText == 'success') {
    	$(form).find('.error-message').empty().hide();
        $(form).find('.error').removeClass('error');
        if (response.submitOn) {
            if (response.redirectUrl) {
                if (response.openerOn) {
                    window.opener.location = response.redirectUrl;
                    window.opener.location.reload();
                    window.close();
                } else {
                    window.location = response.redirectUrl;
                }
            } else if (response.reloadOn) {
                if (typeof reloadPage == 'function') {
                    closeDialogForm('fs_dialogForm');
                    reloadPage(window.location.href, response);
                } else {
                    window.location.reload();
                }
            } else if (response.callFunc) {
                try {
                    (function(e) {
                        var e = response;
                        eval(response.callFunc + '(e);');
                    })();
                } catch (e) {
                }
            } else {

            }
        }
        if (response.errors) {
            
            for ( var ctrlErr in response.errors) {
                $('#err_' + $(form).attr('id') + '_' + ctrlErr).html(
                    response.errors[ctrlErr]).show();
                $('.j-err_' + $(form).attr('id') + '_' + ctrlErr).html(
                    response.errors[ctrlErr]).show();
                $('#' + $(form).attr('id') + ' input[name="'+ctrlErr+'"]').addClass('error');
                $('#' + $(form).attr('id') + ' textarea[name="'+ctrlErr+'"]').addClass('error');

            }
        }
        if ($('.captcha_pic')) {
        	$('.captcha_pic')
        	.attr('src', '/bitrix/tools/captcha.php?captcha_sid=' + response.captcha);
        	$('input[name="captcha_sid"]').val(response.captcha);
        }
    } else {
    }
}
/* /Ajax form */








