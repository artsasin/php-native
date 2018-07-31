<?php

//$phar = new PharData(__DIR__ . '/' . '1533038629-80a2f030-7ec8-11e8-94ec-628caf01d019_sensor_log.tar.gz');
$phar = new PharData(__DIR__ . '/' . '1532873575-cfeed054-90de-11e8-9a23-5254a201cd00_sensor_log.tar.gz');
$csvFilename = $phar->current()->getFilename();
$phar->extractTo(__DIR__, $csvFilename);

$csv = fopen(__DIR__ .'/' . $csvFilename, 'r');

$skey = null;
$sindex = null;

$timestamp = [];

$run = true;

$avg = 0;
$sd = 0;
$pow_sum = 0;
$last = null;
$stm_index = null;
$stm_key = null;
$total = 0;
$min = 10000;
$max = -10000;

$counter = 0;

$row = fgetcsv($csv, 0, ';');
while ($row !== null && $run) {
    if ($stm_index !== intval($row[6])) {
        if (count($timestamp) > 0) {
            $counter++;
            echo $stm_key . ':' . $stm_index . PHP_EOL;
            echo sprintf("\t%-10s= %d\n", 'count', count($timestamp));
            $avg = $total/count($timestamp);
            echo sprintf("\t%-10s= %d\n", 'mean', $avg);
            echo sprintf("\t%-10s= %d\n", 'min', $min);
            echo sprintf("\t%-10s= %d\n", 'max', $max);
            $pow_sum = 0;
            foreach ($timestamp as $t) {
                $pow_sum = $pow_sum + pow(($t - $avg), 2);
            }
            $sd = sqrt($pow_sum / count($timestamp));
            echo sprintf("\t%-10s= %d\n", 'std', $sd);
            if ($counter >= 10) {
                $run = false;
            }
        }
        $stm_index = intval($row[6]);
        $stm_key = $row[5];
        $last = null;
        $total = 0;
        $min = 10000;
        $max = -10000;
        $avg = 0;
        $sd = 0;
        $pow_sum = 0;
        $timestamp = [];
    }

    if ($last === null) {
        $last = intval($row[0]);
    } else {
        $diff = floor((intval($row[0]) - $last) / 1000000);
        if ($min > $diff) {
            $min = $diff;
        }
        if ($max < $diff) {
            $max = $diff;
        }
        $timestamp[] = $diff;
        $total = $total + $diff;
        $last = intval($row[0]);
    }
    $row = fgetcsv($csv, 0, ';');


//    if ($last === 0) {
//        $last = intval($row[0]);
//    } else {
//        if ($skey !== $row[1] && $skey !== null) {
//            if (count($timestamp) > 0) {
//                $avg = ($total / count($timestamp));
//                $pow_sum = 0;
//                foreach ($timestamp as $t) {
//                    $pow_sum = $pow_sum + pow(($t - $avg), 2);
//                }
//                $sd = sqrt($pow_sum / count($timestamp));
//                echo "Module " . $skey . ":" . $sindex . PHP_EOL;
//                echo "\tcount = " . count($timestamp);
//                echo "\tmean = " . $avg;
//                echo "\tstd = " . $sd;
//                $run = false;
//            }
//            $total = 0;
//            $skey = $row[1];
//            $timestamp = [];
//            $sindex = $row[2];
//            $avg = 0;
//            $sd = 0;
//            $last = 0;
//        }
//        $v = floor(($row[0] - $last)/1000000);
//        $timestamp[] = $v;
//        $total = $total + $v;
//    }
}

fclose($csv);
unlink(__DIR__ .'/' . $csvFilename);
