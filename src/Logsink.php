<?php

class Logsink {
    private $log;
    private $amqp;
    private $pdo;
    
    function __construct($log, $amqp, $pdo) {
        $this -> log = $log;
        $this -> amqp = $amqp;
        $this -> pdo = $pdo;
        
        $this -> log -> debug('Initialized log sink');
    }
    
    public function start() {
        $th = $this;
        
        return $this -> amqp -> sub(
            'log',
            function($body) use($th) {
                return $th -> newLog($body);
            }
        ) -> then(
            function() use($th) {
                $th -> log -> info('Started log sink');
            }
        ) -> catch(
            function($e) use($th) {
                $th -> log -> error('Failed to start log sink: '.((string) $e));
                throw $e;
            }
        );
    }
    
    public function stop() {
        $th = $this;
        
        return $this -> amqp -> unsub('log') -> then(
            function() use ($th) {
                $th -> log -> info('Stopped log sink');
            }
        ) -> catch(
            function($e) use($th) {
                $th -> log -> error('Failed to stop log sink: '.((string) $e));
            }
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