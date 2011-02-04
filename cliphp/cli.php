<?php

class CLIPHP {
  var $args = array();

  public function __construct() {
    global $argv;
    set_time_limit(0);

    // Try the config file first.
    $handle = fopen( dirname(__FILE__) . '/config', 'a+');
    if ($handle) {
      while (($buffer = fgets($handle, 4096)) !== false) {
        $values = explode('=', $buffer);
        $this->args[trim($values[0])] = trim($values[1]);
      }
      fclose($handle);
    }

    // Now try the arguments.
    $name = '';
    foreach( $argv as $arg ) {
      if (substr($arg, 0, 1) === '-') {
        $name = substr($arg, 1);
      }
      else if( $name ) {
        $this->args[$name] = $arg;
        $name = '';
      }
    }
  }

  public function read() {
    $fp = fopen("php://stdin", "r");
    $in = fgets($fp, 4094);
    fclose($fp);
    return trim($in);
  }

  public function cache( $name, $value ) {
    $handle = fopen( dirname(__FILE__) . '/config', 'a+');
    if ($handle) {
      fwrite($handle, $name . '=' . $value . "\n");
      fclose($handle);
    }
  }

  public function set( $name, $value, $cache = FALSE ) {
    if( !isset($this->args[$name]) ) {
      if( $cache ) {
        $this->cache( $name, $value );
      }
    }
    $this->args[$name] = $value;
  }

  public function get( $name, $output, $cache = FALSE,$lf = FALSE ) {
    if (!isset($this->args[$name])) {
      echo $output;
      if ($lf) {
        echo "\n";
      }
      $this->set( $name, $this->read(), $cache );
    }
    return $this->args[$name];
  }

}

?>
