<?php

/**
 * PicAnalyzer
 *
 * @author Dmitry Yashin <to.yashin@gmail.com>
 * @link http://jonnyb.ru/picanalyzer/
 * @copyright 2014 jonnyb.ru
 * @license GNU GENERAL PUBLIC LICENSE Version 2
 */

class ImagePartition {

	const
		  COLOR_LEVEL = 250 // Уровень, выше которого пиксель считается белым (2-255)
		, MIN_PIXELS_COUNT_FOR_OBJECT = 4 // Минимальное количество пикселей в часте картинки
	;

	private
		  $_img // Объект Imagick с оригинальной картинкой
		, $_img_array = array() // Карта пикселей объекта
		, $_objects = array() // Массив с объектами (атомарными картинками)
		;

	/**
	 * Делит изображение на атомарные кусочки
	 */
	public function getParts($image_path, $output_path) {
		$this->_img = new Imagick($image_path);
		// Создаем карту пикселей
		$this->_img_array = $this->_getPixelMap($this->_img);
		// Ищем по карте объекты
		do {
			$object = $this->_findObject();
			if ($object) {
                // Добавим объект
				$this->_objects[] = $object;
                // Удалим его с карты
                $this->_removeObject($object);
			}
		} while ($object);
        // Сохраняем найденные объекты в файлы
        foreach ($this->_objects as $number => $object) {
            $this->_objectToImage($object, $output_path . $number . '.png');
        }
	}

	/**
	 * Возвращает попиксельную карту картинки
	 */
	protected function _getPixelMap($img) {
		$img_array = array();
		$iterator = $img->getPixelIterator();
		foreach($iterator as $row => $pixels) {
			foreach ($pixels as $col => $pixel) {
				$color = $pixel->getColor();
				if ($color['r'] > self::COLOR_LEVEL && $color['g'] > self::COLOR_LEVEL && $color['b'] > self::COLOR_LEVEL) {
					$img_array[$col][$row] = false;
				} else {
					$img_array[$col][$row] = true;
				}
			}
		}

		return $img_array;
	}

	/**
	 * Возвращает первый найденный на карте объект или false
	 */
	protected function _findObject() {
		foreach ($this->_img_array as $col => $rows) {
			foreach ($rows as $row => $pixel) {
				if ($pixel) {
					return $this->_findBesidePixels($col, $row);
				}
			}
		}

		return false;
	}

	/**
	 * Возвращает массив непустых соседних пикселей
	 */
	protected function _findBesidePixels($col, $row) {
        // Карта новой картинки
		$image_map = array($col . '_' . $row);
        // Список пустых пикселей
        $null_pixels = array();
        // Список пикселей на проверку
        $for_check_pixels = array(
            ($col + 1) . '_' . $row,
            ($col - 1) . '_' . ($row + 1),
            $col . '_' . ($row + 1),
            ($col + 1) . '_' . ($row + 1),
        );
        while (count($for_check_pixels)) {
            $for_check_pixel = array_shift($for_check_pixels);
            $pixel = explode('_', $for_check_pixel);
            if (!isset($this->_img_array[$pixel[0]]) || !isset($this->_img_array[$pixel[0]][$pixel[1]])) {
                // Вышли за пределы поля изображения
                $null_pixels[] = $for_check_pixel;
            } else if (!$this->_img_array[$pixel[0]][$pixel[1]]) {
                // Пустой пиксель
                $null_pixels[] = $for_check_pixel;
            } else {
                // Не пустой пиксель
                $image_map[] = $for_check_pixel;
                // Добавляем все окружающие его пиксели на проверку
                $outer_pixels = array(
                    ($pixel[0] - 1) . '_' . ($pixel[1] - 1),
                    $pixel[0] . '_' . ($pixel[1] - 1),
                    ($pixel[0] + 1) . '_' . ($pixel[1] - 1),
                    ($pixel[0] - 1) . '_' . $pixel[1],
                    ($pixel[0] + 1) . '_' . $pixel[1],
                    ($pixel[0] - 1) . '_' . ($pixel[1] + 1),
                    $pixel[0] . '_' . ($pixel[1] - 1),
                    ($pixel[0] + 1) . '_' . ($pixel[1] + 1),
                );
                foreach ($outer_pixels as $outer_pixel) {
                    if (
                        !in_array($outer_pixel, $image_map)
                        && !in_array($outer_pixel, $null_pixels)
                        && !in_array($outer_pixel, $for_check_pixels)
                    ) {
                        $for_check_pixels[] = $outer_pixel;
                    }
                }
            }
        }
        $correct_map = array();
        foreach ($image_map as $pixel) {
            $pixel = explode('_', $pixel);
            if (!isset($correct_map[$pixel[0]])) {
                $correct_map[$pixel[0]] = array();
            }
            $correct_map[$pixel[0]][$pixel[1]] = true;
        }

        return $correct_map;
	}

    /**
     * Удаляет объект с карты
     * 
     * @param array $map
     * @param array $object
     */
    protected function _removeObject($object) {
        foreach ($object as $col => $rows) {
            foreach ($rows as $row => $pixel) {
                $this->_img_array[$col][$row] = false;
            }
        }
    }

    /**
     * Сохраняет объект в файл
     * 
     * @param array $object
     * @param string $image_path
     */
    protected function _objectToImage($object, $image_path) {
        // Находим минимальную и максимальную координату, ширину и высоту изображения
        $min_col = false;
        $min_row = false;
        $max_col = 0;
        $max_row = 0;
        foreach ($object as $col => $rows) {
            foreach ($rows as $row => $pixel) {
                 if ($min_col !== false) {
                     $min_col = min($min_col, $col);
                 } else {
                     $min_col = $col;
                 }
                 if ($min_row !== false) {
                     $min_row = min($min_row, $row);
                 } else {
                     $min_row = $row;
                 }
                 $max_col = max($max_col, $col);
                 $max_row = max($max_row, $row);
            }
        }
        $width = $max_col - $min_col;
        $height = $max_row - $min_row;
        // Создаем изображение png с нужной шириной и высотой
        $image = new Imagick();
        $draw = new ImagickDraw();
        $image->newImage($width, $height, new ImagickPixel('white'), 'png');
        $image->setImageColorspace(imagick::COLORSPACE_RGB);
        // Заполняем пиксели с карты объекта
        foreach ($object as $col => $rows) {
            foreach ($rows as $row => $pixel) {
                $pixel = $this->_img->getImagePixelColor($col, $row);
                $draw->setFillColor($pixel);
                $draw->point($col - $min_col, $row - $min_row);
            }
        }
        // Записываем изображение
        $image->drawImage($draw);
        $image->writeImage($image_path);
        $image->clear();
        $image->destroy();
    }

}
