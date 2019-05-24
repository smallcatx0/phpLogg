<?php

/**
 * sqlite3的驱动
 */
class Sqlite {

    protected $conf = [
        'path' => '../logdata/',  // 默认文件位置
        'single' => true,      // 默认支持单文件
    ];
    /**
     * sqlite3 对象
     * @var SQLite3
     */
    protected $db;
    // 入库的sql头信息
    private $inSqls = "INSERT INTO main.log('type','key','value','time','log_id') VALUES "; 
    
    function __construct($conf){
        is_array($conf) && $this->conf = array_merge($this->conf, $conf);
        // 判断是否单文件日志
        if ($this->conf['single']) {
            $dbFile = $this->conf['path'] . 'curr.log.db';
        }else{
            $filename =  date('Ym') . '/' . date('d') . '.log.db';
            $dbFile = $this->conf['path'] .$filename;
        }
        // 父目录不存在，则创建
        $path = dirname($dbFile);
        !is_dir($path) && mkdir($path,'0755',true);
        // 初始化数据库
        $this->initDb($dbFile);
    }
    
    /**
     * 初始化数据库
     *
     * @param string $dbFile 数据库文件位置
     * @return boolean 成功与否
     */
    protected function initDb($dbFile){
        $initSql = '
        CREATE TABLE IF NOT EXISTS "log" (
            "id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "type"  TEXT,
            "key"  TEXT,
            "value"  TEXT,
            "time"  TEXT,
            "log_id"  TEXT
        );
        ';
        $this->db = new SQLite3($dbFile);
        return $this->db->exec($initSql);
    }


    /**
     * 保存日志
     *
     * @param array $log 日志数据
     * @return boolean
     */
    public function save($log){
        $time = time();
        $unid = $this->unId();
        foreach ($log as $type => $items) {
            foreach ($items as $key => $content) {
                if(is_numeric($key)) $key = null;
                // 拼接sql
                $this->inSqls .= "('{$type}','{$key}','{$content}','{$time}','{$unid}'),";
            }
        }
        return $this->execSql();
    }

    /**
     * 执行sql如果参数为空，则执行缓存中的sql并将缓存清空
     *
     * @param string $sql 待执行的sql
     * @return boolean
     */
    public function execSql($sql = '') {
        if(!empty($sql)){
            return $this->db->exec($sql);
        }
        $sql = rtrim($this->inSqls,',');
        if( $res = $this->db->exec($sql)){
            // 执行成功则清除缓存
            $this->inSqls =  "INSERT INTO main.log('type','key','value','time','log_id') VALUES ";
        }
        return $res;
    }

    /**
     * 获取全局唯一id
     *
     * @return string
     */
    public function unId(){
        $startYear = 2018;
        $yCode = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
        $order_sn = '' 
            .$yCode[intval(date('Y')-$startYear)]   // 年(1)
            .\dechex(date('m'))                     // 月(1)
            .date('d')                              // 日(2)
            .sprintf('%02s',dechex(\rand(0,255)))  // 00-ff随机数(2)
            .\substr(\microtime(),2,5)              // 毫秒(5)
            .\substr(time(),-5)                     // 秒(5)
        ;
        return $order_sn;
    }

    function __destruct(){
        if ( ($this->db instanceof SQLite3)) {
            $this->db->close();
        }
    }
}