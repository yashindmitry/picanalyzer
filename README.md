picanalyzer
===========

Требования
----------
- PHP 5.4
- ImageMagick

Пример использования
--------------------
Разбить изображение на отдельные картинки:

    $ php picanalyzer.php --partition --partition-input=data/image.gif --partition-output=data/output/

Собрать изображение из отдельных картинок склеивая горизонтально:

    $ php picanalyzer.php --join --join-input=data/output/ --join-output=data/joined.png


Версии
------
1.1

    - Изменились названия аргументов

    - Воможность склеить несколько изображений в одно

1.0

    - Возможность разбивать изображение на несколько, выделяя по объекту на каждое