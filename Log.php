<?php
/**
 * 自定义php日志类
 * 
 * @method void log($msg) static 记录一般日志
 * @method void error($msg) static 记录错误日志
 * @method void info($msg) static 记录一般信息日志
 * @method void sql($msg) static 记录 SQL 查询日志
 * @method void notice($msg) static 记录提示日志
 * @method void alert($msg) static 记录报警日志
 * @author Tan <smallcatx0@gmail.com>
 */
class Log {
    /**
     * 日志驱动类
     * @var File
     */
    protected static $driver;

    protected static $conf = [
        'driver' => 'File',
        'path' => './data/', // 默认记录日志路径
    ];

    protected static $log;
    /**
     * 定义的日志类型
     * @var array
     */
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];

    /**
     * 初始化配置
     * @param  array $conf 配置信息
     * @return void
     */
    public static  function init($conf = null){
        is_array($conf) && self::$conf = array_merge(self::$conf, $conf);
        // 创建驱动
        self::cDriver(self::$conf['driver']);
    }

    /**
     * 记录日志
     * @param  string|array $msg  日志信息
     * @param  string $type 日志类型
     * @return void
     */
    public static function record($msg,$type='log'){
        if(is_array($msg)){
            ! isset(self::$log[$type]) && self::$log[$type] = [];
            self::$log[$type] = array_merge(self::$log[$type],$msg);
        }elseif (is_string($msg)) {
            self::$log[$type][] = $msg;
        }
    }

    /**
     * 保存日志
     * @return booleam
     */
    public static function save(){
        if(empty(self::$log)){
            return true;
        }
        is_null(self::$driver) && self::cDriver(self::$conf['driver']);
        // TODO 日志过滤
        $log = self::$log;
        if ($res = self::$driver->save($log)) {
            self::$log = [];
        }
        return $res;
    }

    /**
     * 直接写入日志
     * @param  string $msg  待写入的数据
     * @param  string $type 类型
     * @return booleam   成功与否
     */
    public static function write($msg,$type='log'){
        self::record($msg,$type);
        return self::save();
    }

    /**
     * 查询记录在内存中的日志
     * @param  string $type 日志类型
     * @return string|array
     */
    public static function get($type = null){
        if (empty($type)) {
            return self::$log;
        }elseif (array_key_exists($type, self::$log)) {
            return self::$log[$type];
        }else{
            return '';
        }
    }

    /**
     * 清空内存中的日志
     * @return void
     */
    public static function clear(){
        self::$log = [];
    }

    /**
     * 创建驱动
     * @param  string $driver 驱动的类型
     * @return void
     */
    public static function cDriver($driver){
        if (is_string($driver)) {
            require_once 'dirver/'.$driver.'.php';
            self::$driver = new $driver(self::$conf);
        }else{
            self::$driver = $driver;
        }
    }


     /**
     * 静态方法调用
     * @access public
     * @param  string $method 调用方法
     * @param  mixed  $args   参数
     * @return void
     */
    public static function __callStatic($method, $args){
        if (in_array($method, self::$type)) {
            self::write($args[0],$method);
        }
    }

}

