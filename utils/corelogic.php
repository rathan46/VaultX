<?php
require_once __DIR__ . '/datatable.php';

function normalizeCustId($custid, $max) {
    return abs($custid) % $max;
}

function encode($text, $custid) {
    global $series;

    $rb = normalizeCustId($custid, count($series));
    $c  = $series[$rb];

    $map = array_flip($c);
    $len = count($c);
    $out = '';

    foreach (str_split($text) as $i => $ch) {
        if (isset($map[$ch])) {
            $out .= $c[($map[$ch] + $custid + $i) % $len];
        } else {
            $out .= $ch;
        }
    }
    return $out;
}

function decode($text, $custid) {
    global $series;

    $rb = normalizeCustId($custid, count($series));
    $c  = $series[$rb];

    $map = array_flip($c);
    $len = count($c);
    $out = '';

    foreach (str_split($text) as $i => $ch) {
        if (isset($map[$ch])) {
            $out .= $c[($map[$ch] - $custid - $i + $len) % $len];
        } else {
            $out .= $ch;
        }
    }
    return $out;
}

function safe_encode_base64($b64, $custid) {
    $pad = '';
    while (substr($b64, -1) === '=') {
        $pad .= '=';
        $b64 = substr($b64, 0, -1);
    }
    return encode($b64, $custid) . $pad;
}

function safe_decode_base64($b64, $custid) {
    $pad = '';
    while (substr($b64, -1) === '=') {
        $pad .= '=';
        $b64 = substr($b64, 0, -1);
    }
    return decode($b64, $custid) . $pad;
}
