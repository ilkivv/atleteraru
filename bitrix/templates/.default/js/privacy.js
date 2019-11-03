var privacy = function() {
  function privacy(params) {
    if (!(this instanceof privacy)) {
      return new privacy(params);
    }
    this.initialize.apply(this, arguments);
  }
  privacy.prototype = {
    site: window.location.origin,
    company: '',
    selector: 'privacy',
    date: '«29»  мая 2017 г.',
    privacy_window: '',
    initialize: function (arg) {
      if(typeof arg != 'undefined') {
        this.date     = 'date' in arg ? arg.date : this.date;
        this.site     = 'site' in arg ? arg.site : this.site;
        this.company  = 'company' in arg ? arg.company : this.company;
      }

      this.text = this.text.replace(/\%Date\%/gi, this.date);
      this.text = this.text.replace(/\%WebSite\%/gi, this.site);
      this.text = this.text.replace(/\%CompanyName\%/gi, this.company);
      document.write(this.style);

      this.ready(this.readyInitialize);
    },
    readyInitialize: function (arg) {
      var frag = document.createDocumentFragment(),
          temp = document.createElement('div');

      temp.innerHTML = arg.wrapper;
      while (temp.firstChild) frag.appendChild(temp.firstChild);
      document.body.appendChild(frag);
      arg.privacy_window = document.getElementById("privacy-window");

      var inner = arg.privacy_window.getElementsByClassName("privacy-inner")[0];
      inner.innerHTML = arg.text;

      var iframes = document.getElementsByTagName('iframe');
      for (var i = 0, len = iframes.length; i < len; i++) {
        iframes[i].onload = arg.iframeLoad;
      }

      document.addEventListener("click", function(e) {
        for (var target=e.target; target && target!=this; target=target.parentNode) {
          if (target.matches('.' + arg.selector)) {
              arg.showModal();
              break;
          }
        }
      }, false);

      arg.privacy_window.addEventListener('click', function(event){
        if(event.target == arg.privacy_window || event.target.className.indexOf("privacy-close") > -1) {
          arg.fadeOut(arg.privacy_window, 300);
          document.body.classList.remove("privacy-overlay");
        }
      }, false);
    },
    bindItems() {
      var items = document.getElementsByClassName(this.selector);
      for (var i = 0, len = items.length; i < len; i++) {
        items[i].removeEventListener('click', this.showModal);
        items[i].addEventListener('click', this.showModal, false);
      }
    },
    showModal(event) {
      if(typeof event != 'undefined') event.preventDefault();
      document.body.classList.add("privacy-overlay");
      this.fadeIn(this.privacy_window, 300);
    },
    iframeLoad(e, i) {
      var item = e.target,
          items = [];
      try {
        if (item.contentDocument) {
          items = iframe.contentDocument.getElementsByClassName(this.selector);
        } else {
          items = window.frames[item.name].document.getElementsByClassName(this.selector);
        }
        for (var i = 0, len = items.length; i < len; i++) {
          items[i].removeEventListener('click', this.showModal);
          items[i].addEventListener('click', this.showModal, false);
        }
      } catch (e) {
        console.log(e.message);
      }
    },
    ready(fn) {

      var $this = this;
      if (document.readyState != 'loading'){
        fn($this);
      } else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', function(){
          fn($this)
        });
      } else {
        document.attachEvent('onreadystatechange', function() {
          if (document.readyState != 'loading')
            fn($this);
        });
      }
    },
    fadeIn( elem, ms ) {
      if( ! elem )
        return;

      elem.style.opacity = 0;
      elem.style.filter = "alpha(opacity=0)";
      elem.style.display = "inline-block";
      elem.style.visibility = "visible";

      if( ms ) {
        var opacity = 0;
        var timer = setInterval( function() {
          opacity += 50 / ms;
          if( opacity >= 1 ) {
            clearInterval(timer);
            opacity = 1;
          }
          elem.style.opacity = opacity;
          elem.style.filter = "alpha(opacity=" + opacity * 100 + ")";
        }, 50 );
      } else {
        elem.style.opacity = 1;
        elem.style.filter = "alpha(opacity=1)";
      }
    },
    fadeOut( elem, ms ) {
      if( ! elem )
        return;

      if( ms ) {
        var opacity = 1;
        var timer = setInterval( function() {
          opacity -= 50 / ms;
          if( opacity <= 0 ) {
            clearInterval(timer);
            opacity = 0;
            elem.style.display = "none";
            elem.style.visibility = "hidden";
          }
          elem.style.opacity = opacity;
          elem.style.filter = "alpha(opacity=" + opacity * 100 + ")";
        }, 50 );
      } else {
        elem.style.opacity = 0;
        elem.style.filter = "alpha(opacity=0)";
        elem.style.display = "none";
        elem.style.visibility = "hidden";
      }
    },
    wrapper: "<div id='privacy-window'>"
      + "<div class='privacy-container'>"
      + "<div class='privacy-close'></div>"
      + "<div class='privacy-inner'>"
      + ""
      + "</div>"
      + "</div>"
      + "</div>",
    style: "<style>"
      + "body.privacy-overlay { overflow: hidden; }"
      + "#privacy-window { z-index: 99999; cursor: pointer; font-family: Tahoma; position: fixed; top: 0; left: 0; width: 100% !important; height: 100% !important; display: none; background-color: #f6f6f6; overflow: auto; }"
      + "#privacy-window .privacy-inner { padding: 3em; box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.1); border-radius: 2px; background: #ffffff; }"
      + "#privacy-window .privacy-container { cursor: default; position: relative; margin: 3em 0; min-width: 1px !important; padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; }"
      + "#privacy-window .privacy-close { width: 30px; height: 30px; position: fixed; right: 30px; top: 15px; cursor: pointer; opacity: .3; }"
      + "#privacy-window .privacy-close:hover { opacity: 1; }"
      + "#privacy-window .privacy-close:before { content: ''; width: 100%; height: 100%; position: absolute; background-size: contain; background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iMzU3cHgiIGhlaWdodD0iMzU3cHgiIHZpZXdCb3g9IjAgMCAzNTcgMzU3IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAzNTcgMzU3OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PGc+PGcgaWQ9ImNsb3NlIj48cG9seWdvbiBwb2ludHM9IjM1NywzNS43IDMyMS4zLDAgMTc4LjUsMTQyLjggMzUuNywwIDAsMzUuNyAxNDIuOCwxNzguNSAwLDMyMS4zIDM1LjcsMzU3IDE3OC41LDIxNC4yIDMyMS4zLDM1NyAzNTcsMzIxLjMgMjE0LjIsMTc4LjUgIi8+PC9nPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48L3N2Zz4='); }"
      + "#privacy-window p { color: #000; font-size: 1em; margin: 1em 0; line-height: 1.5; }"
      + "#privacy-window .privacy-h2 { color: #000; margin: 0.8em 0px; font-size: 1.5em; font-weight: bold; }"
      + "@media (min-width: 768px) { #privacy-window .privacy-container { width: 750px; } }"
      + "@media (min-width: 992px) { #privacy-window .privacy-container { width: 970px; } }"
      + "@media (min-width: 1200px) { #privacy-window .privacy-container { width: 1170px; } }"
      + "@media (max-width: 767px) { #privacy-window .privacy-inner { padding: 1.5em; } #privacy-window .privacy-h2 { font-size: 1em; } }"
      + "</style>",
    text: "<div class='privacy-h2'>ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ</div>" 
      + "<p>Настоящая Политика конфиденциальности персональных данных (далее – Политика конфиденциальности) действует в отношении всей информации, которая расположена на сайте %WebSite%, может получить о Пользователе во время использования сайта, программ и продуктов.</p>"         
      + "<div class='privacy-h2'>1. ОПРЕДЕЛЕНИЕ ТЕРМИНОВ</div>"
      + "<p>1.1.  В настоящей Политике конфиденциальности используются следующие термины:</p>"
      + "<p>1.1.1.  «Администрация сайта (далее – Администрация сайта) » – уполномоченные сотрудники на управления сайтом, действующие от имени %CompanyName%,  которые организуют и (или) осуществляет обработку персональных данных, а также определяет цели обработки персональных данных, состав персональных данных, подлежащих обработке, действия (операции), совершаемые с персональными данными.</p>"
      + "<p>1.1.2. «Персональные данные» - любая информация, относящаяся к прямо или косвенно определенному или определяемому физическому лицу (субъекту персональных данных).</p>"
      + "<p>1.1.3. «Обработка персональных данных» - любое действие (операция) или совокупность действий (операций), совершаемых с использованием средств автоматизации или без использования таких средств с персональными данными, включая сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (распространение, предоставление, доступ), обезличивание, блокирование, удаление, уничтожение персональных данных.</p>"
      + "<p>1.1.4. «Конфиденциальность персональных данных» - обязательное для соблюдения Оператором или иным получившим доступ к персональным данным лицом требование не допускать их распространения без согласия субъекта персональных данных или наличия иного законного основания.</p>"
      + "<p>1.1.5. «Пользователь сайта (далее   Пользователь)» – лицо, имеющее доступ к Сайту, посредством сети Интернет и использующее Сайт.</p>"
      + "<p>1.1.6. «Cookies» — небольшой фрагмент данных, отправленный веб-сервером и хранимый на компьютере пользователя, который веб-клиент или веб-браузер каждый раз пересылает веб-серверу в HTTP-запросе при попытке открыть страницу соответствующего сайта.</p>"
      + "<p>1.1.7. «IP-адрес» — уникальный сетевой адрес узла в компьютерной сети, построенной по протоколу IP.</p>"
      + "<div class='privacy-h2'>2.  ОБЩИЕ ПОЛОЖЕНИЯ</div>"
      + "<p>2.1.  Использование Пользователем сайта означает согласие с настоящей Политикой конфиденциальности и условиями обработки персональных данных Пользователя.</p>"
      + "<p>2.2.  В случае несогласия с условиями Политики конфиденциальности Пользователь должен прекратить использование сайта.</p>"
      + "<p>2.3.  Настоящая Политика конфиденциальности применяется только к сайту %WebSite%. Сайт не контролирует и не несет ответственность за сайты третьих лиц, на которые Пользователь может перейти по ссылкам, доступным на сайте.</p>"
      + "<p>2.4.  Администрация сайта не проверяет достоверность персональных данных, предоставляемых Пользователем сайта.</p>"
      + "<div class='privacy-h2'>3.  ПРЕДМЕТ ПОЛИТИКИ КОНФИДЕНЦИАЛЬНОСТИ</div>"
      + "<p>3.1.  Настоящая Политика конфиденциальности устанавливает обязательства Администрации сайта по неразглашению и обеспечению режима защиты конфиденциальности персональных данных, которые Пользователь предоставляет по запросу Администрации сайта или при заполнении на сайте форм обратной связи, регистрации на сайте или при оформлении заказа для приобретения Товара.</p>"
      + "<p>3.2. Персональные данные, разрешённые к обработке в рамках настоящей Политики конфиденциальности, предоставляются Пользователем путём заполнения любых форм обратной связи на Сайте  %WebSite%, и могут включать в себя следующую информацию:</p>"
      + "<p>3.2.1. фамилию, имя, отчество Пользователя; </p>"
      + "<p>3.2.2. контактный телефон Пользователя;</p>"
      + "<p>3.2.3. адрес электронной почты (e-mail); </p>"
      + "<p>3.2.4. адрес доставки Товара;</p>"
      + "<p>3.2.5. место жительство Пользователя.</p>"
      + "<div class='privacy-h2'>4.  ЦЕЛИ СБОРА ПЕРСОНАЛЬНОЙ ИНФОРМАЦИИ ПОЛЬЗОВАТЕЛЯ</div>"
      + "<p>4.1. Персональные данные Пользователя Администрация сайта может использовать в целях:</p>"
      + "<p>4.1.1. Идентификации Пользователя, зарегистрированного на сайте, для оформления заказа и (или) заключения Договора купли-продажи товара дистанционным способом с %CompanyName%.</p>"
      + "<p>4.1.2. Предоставления Пользователю доступа к персонализированным ресурсам Сайта.</p>"
      + "<p>4.1.3. Установления с Пользователем обратной связи, включая направление уведомлений, запросов, касающихся использования Сайта, оказания услуг, обработка запросов и заявок от Пользователя.</p>"
      + "<p>4.1.4. Определения места нахождения Пользователя для обеспечения безопасности, предотвращения мошенничества.</p>"
      + "<p>4.1.5. Подтверждения достоверности и полноты персональных данных, предоставленных Пользователем.</p>"
      + "<p>4.1.6. Создания учетной записи для совершения покупок, если Пользователь дал согласие на создание учетной записи.</p>"
      + "<p>4.1.7. Уведомления Пользователя Сайта о состоянии Заказа.</p>"
      + "<p>4.1.8. Обработки и получения платежей, подтверждения налога или налоговых льгот, оспаривания платежа, определения права на получение кредитной линии Пользователем.</p>"
      + "<p>4.1.9. Предоставления Пользователю эффективной клиентской и технической поддержки при возникновении проблем связанных с использованием Сайта.</p>"
      + "<p>4.1.10. Предоставления Пользователю с его согласия, обновлений продукции, специальных предложений, информации о ценах, новостной рассылки и иных сведений от имени %CompanyName% или от имени партнеров %CompanyName%.</p>"
      + "<p>4.1.11. Осуществления рекламной деятельности с согласия Пользователя.</p>"
      + "<p>4.1.12. Предоставления доступа Пользователю на сайты или сервисы партнеров %CompanyName% с целью получения продуктов, обновлений и услуг.</p>"
      + "<div class='privacy-h2'>5.  СПОСОБЫ И СРОКИ ОБРАБОТКИ ПЕРСОНАЛЬНОЙ ИНФОРМАЦИИ</div>"
      + "<p>5.1. Обработка персональных данных Пользователя осуществляется без ограничения срока, любым законным способом, в том числе в информационных системах персональных данных с использованием средств автоматизации или без использования таких средств.</p>"
      + "<p>5.2. Пользователь соглашается с тем, что Администрация сайта вправе передавать персональные данные третьим лицам, в частности, курьерским службам, организациями почтовой связи, операторам электросвязи, исключительно в целях выполнения заказа Пользователя, оформленного на Сайте %WebSite%, включая доставку Товара.</p>"
      + "<p>5.3. Персональные данные Пользователя могут быть переданы уполномоченным органам государственной власти Российской Федерации только по основаниям и в порядке, установленным законодательством Российской Федерации.</p>"
      + "<p>5.4. При утрате или разглашении персональных данных Администрация сайта информирует Пользователя об утрате или разглашении персональных данных.</p>"
      + "<p>5.5. Администрация сайта принимает необходимые организационные и технические меры для защиты персональной информации Пользователя от неправомерного или случайного доступа, уничтожения, изменения, блокирования, копирования, распространения, а также от иных неправомерных действий третьих лиц.</p>"
      + "<p>5.6. Администрация сайта совместно с Пользователем принимает все необходимые меры по предотвращению убытков или иных отрицательных последствий, вызванных утратой или разглашением персональных данных Пользователя.</p>"
      + "<div class='privacy-h2'>6.  ОБЯЗАТЕЛЬСТВА СТОРОН</div>"
      + "<p>6.1. Пользователь обязан:</p>"
      + "<p>6.1.1. Предоставить информацию о персональных данных, необходимую для пользования Сайтом.</p>"
      + "<p>6.1.2. Обновить, дополнить предоставленную информацию о персональных данных в случае изменения данной информации.</p>"
      + "<p>6.2. Администрация сайта обязана:</p>"
      + "<p>6.2.1. Использовать полученную информацию исключительно для целей, указанных в п. 4 настоящей Политики конфиденциальности.</p>"
      + "<p>6.2.2. Обеспечить хранение конфиденциальной информации в тайне, не разглашать без предварительного письменного разрешения Пользователя, а также не осуществлять продажу, обмен, опубликование, либо разглашение иными возможными способами переданных персональных данных Пользователя, за исключением п.п. 5.2. и 5.3. настоящей Политики Конфиденциальности.</p>"
      + "<p>6.2.3. Принимать меры предосторожности для защиты конфиденциальности персональных данных Пользователя согласно порядку, обычно используемого для защиты такого рода информации в существующем деловом обороте.</p>"
      + "<p>6.2.4. Осуществить блокирование персональных данных, относящихся к соответствующему Пользователю, с момента обращения или запроса Пользователя или его законного представителя либо уполномоченного органа по защите прав субъектов персональных данных на период проверки, в случае выявления недостоверных персональных данных или неправомерных действий.</p>"
      + "<div class='privacy-h2'>7.  ОТВЕТСТВЕННОСТЬ СТОРОН</div>"
      + "<p>7.1. Администрация сайта, не исполнившая свои обязательства, несёт ответственность за убытки, понесённые Пользователем в связи с неправомерным использованием персональных данных, в соответствии с законодательством Российской Федерации, за исключением случаев, предусмотренных п.п. 5.2., 5.3. и 7.2. настоящей Политики Конфиденциальности.</p>"
      + "<p>7.2. В случае утраты или разглашения Конфиденциальной информации Администрация сайта не несёт ответственность, если данная конфиденциальная информация:</p>"
      + "<p>7.2.1. Стала публичным достоянием до её утраты или разглашения.</p>"
      + "<p>7.2.2. Была получена от третьей стороны до момента её получения Администрацией сайта.</p>"
      + "<p>7.2.3. Была разглашена с согласия Пользователя.</p>"
      + "<div class='privacy-h2'>8.  РАЗРЕШЕНИЕ СПОРОВ</div>"
      + "<p>8.1. До обращения в суд с иском по спорам, возникающим из отношений между Пользователем сайта и Администрацией сайта, обязательным является предъявление претензии (письменного предложения о добровольном урегулировании спора).</p>"
      + "<p>8.2 .Получатель претензии в течение 30 календарных дней со дня получения претензии, письменно уведомляет заявителя претензии о результатах рассмотрения претензии.</p>"
      + "<p>8.3. При не достижении соглашения спор будет передан на рассмотрение в судебный орган в соответствии с действующим законодательством Российской Федерации.</p>"
      + "<p>8.4. К настоящей Политике конфиденциальности и отношениям между Пользователем и Администрацией сайта применяется действующее законодательство Российской Федерации.</p>"
      + "<div class='privacy-h2'>9.  ДОПОЛНИТЕЛЬНЫЕ УСЛОВИЯ</div>"
      + "<p>9.1. Администрация сайта вправе вносить изменения в настоящую Политику конфиденциальности без согласия Пользователя.</p>"
      + "<p>9.2. Новая Политика конфиденциальности вступает в силу с момента ее размещения на Сайте, если иное не предусмотрено новой редакцией Политики конфиденциальности.</p>"
      + "<p>9.3. Все предложения или вопросы по настоящей Политике конфиденциальности следует сообщать  указать раздел сайта</p>"
      + "<p>9.4. Действующая Политика конфиденциальности размещена на странице по адресу %WebSite%.</p>"
      + "<p>Обновлено %Date%</p>"
  };
  return privacy;
}();
