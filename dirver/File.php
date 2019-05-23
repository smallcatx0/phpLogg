<?php

/**
 * 文件日志驱动
 */
class File {

    protected $conf=[
        'time_fomat' => 'c',
        'file_size' => 204800, // 单日志文件200M
        'path' => '../data/',  // 默认文件位置
        'single' => true,
        'buffer' => 0,
    ];
    protected $filePath ;      // 日志文件路径
    protected $writeStr = '';  // 待写入文件的字符串

    public function __construct($conf=[]){
        is_array($conf) && $this->conf = array_merge($this->conf, $conf);
        // 判断是否单文件日志
        if ($this->conf['single']) {
            $this->filePath = $this->conf['path'] . 'curr.log';
        }else{
            $filename =  date('Ym') . '/' . date('d') . '.log';
            $this->filePath = $this->conf['path'] .$filename;
        }
        // 父目录不存在，则创建
        $path = dirname($this->filePath);
        !is_dir($path) && mkdir($path,'0755',true);
    }
    /**
     * 保存日志
     * @param  array $log
     * @return booleam   成功与否
     */
    public function save($log){
        // 检测日志文件大小，超过配置则本分
        if (is_file($this->filePath) && floor($this->conf['file_size'] <= filesize($this->filePath))) {
            $bak = dirname($this->filePath) .'/'. time() . basename($this->filePath);
            rename($this->filePath, $bak);
        }

        // 开始拼接写入字符串
        $this->writeStr .= "================================="
                            ."=================================\n"
                            .'[ '.date($this->conf['time_fomat'])." ]\n";
        foreach ($log as $type => $items) {
            $str = '';
            foreach ($items as $key => $item) {
                if(is_numeric($key)){
                    $str .= "[ {$type} ] ". $item ."\n";
                }elseif(is_string($key)){
                    $str .= "[ {$type} ] [ {$key} ] ". $item ."\n";
                }
            }
            $this->writeStr .= $str;
        }
        return $this->write();
    }

    /**
     * 将字符串追加文件中
     * @param  [type] $fPath [description]
     * @param  [type] $msg   [description]
     * @return [type]        [description]
     */
    public function write($fPath='',$msg=''){
        $fPath = empty($fPath) ? $this->filePath : $fPath;
        if(empty($msg)){
            // 检测缓冲区大小,判断是否写入磁盘
            if(count($this->writeStr) < $this->conf['buffer']){
                return true;
            }else{
                $fp = fopen($fPath, 'ab');
                fwrite($fp,$this->writeStr);
                fclose($fp);
                $this->writeStr = '';
                return true;
            }
        }else{
            $fp = fopen($fPath, 'ab');
            fwrite($fp,$msg);
            fclose($fp);
            $this->writeStr = '';
            return true;
        }
    }

    function __destruct(){
        // 判断缓冲区是否有文件
        if (!empty($this->writeStr)) {
            $fp = fopen($this->filePath, 'ab');
            fwrite($fp,$this->writeStr);
            fclose($fp);
        }
    }
}
