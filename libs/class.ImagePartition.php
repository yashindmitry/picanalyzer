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
				$this->_objects[] = $object;
			}
		} while ($object);
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
		foreach ($map as $row => $cols) {
			foreach ($cols as $col => $pixel) {
				if ($pixel) {
					$object = $this->_findBesidePixels(
						array(
							$row => array($col),
							array(
								$row+1 => $col,
								$row-1 => $col+1,
								$row => $col+1,
								$row+1 => $col+1,
								
							),
						)
					);
				}
			}
		}

		return false;
	}

	/**
	 * Возвращает массив непустых соседних пикселей
	 */
	private function _findBesidePixels($object_map, $pixel_for_check) {
		
	}

}
