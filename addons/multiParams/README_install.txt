Более гибкие мультипараметры с использованием чанков.

Доступны для MODx 1.0.15 и 1.1

Типы параметров :
Parameters (Multiple x 2)
Parameters (Multiple x 4)
Parameters (Multiple x 5)
Images (Multiple)
Colors (Multiple)


Для того что бы добавить мультипараметры на сайт:

0. В архиве есть папки с версиями MODx, из папки с нужной версией копируем 3 файла на сервер:
-mutate_tmplvars.dynamic.php в manager/actions/
-tmplvars.inc.php в /manager/includes/
-save_content.processor.php в manager/processors/
(соответственно перед этим переименовываем оригинальные файлы на сервере во что нибудь)

1. Создаем сниппет "pxDooMulti".
Код берем из файла pxDooMulti.txt в архиве.

2. Создаем чанк для вывода параметров.
В него пихаем шаблон вывода одной строки параметров.

ПЛЕЙСХОЛДЕРЫ:

для Parameters (Multiple x Х) - 
      [+px_params_1+] - столбец 1
      [+px_params_2+] - столбец 2
      [+px_params_3+] - столбец 3
      [+px_params_4+] - столбец 4
      [+px_params_5+] - столбец 5
(соответственно если вы выбрали "Parameters (Multiple x 2)" , то столбца будет всего два и два доступных плейсхолдера [+px_params_1+] и  [+px_params_2+] )


для Images (Multiple) - 
      [+px_images+] - URL картинки как ни странно


для Colors (Multiple) - 
      [+px_images+] - URL картинки
      [+px_name+] - доп. поле  "Наименование"
      [+px_description+] - доп. поле  "Описание"


Примеры вызова сниппета:

[[pxDooMulti? &tpl=`NameMyChunk` &tv=`testTV`]]

[[pxDooMulti? &tpl=`NameMyChunk_COLORS` &tv=`testTV` &resourceId=`115`]]

где :
&tpl - имя чанка
&tv - имя TV параметра
&resourceId - необязательный параметр (ID ресурса). Если не указан, то данные выводятся для текущего ресурса. Если указан - то данные выводятся из того ресурса, ID которого указан.
