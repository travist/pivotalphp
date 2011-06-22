<?php
/**
 * The CLIPHP class is an easy to use php-cli class that allows your php cli
 * scripts to gather user input and manage arguments for your script.  Please
 * refer to the README.txt for documentation on usage.
 */
class CLIPHP {
  
  // The arguments for this script.
  var $args = array();

  // Constructor.
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

  /**
   * Reads input from the user.
   * 
   * @return string - The user entered input. 
   */
  public function read() {
    $fp = fopen("php://stdin", "r");
    $in = fgets($fp, 4094);
    fclose($fp);
    return trim($in);
  }

  /**
   * Reads silent input from the user.  Good for passwords, etc.
   * 
   * Should work on UNIX/DOS 
   * http://blogs.sitepoint.com/interactive-cli-password-prompt-in-php/
   * 
   * @param string - The prompt to give to the user to enter in their data.
   * @return string - The user entered input.
   */
  public function readSilent($prompt) {
    if (preg_match('/^win/i', PHP_OS)) {
      $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
      file_put_contents(
        $vbscript, 'wscript.echo(InputBox("'
        . addslashes($prompt)
        . '", "", "password here"))');
      $command = "cscript //nologo " . escapeshellarg($vbscript);
      $in = rtrim(shell_exec($command));
      unlink($vbscript);
    } else {
      $command = "/usr/bin/env bash -c 'echo OK'";
      if (rtrim(shell_exec($command)) !== 'OK') {
        trigger_error("Can't invoke bash");
        return;
      }
      $command = "/usr/bin/env bash -c 'read -s -p \""
        . addslashes($prompt)
        . "\" mypassword && echo \$mypassword'";
      $in = rtrim(shell_exec($command));
    }      
    return $in;
  }
  
  /**
   * Caches user input based on the name of the parameter and the value.
   * This will add the key=value pairs to a config file within the cliphp
   * library directory.
   * 
   * @param string - The name of the parameter.
   * @param string - The value of that parameter.
   */
  public function cache( $name, $value ) {
    $handle = fopen( dirname(__FILE__) . '/config', 'a+');
    if ($handle) {
      fwrite($handle, $name . '=' . $value . "\n");
      fclose($handle);
    }
  }

  /**
   * Sets the value of an argument within the args array and caches that value
   * if the user provided to do so.
   * 
   * @param string - The name of the parameter.
   * @param string - The value of the parameter.
   * @param string - Whether or not we should cache the response.
   */
  public function set( $name, $value, $cache = FALSE ) {
    if( !isset($this->args[$name]) ) {
      if( $cache ) {
        $this->cache( $name, $value );
      }
    }
    $this->args[$name] = $value;
  }

  /**
   * The main API for this class.  This will get a response from the user, or
   * return the cached value for a parameter based on whether or not it exists
   * in cache or was passed directly to the script.
   * 
   * @param string - The name of the parameter to get.
   * @param string - What should be asked to the user if the parameter is not set.
   * @param string - Whether the response should be cached or not.
   * @param string - If the input should be silenced meaning they cannot see what they type.
   * 
   * @return string - The value of the parameter to get. 
   */
  public function get( $name, $prompt, $cache = FALSE, $silent = FALSE ) {
    
    // See if we already have this argument set.
    if (!isset($this->args[$name])) {
      if( $silent ) {
        $this->set( $name, $this->readSilent($prompt), $cache );
      }
      else {
        echo $prompt;
        $this->set( $name, $this->read(), $cache );
      }
    }
    
    // Return the value of this argument.
    return $this->args[$name];
  } 
}

?>
