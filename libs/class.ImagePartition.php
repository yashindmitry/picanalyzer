<?php

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
	public function getParts($path) {
		$this->_img = new Imagick($path);
		// Создаем карту пикселей
		$this->_img_array = $this->_getPixelMap($this->_img);
		// Ищем по карте объекты
		do {
			$object = $this->_findObject($this->_img_array);
			if ($object) {
                // Добавим объект
				$this->_objects[] = $object;
                // Удалим его с карты
                $this->_img_array = $this->_removeObject($this->_img_array, $object);
			}
		} while ($object);
        var_dump(count($this->_objects));exit;
	}

	/**
	 * Возвращает попиксельную карту картинки
	 */
	private function _getPixelMap($img) {
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
	private function _findObject($map) {
		foreach ($map as $col => $rows) {
			foreach ($rows as $row => $pixel) {
				if ($pixel) {
					return $this->_findBesidePixels($map, $col, $row);
				}
			}
		}

		return false;
	}

	/**
	 * Возвращает массив непустых соседних пикселей
	 */
	private function _findBesidePixels($map, $col, $row) {
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
            if (!isset($map[$pixel[0]]) || !isset($map[$pixel[0]][$pixel[1]])) {
                // Вышли за пределы поля изображения
                $null_pixels[] = $for_check_pixel;
            } else if (!$map[$pixel[0]][$pixel[1]]) {
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
    protected function _removeObject($map, $object) {
        foreach ($object as $col => $rows) {
            foreach ($rows as $row => $pixel) {
                $map[$col][$row] = false;
            }
        }

        return $map;
    }

}
