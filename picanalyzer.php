<?php

/**
 * PicAnalyzer
 *
 * @author Dmitry Yashin <to.yashin@gmail.com>
 * @link http://jonnyb.ru/picanalyzer/
 * @copyright 2014 jonnyb.ru
 * @license GNU GENERAL PUBLIC LICENSE Version 2
 */

define('ROOT', dirname(__FILE__) . '/');

require_once ROOT . 'libs/class.ImagePartition.php';
$image_partition = new ImagePartition();

$options = getopt('', array(
    // Разделить картинку на части
    'partition::',
    // Исходная картинка
    'image:',
    // Директория с выходными файлами
    'output::',
));

if (isset($options['partition'])) {
    if (!isset($options['image'])) {
        error('Укажите аттрибут image');
    }
    if (!is_readable($options['image'])) {
        error('Картинка ' . $options['image'] . ' не найдена');
    }
    $options['image'] = realpath($options['image']);
    if (!is_writable($options['output'])) {
        error('Директория ' . $options['output'] . ' не найдена или нет прав на запись');
    }
    $options['output'] = realpath($options['output']) . '/';
    $image_partition->getParts($options['image'], $options['output']);
}

function error($text) {
    echo "\n", $text, "\n\n";
    exit;
}
