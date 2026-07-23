<?php
/*
 * Created on 01-Mar-09
 *
 * An easy way to keep in track of external processes.
 * Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
 * @compability: Linux only. (Windows does not work).
 * @author: Ratheesh Kumar CK <ratheesh@saturn.in>
 */

class Process{
    private $pid;
    private $command;

    public function __construct($cl=false){
        if ($cl != false){
            $this->command = $cl;
            $this->runCom();
        }
    }
    private function runCom(){
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
        exec($command ,$op);
        $this->pid = (int)$op[0];
    }

    public function setPid($pid){
        $this->pid = $pid;
    }

    public function getPid(){
        return $this->pid;
    }

    public function status(){
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1]))return false;
        else return true;
    }

    public function start(){
        if ($this->command != '')$this->runCom();
        else return true;
    }

    public function stop(){
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false)return true;
        else return false;
    }
}

/**
* Is Windows
*
* Tells if we are running on Windows Platform
* @author raccettura
*/
if (!function_exists('is_windows')){
	function is_windows(){
	    if(PHP_OS == 'WINNT' || PHP_OS == 'WIN32'){
	        return true;
	    }
	    return false;
	}
}


/**
* Launch Background Process
*
* Launches a background process (note, provides no security itself, $call must be sanitized prior to use)
* @param string $call the system call to make
* @author racexecettura
*/
/*function launchBackgroundProcess($call) {

    // Windows
    if(is_windows()){
        pclose(popen('start /b '.$call.'', 'r'));
    }

    // Some sort of UNIX
    else {
        pclose(popen($call.' /dev/null &#038;', 'r'));
    }
    return true;
}
*/

define('PROCESS_CLASS', true);
?>
