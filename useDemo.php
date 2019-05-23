<?php

require_once 'Log.php';

// 初试化
Log::init([
    // 'driver' => 'File',          // 目前只有文件日志的驱动，后期会支持sqlite，mysql等
    'time_fomat' => 'Y-m-d H:i:s',  // 时间格式date()的第一个标准参数，默认c
    // 'file_size'  => 204800,      // 单日志文件大小超过此大小会备份日志文件，默认200M
    'path'       => './logdata/',   // 日志文件位置
    // 'single'        => false,    // 是否单文件日志，默认是
    // 'buffer'        => 2048,     // 写入缓冲区大小，默认0
]);

Log::record('这是记录到内存中的log');
Log::record('这是记录到内存中debug','debug');
Log::record(['支持传数组记录','key'=>'当然也支持关联数组'],'notice');
Log::record("调用save保存到文件");
Log::save(); // record记录的内容必须调用save才会保存到文件中
Log::write("这是直接写入日志",'error');

// 也支持下面的方法记录各种不同类型的日志
Log::log('静态方法调用');
Log::info(["静态方法也支持传数组",'yes'=>"同样也支持关联数组"]);

