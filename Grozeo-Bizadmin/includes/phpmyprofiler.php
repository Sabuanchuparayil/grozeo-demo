<?php
/***
 * parameter $log should be a directory with the trailing slash, since the logs would be real heavy
 * and as of now the directory should be writable by the webserver process.. 
 * a file will be created under the directory foreach host with value in $_SERVER['HTTP_HOST']
 */
class phpMyProfiler
{
    private $link = false;
    private $error;
    private $log;
    
    function __construct($host, $user, $pass, $log = false){
        $this->log = ($log === false) ? '/tmp/' : $log;
        if($host){
            $this->link = mysqli_connect($host, $user, $pass) or die(mysql_error());
            $this->startProfiling();
        }
    }
    
    function setLink(&$link){
        if($this->link)
	        $this->stopProfiling();
	        
	    $this->link = $link;
	    $this->startProfiling();
    }

    function __destruct(){
        $this->log();
    }
    private function startProfiling(){
       // mysqli_query($this->link, 'set profiling_history_size=100');
        mysqli_query($this->link, 'set profiling=1');
    }
    private function stopProfiling(){
        mysqli_query($this->link, 'set profiling=0');
    }
    
    private function collectData(){
       $rv = array();

       $rs = mysqli_query($this->link, 'show profiles');
        while($rd = mysqli_fetch_assoc($rs)){
            //if($rd['Query_ID'] == 0) continue;
            if($detail = $this->getDetails($rd['Query_ID']))
                $rd['detail'] = $detail;
            $rv[] = $rd;
        }
	    mysqli_free_result($rs);
        return $rv;
    }
    
    private function getDetails($qid){
	    //$this->debug(__LINE__ . ' :: ' . __METHOD__);
            $rsd = mysqli_query($this->link, 'select min(seq) seq,state,count(*) numb_ops, '
                . 'round(sum(duration),5) sum_dur, round(avg(duration),5) avg_dur, '
                . 'round(sum(cpu_user),5) sum_cpu, round(avg(cpu_user),5) avg_cpu '
                . 'from information_schema.profiling '
                . 'where query_id = ' . $qid
                . ' group by state order by seq');
            $rsv = array();
            while($rdd = mysqli_fetch_assoc($rsd))
                $rsv[] = $rdd;
            return $rsv;
    }
    
    public function log(){

        if(!$this->link or !$this->log)
            return;
        $this->stopProfiling();
        $data['instance'] = array('timestamp' => time(), 'request' => $_SERVER['REQUEST_URI' ]);


	//ob_start();
        $data['profiles'] = $this->collectData();

	//ob_get_clean();
    //print_r($data);


        if(empty($data['profiles']) or count($data['profiles']) == 0) return;

        $logFile = $this->log . '/'.$_SERVER['HTTP_HOST'] .'-' . date('Ymd-G') . '.txt';
        $logData = base64_encode(gzcompress(serialize($data))) . "\n";
        if(!file_exists($logFile)){
            file_put_contents($logFile, '#PhpMyProfiler' . "\n");
        }

        file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);
        $this->log = false; // dont want to call a second time
    }
}
?>
