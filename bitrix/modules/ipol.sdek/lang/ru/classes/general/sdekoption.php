<?
// Авторизация 
	$MESS ['IPOLSDEK_AUTH_NO']  = 'Не удалось авторизоваться. Проверьте введенные данные или повторите попытку позже. Ошибка: ';
	$MESS ['IPOLSDEK_AUTH_YES'] = 'Вы успешно авторизовались.';
	$MESS ['IPOLSDEK_AUTH_NOCURL'] = "Авторизация невозможна: отсутствует PHP-библиотека CURL. Свяжитесь с техподдержкой хостинга с просьбой подключить библиотеку.";
//Печать
	$MESS ['IPOLSDEK_SIGN_PRNTSDEK'] = "Печать СДЭК";
	$MESS ['IPOLSDEK_PRINTERR_BADORDERS'] = "Нельзя напечатать следующие заказы: ";
	$MESS ['IPOLSDEK_PRINTERR_TOTALERROR'] = "Выбранные заказы распечатать нельзя. Возможные причины: заявка на заказ не была отослана модулем и принята СДЭКом.";
// Удаление заявки
	$MESS ['IPOLSDEK_DRQ_DELETED']="Заявка удалена.";

// синхронизация статусов
	$MESS ['IPOLSDEK_ERRLOG_NOSALEOOS']   = 'Ошибка подключения модуля Интернет-магазина при получении статусов заказов.';
	$MESS ['IPOLSDEK_GOS_UNBLSND']="Не удалось запросить статусы заказов.\n";
	$MESS ['IPOLSDEK_GOS_HASERROR']="Ошибка синхронизации статусов заказов. ";
	$MESS ['IPOLSDEK_GOS_UNKNOWNSTAT']="Неопознанный статус заказа №";
	$MESS ['IPOLSDEK_GOS_NOTUPDATED']="Заказ не обновлен.";
	$MESS ['IPOLSDEK_GOS_STATUS']="Статус: ";
	$MESS ['IPOLSDEK_GOS_CANTUPDATEREQ']="Не удалось обновить информацию о статусе заявки заказа №";
	$MESS ['IPOLSDEK_GOS_CANTUPDATEORD']="Не удалось обновить статус заказа №";
	$MESS ['IPOLSDEK_GOS_BADREQTOUPDTorder']="Попытка изменить статус неподтвержденной заявки на заказ №";
	$MESS ['IPOLSDEK_GOS_CANTMARKPAYED']="Не удалось отметить оплаченным заказ №";
	$MESS ['IPOLSDEK_GOS_CANTUPDATESHP']="Не удалось обновить статус отгрузки №";
	$MESS ['IPOLSDEK_GOS_BADREQTOUPDTshipment']="Попытка изменить статус неподтвержденной заявки на отгрузку №";

// синхронизация городов
	$MESS ['IPOLSDEK_SYNCTY_ERR_HAPPENING']   = "Ошибка синхронизации городов: ";
	$MESS ['IPOLSDEK_SYNCTY_LBL_SCD']   = "Синхронизация городов завершена. Запуск синхронизации справочников.";
	$MESS ['IPOLSDEK_SYNCTY_LBL_PROCESS']   = "Синхронизация городов: ";

	$MESS ['IPOLSDEK_UPDT_ERR']='При синхронизации произошли ошибки, за подробной информацией обратитесь к лог-файлу ошибок (/bitrix/js/ipol.sdek/errorLog.txt)';
	$MESS ['IPOLSDEK_UPDT_DONE']='Модуль синхронизирован - ';
	$MESS ['IPOLSDEK_FILE_UNBLUPDT']='Не удалось получить с сервера информацию о пунктах самовывоза. Код ответа сервера: ';
	$MESS ['IPOLSDEK_ERRLOG_ERRSUNCCITY']='Не удалось синхронизировать города. Ошибка: ';
	$MESS ['IPOLSDEK_DELCITYERROR'] = "Не удалось переопределить города, ошибка SQL: ";
	$MESS ['IPOLSDEK_FILEIPL_UNBLUPDT']='Не удалось запросить дополнительную информацию о пунктах самовывоза. Код ответа сервера: ';

// Импорт
	$MESS ['IPOLSDEK_IMPORT_ERROR_NOFILES'] = "Отсутствуют файлы импорта. Запустите импорт заново";
	$MESS ['IPOLSDEK_IMPORT_ERROR_lbl'] = "Ошибка: ";
	$MESS ['IPOLSDEK_IMPORT_ERROR_NOCITY'] = "Не найден тип местоположения Город (код - CITY)";
	$MESS ['IPOLSDEK_IMPORT_ERROR_WHILEIMPORT'] = "Обнаружены <a href='javascript:void(0)' onclick='$(this).next().toggle(); return false;'>ошибки</a> в процессе работы.";

	$MESS ['IPOLSDEK_IMPORT_STATUS_IDONE'] = 'Импорт завершен. Добавлено местоположений: ';
	$MESS ['IPOLSDEK_IMPORT_STATUS_SDONE'] = 'Синхронизация завершена.';
	$MESS ['IPOLSDEK_IMPORT_STATUS_MDONE'] = 'Оценка местоположений завершена.';

	$MESS ['IPOLSDEK_IMPORT_PROCESS_SYNC'] = '> синхронизация: проверено ';
	$MESS ['IPOLSDEK_IMPORT_PROCESS_FROM'] = ' из ';
	$MESS ['IPOLSDEK_IMPORT_PROCESS_addingCities'] = 'Добавление';
	$MESS ['IPOLSDEK_IMPORT_PROCESS_gettingCities'] = 'Получение';
	$MESS ['IPOLSDEK_IMPORT_PROCESS_definingCities'] = 'Определение';
	$MESS ['IPOLSDEK_IMPORT_PROCESS_WORKING'] = 'местоположений. Обработано: ';

// Таблица
	$MESS ['IPOLSDEK_STT_CHNG']='Изменить';
	$MESS ['IPOLSDEK_STT_TOORDR']='К заказу';
	$MESS ['IPOLSDEK_STT_SHOW']='показать';
	$MESS ['IPOLSDEK_STT_SENDER']='Отправитель';
	$MESS ['IPOLSDEK_STT_ADDRESS']='Адрес';
	$MESS ['IPOLSDEK_STT_PACKS'] = "Места";
?>