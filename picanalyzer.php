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

$options = getopt('', array(
    // Разделить картинку на части
    'partition::',
    // Исходная картинка
    'partition-input::',
    // Директория с выходными файлами
    'partition-output::',
    // Объеденить картинки в одну
    'join::',
    // Директория с картинками
    'join-input::',
    // Выходная картинка
    'join-output::',
));

if (isset($options['partition'])) {
    if (!isset($options['partition-input'])) {
        error('Укажите исходную картинку в --partition-input');
    }
    if (!is_readable($options['partition-input'])) {
        error('Картинка ' . $options['partition-input'] . ' не найдена');
    }
    $options['partition-input'] = realpath($options['partition-input']);
    if (!isset($options['partition-output'])) {
        error('Укажите директорию для выходных файлов-картинок в --partition-output');
    }
    if (!is_writable($options['partition-output'])) {
        error('Директория ' . $options['partition-output'] . ' не найдена или нет прав на запись');
    }
    $options['partition-output'] = realpath($options['partition-output']) . '/';
    require_once ROOT . 'libs/class.ImagePartition.php';
    $image_partition = new ImagePartition();
    $image_partition->getParts($options['partition-input'], $options['partition-output']);
}

if (isset($options['join'])) {
    if (!isset($options['join-input'])) {
        error('Укажите директорию для входных файлов-картинок в --join-input');
    }
    if (!is_readable($options['join-input'])) {
        error('Директория ' . $options['join-input'] . ' не найдена или нет прав на чтение');
    }
    $options['join-input'] = realpath($options['join-input']) . '/';
    if (!isset($options['join-output'])) {
        error('Укажите путь к результирующей картинке в --join-output');
    }
    if (!is_writable(dirname($options['join-output']))) {
        error('Не могу записать в ' . $options['join-output']);
    }
    touch($options['join-output']);
    $options['join-output'] = realpath($options['join-output']);
    require_once ROOT . 'libs/class.ImageJoin.php';
    $image_join = new ImageJoin();
    $image_join->join($options['join-input'], $options['join-output']);
}

function error($text) {
    echo "\n", $text, "\n\n";
    exit;
}
