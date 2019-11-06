(function(document){
	var __yaCounterID = '28148988', // YandexCounter ID / Идентификатор яндекс счетчика
		__googleID = '',
		__debug = true,
		__init = false; // Console log debug

	/* Example set goal / Пример установки цели
		__setGoal('button', 'click', 'buttonClick');
	*/
	function __EventGoals() {
		if(!__init) __init = true; else return false;
		if(__debug) {
			var __text = __yaCounterID && __googleID == '' ? 'YandexCounter' : (__yaCounterID == '' && __googleID ? 'GoogleCounter' : 'Yandex and Google')
			console.log("(" + __text + ": " + __yaCounterID + " " + __googleID + ") - inited");
		}
		/* Goals / Цели */
		__setGoal('.to-basket',								'click',	'add2cart', 		'', 		{ya: true});
		__setGoal('#regUserForm',							'submit',	'registration', 	'', 					{ya: true});
		__setGoal('#authPopupForm',							'submit',	'login', 			'', 					{ya: true});
		__setGoal('#sForm',									'submit',	'search', 			'', 					{ya: true});
		__setGoal('#feedbackForm',							'submit',	'feedback', 		'', 					{ya: true});
		__setGoal('#ordForm',								'submit',	'orderformend', 	'', 					{ya: true});
		__setGoal('#basket_form',							'submit',	'order', 	'', 					{ya: true});
		__setGoal('.form-submit',							'click',	'callback', 	'', 					{ya: true});
		//__setGoal('#order form input[type=submit]',					'submit', 	'form-call', '', 	{google: true, action: 'click-button'});

		/* End goals / Конец цели */
	}

	/* Do not edit / Не редактировать */

	var __EventCounterInited = "yacounter" + __yaCounterID + "inited",
		__Counter = "yaCounter" + __yaCounterID,
		__nameSetGoal = "__setGoal" + __yaCounterID,
		__functionName = "gaInit_" + __googleID,
		__ajaxSearch = [];

	function __CheckCounter() {
		if(__yaCounterID && typeof window[__Counter] !== 'undefined' && __googleID == '') {
			__EventGoals();
		}
		if(__googleID && typeof window['ga'] !== 'undefined' && __yaCounterID == '') {
			__EventGoals();
		}
		if(__googleID && __yaCounterID && typeof window[__Counter] !== 'undefined' && typeof window['ga'] !== 'undefined') {
			__EventGoals();
		}
	}

	if(typeof window[__Counter] == 'undefined') {
		document.addEventListener(__EventCounterInited, __CheckCounter, false);
	} else {
		__CheckCounter();
	}

	var __functionGoogle = function () {
		__CheckCounter();
	}

	if (!Element.prototype.matches) {
	    Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.webkitMatchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector;
	}

	if(typeof XMLHttpRequest !== 'undefined') {
		var requestOpen = XMLHttpRequest.prototype.open;
	    XMLHttpRequest.prototype.open = function() {
	        this.addEventListener('load', function(ProgressEvent) {
	        	var _json = '', _text = '', _count = __ajaxSearch.length;
	        	if(_count) {
		        	try {
				        _json = JSON.parse(this.responseText);
				    } catch (e) {}
				    if(typeof _json == 'object') {
			    		_text = JSON.stringify(_json);
				    } else {
						_text = this.responseText;				
				    }
					var div = document.createElement("div");
					div.innerHTML = _text;
					_text = div.textContent || div.innerText || "";
				    for(i = 0; i < _count; i++) {
				    	if(__Contains(__ajaxSearch[i].selector, __ajaxSearch[i].search).length && _text.indexOf(__ajaxSearch[i].search) !== -1) {
				    		if(__yaCounterID && (__ajaxSearch[i].params.google && __ajaxSearch[i].params.google != true)) {
					    		try {
									window[__Counter].reachGoal(__ajaxSearch[i].goal, __ajaxSearch[i].params, __ajaxSearch[i].callback);	
								} catch (e) {
						        	console.log("(Yandex Counter: " + __yaCounterID + ") Error reachGoal: " + e.message);
						    	}	
						    	if(__debug) {
									console.log("(Yandex Counter: " + __yaCounterID + ") AjaxGoal: goal: " + __ajaxSearch[i].goal +", selector: " + __ajaxSearch[i].selector + ", search: " + __ajaxSearch[i].search);
								}	
							}
		    				if(__googleID && (__ajaxSearch[i].params.ya && __ajaxSearch[i].params.ya != true)) {
					    		try {
					    			if('action' in __ajaxSearch[i].params) {
										window['ga']('send', 'event', __ajaxSearch[i].goal, __ajaxSearch[i].params.action);	
									} else {
										console.log("(Google counter: " + __googleID + ") Error reachGoal: Empty action");
									}
								} catch (e) {
						        	console.log("(Google counter: " + __googleID + ") Error reachGoal: " + e.message);
						    	}	
						    	if(__debug) {
									console.log("(Google Counter: " + __googleID + ") AjaxGoal: goal: " + __ajaxSearch[i].goal +", selector: " + __ajaxSearch[i].selector + ", search: " + __ajaxSearch[i].search);
								}	
							}
				    	}
				    }
				}
	        }); 
	        requestOpen.apply(this, arguments);
	    };
	}

	function __Contains(selector, text) {
	  var elements = document.querySelectorAll(selector);
	  return Array.prototype.filter.call(elements, function(element){
	    return RegExp(text).test(element.textContent);
	  });
	}

	/**
	 * Sets the goal of the YandexCounter / Устанавливает цель ЯндексМетрики 
	 * @param {String} selector - DOM query selector
	 * @param {String} event - Event for the DOM elements
	 * @param {String} goal - YandexCounter goal
	 * @param {Object} params - Params for goal, more details by reference https://yandex.ru/support/metrika/data/visit-params.html
	 * @param {Function} callback - callback for goal
	 * @return {undefined}
	 */
	function __setGoal(selector, event, goal, ajaxSearch, params, callback) {
		params = params ? params : {};
		callback = callback ? callback : null;
		ajaxSearch = ajaxSearch ? ajaxSearch : null;

		if(ajaxSearch) {
			__ajaxSearch.push({selector:selector, search: ajaxSearch, goal: goal, params: params, callback: callback});
		}

		/*var element = document.querySelectorAll(selector),
			onevent = 'on' + event;
		for(var i = 0; i < element.length; i++) {	
			
			var _eventtext = "",
				_attr = element[i].getAttribute(onevent);
			_attr = _attr ? _attr : "";

			if(__yaCounterID && (!params.google || (params.google && params.google != true))) {
				_eventtext = __Counter + ".reachGoal('" + goal + "');";
			}
			if(__googleID && (!params.ya || (params.ya && params.ya != true))) {
				_eventtext = "ga('send', 'event', '" + goal + "', '" + params.action + "');";
			}
			element[i].setAttribute(onevent, _eventtext + _attr);
			
		}*/

 		document.addEventListener(event, function(e) {
	        for (var target=e.target; target && target!=this; target=target.parentNode) {
	          	if (target.matches(selector)) {
	          		if(__yaCounterID && (!params.google || (params.google && params.google != true))) {
		          		try {
		              		window[__Counter].reachGoal(goal, params, callback);
		          		} catch (e) {
					        console.log("(Yandex Counter: " + __yaCounterID + ") Error reachGoal: " + e.message);
					    }
						if(__debug) {
							console.log("(Yandex Counter: " + __yaCounterID + ") Event: " + event + ", goal: " + goal +", selector: " + selector);
						}	
					}
		    		if(__googleID && (!params.ya || (params.ya && params.ya != true))) {
		    			try {
		    				if('action' in params) {
								window['ga']('send', 'event', goal, params.action);	
							} else {
								console.log("(Google counter: " + __googleID + ") Error reachGoal: Empty action");
							}
						} catch (e) {
				        	console.log("(Google counter: " + __googleID + ") Error reachGoal: " + e.message);
				    	}	
				    	if(__debug) {
				    		console.log("(Google Counter: " + __googleID + ") Event: " + event + ", goal: " + goal +", selector: " + selector);
				    	}
		    		}
	          	}
	        }
      	}, true);
	}

	window[__nameSetGoal] = __setGoal;
	window[__functionName] = __functionGoogle;
}(document, window));
