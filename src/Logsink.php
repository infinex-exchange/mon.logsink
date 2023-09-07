<?php

class Logsink {
    private $log;
    private $pdo;
    
    function __construct($log, $pdo) {
        $this -> log = $log;
        $this -> pdo = $pdo;
        
        $this -> log -> debug('Initialized log sink');
    }
    
    public function bind($amqp) {
        $th = $this;
        
        $amqp -> sub(
            'log',
            function($body) use($th) {
                return $th -> newLog($body);
            }
        );
    }
    
    public function newLog($body) {
        $task = [
            ':service' => $body['service'],
            ':hostname' => $body['hostname'],
            ':instance' => $body['instance'],
            ':time' => $body['time'],
            ':level' => $body['level'],
            ':msg' => $body['msg']
        ];
        
        $sql = 'INSERT INTO logs(
                    service,
                    hostname,
                    instance,
                    time,
                    level,
                    msg
                ) VALUES (
                    :service,
                    :hostname,
                    :instance,
                    TO_TIMESTAMP(:time),
                    :level,
                    :msg
                )';
        
        $q = $this -> pdo -> prepare($sql);
        $q -> execute($task);
    }
}

?>