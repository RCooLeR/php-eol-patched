<?php
$results = array();
$large = isset($argv[1]) ? (int)$argv[1] : 104857600;
foreach (array('UTF-8', 'ASCII', 'UTF-16LE', 'UTF-7') as $encoding) {
    $results[$encoding] = array(mb_str_split('', 1, $encoding), mb_str_split('', $large, $encoding), mb_strcut('', 0, 1000, $encoding));
}
echo json_encode($results), "\n";
