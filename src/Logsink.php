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
            },
            'log'
        );
    }
    
    public function newLog($body) {
        $task = [
            ':hostname' => $body['hostname'],
            ':service' => $body['service'],
            ':instance' => $body['instance'],
            ':time' => $body['time'],
            ':level' => $body['level'],
            ':msg' => $body['msg']
        ];
        
        $sql = 'INSERT INTO logs(
                    time,
                    hostname,
                    service,
                    instance,
                    level,
                    msg
                ) VALUES (
                    TO_TIMESTAMP(:time),
                    :hostname,
                    :service,
                    :instance,
                    :level,
                    :msg
                )';
        
        $q = $this -> pdo -> prepare($sql);
        $q -> execute($task);
    }
}

?>