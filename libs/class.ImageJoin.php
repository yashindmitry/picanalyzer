<?php

/**
 * PicAnalyzer
 *
 * @author Dmitry Yashin <to.yashin@gmail.com>
 * @link http://jonnyb.ru/picanalyzer/
 * @copyright 2014 jonnyb.ru
 * @license GNU GENERAL PUBLIC LICENSE Version 2
 */

class ImageJoin {

    protected
        $_image_instances = array(); // Инстансы объект Imagick с входными картинками

    public function join($input_path, $image_path) {
        $images = glob($input_path . '/*.{jpg,jpeg,gif,png}', GLOB_BRACE | GLOB_ERR);
        natcasesort($images);
        $im = new Imagick($images);
        $im->resetIterator();
        $combined = $im->appendImages(false);
        $combined->setImageFormat('png');
        $combined->writeImage($image_path);
        $combined->clear();
        $combined->destroy();
        $im->clear();
        $im->destroy();
    }

}
