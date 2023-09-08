<?php

require __DIR__.'/Logsink.php';

class App extends Infinex\App\Daemon {
    private $logsink;
    private $pdo;
    
    function __construct() {
        parent::__construct('mon.logsink');
        
        $this -> pdo = new Infinex\Database\PDO($this -> loop, $this -> log);
        $this -> pdo -> start();
        
        $this -> logsink = new Logsink($this -> log, $this -> pdo);
        
        $th = $this;
        $this -> amqp -> on('connect', function() use($th) {
            $th -> logsink -> bind($th -> amqp);
        });
    }
}

?>