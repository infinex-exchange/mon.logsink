<?php

require __DIR__.'/Logsink.php';

class App extends Infinex\App\App {
    private $pdo;
    private $logsink;
    private $cs;
    
    function __construct() {
        parent::__construct('mon.logsink');
        
        $this -> pdo = new Infinex\Database\PDO(
            $this -> loop,
            $this -> log,
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );
        
        $this -> logsink = new Logsink($this -> log, $this -> amqp, $this -> pdo);
        
        $this -> cs = new Infinex\App\ConditionalStart(
            $this -> loop,
            $this -> log,
            [
                $this -> amqp,
                $this -> pdo
            ],
            $this -> logsink
        );
    }
    
    public function start() {
        parent::start();
        $this -> pdo -> start();
        $this -> cs -> start();
    }
    
    public function stop() {
        $th = $this;
        
        $this -> cs -> stop() -> then(
            function() use($th) {
                return $th -> pdo -> stop();
            }
        ) -> then(
            function() use($th) {
                $th -> parentStop();
            }
        );
    }
    
    private function parentStop() {
        parent::stop();
    }
}

?>