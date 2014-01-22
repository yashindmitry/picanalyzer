<?php

define('ROOT', dirname(__FILE__) . '/');

require_once ROOT . 'libs/class.ImagePartition.php';
$image_partition = new ImagePartition();

$options = getopt('', array(
    'partition::',
    'image:',
));

if (isset($options['partition'])) {
    $image = array_pop($argv);
    $image_partition->getParts(ROOT . $options['image']);
}
