<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

foreach (glob(__DIR__ . "/*.php") as $filename) {
    if (!strstr($filename, "app/configs/autoload.php")) {
        include_once $filename;
    }
}