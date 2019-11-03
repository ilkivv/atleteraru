$(function() {
	

	
	$('.js-product-container .to-basket').on('click', function(e){				
		

		var item = $(this).closest('.js-product-container');
		
		var id = item.attr('data-id');
		
		var quantityElem = item.find('input[name="count"]');
		
		var quantity = 1;
		
		if(quantityElem.length > 0){
			quantity = quantityElem.val();
		}
		

		var name = $.trim(item.attr('data-name'));
		
		var price = item.attr('data-price');		
		var category = $.trim(item.attr('data-category'));
		var brand = item.attr('data-brand');

		dataLayer.push({
			"ecommerce": {
				"add": {
					"products": [
						{
							"id": id,
							"name" : name,
							"price": price,
							"quantity": quantity,
							"brand": brand,
							"category": category
						}
					]
					}
				}
			});
			


	});
	
	$('body').on('click', '.ajax-popup-form' ,function(e){
			
		e.preventDefault();
		var url = $(this).attr('href');
		
		$.magnificPopup.open({
			type: 'ajax',
			items: {
				src: url,		
			},						
			callbacks: {
				ajaxContentAdded: function() {
					
				}
			}
			

		});
	}); 

	
	
	$('body').on('click', '.deleteitem' , function(e){
			
		var item = $(this).closest('.product-line');
		var id = item.attr('data-product-id');
		var title = $.trim(item.attr('data-item-name'));
		dataLayer.push({
			"ecommerce": {
				"remove": {
					"products": [
						{
							"id": id,
							"name": title,						
						}
					]
				}
			}
		});
	});
	
	
	$('.cmn-toggle-switch').on('click', function(e){
			e.preventDefault();
			var item = $(this);
			var container = $(this).closest('.toggle-container');
			var toggle = container.find('.toggle-content');

			if (item.hasClass("active")){
				item.removeClass("active");
				toggle.removeClass("opened");
				container.removeClass("is-active");
				$('body').children('.menu-overlay').remove();
				$('body').removeClass('fixed-body');
			}else{
				item.addClass("active");
				toggle.addClass("opened");
				container.addClass("is-active");

				var overlay = $('body').append('<div class="menu-overlay"></div>').children('.menu-overlay');
				overlay.append('<div class="menu-overlay-inner"></div>');
				
				$('body').addClass('fixed-body');

				overlay.on('click', function(){
					item.click();
				});
			}

	});
	
	
	$('.mobile-catalog-menu .menu-title').on('click', function(){
		
		var elem = $(this); 
		var container = elem.closest('.mobile-catalog-menu');
		
		container.toggleClass('opened');
		
	});
	
	
	$('body').on('click', '.mobile-toggle-search', function(e){
		
		$(this).find('.mobile-search-block').addClass('active');
		
	});
	
	
	 $(window).on('load', function(){
		
		$( '.slider-main .slider-items' ).each( function() {
     
			  var that = this;
		  
			  $(that).slick({
				  arrows: true,
				  dots: false,     
				  prevArrow: '<div class="prev"></div>',
				  nextArrow: '<div class="next"></div>',
				 fade: true,
				cssEase: 'linear'         
			  });
			  
			  
			 
		   });
		   
		   
		  // $('.slider-main').css({height: auto});
	
	
	});
	
	
	
	//$('#basket_items').stacktable();
	
	$(document).on('click', '.mobile-search-block .search-btn', function(e){
      
      var container = $(this).closest('.mobile-search-block');
      var value = container.find('.search-field').val();
      
      if(value == ''){
        e.preventDefault();
        container.removeClass('active');
      }
      
    });
  
  $(document).mouseup(function (e){ 
  
		var div = $(".mobile-search-block"); 
    
		if (!div.is(e.target) 
		    && div.has(e.target).length === 0) { 
			div.removeClass('active');
		}
    
	});
  
  $('.products-carousel').slick({
		  slidesToShow: 5,
		  slidesToScroll: 1,		 
		  focusOnSelect: false,
		  arrows: true,
		  dots: false,
		  prevArrow: '<div class="prev carousel-prev"></div>',
		  nextArrow: '<div class="next carousel-next"></div>',
		   responsive: [
				{
				  breakpoint: 1654,
				  settings: {
					slidesToShow: 5
				  }
				},		  
				{
				 breakpoint: 1200,
				  settings: {
					slidesToShow: 4
				  }
				}, 
				{
				  breakpoint: 992,
				  settings: {
					slidesToShow: 2
				  }
				},

               {
                   breakpoint: 768,
                   settings: {
                       slidesToShow: 3
                   }
               },
               {
                   breakpoint: 664,
                   settings: {
                       slidesToShow: 2
                   }
               },
               {
                   breakpoint: 423,
                   settings: {
                       slidesToShow: 1
                   }
               },
				// You can unslick at a given breakpoint now by adding:
				// settings: "unslick"
				// instead of a settings object
			  ]
	});
	
});

