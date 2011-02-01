This is a simple to use Command Line Interface ( CLI ) PHP class that allows for user input.

This script can take parameters to your script in 3 different ways...

1.)  User Input

   - With this method, you can allow your script to ask the user for the parameters for your script.

<?php
  require_once('cli.php');

  // Create a new CLI object.
  $cli = new CLIPHP();

  // Ask the user for their first name.
  $cli->get('first', "What is your first name?");

  // Ask the user for their last name.
  $cli->get('last', "What is your last name?");

  // Now print the results.
  print $cli->args['first'];
  print $cli->args['last'];
?>

2.)  Cached User Input

   - With this method, you can cache the user results inside of a "config" file on the root path of where this script is ran.
   - Using this method, they will be asked for the parameters only once, and then every other time the script is ran, the 
     cached results will populate the parameters.  Like so...

<?php
  require_once('cli.php');

  // Create a new CLI object.
  $cli = new CLIPHP();

  // Ask the user for their first name.  PASS TRUE TO CACHE THE RESULT!!!
  $cli->get('first', "What is your first name?", TRUE);

  // Ask the user for their last name.  PASS TRUE TO CACHE THE RESULT!!!!
  $cli->get('last', "What is your last name?", TRUE);

  // Now print the results.
  print $cli->args['first'];
  print $cli->args['last'];

  // Now create a separate CLI object.
  $cli2 = new CLIPHP();
  
  // Print's the cached results from the first object.
  print $cli2->args['first'];
  print $cli2->args['last'];
?>

3.)  Direct arguments...

   - With this, you can just pass the arguments directly using the "-param value" method.

php mycli.php -name "Travis" -last "Tidwell"

   - Then inside of your script, you just need to provide the following..
   - It will first check to see if values have been passed as arguments, and then if so, it will use those instead.


<?php
  require_once('cli.php');

  // Create a new CLI object.
  $cli = new CLIPHP();

  // Ask the user for their first name.
  $cli->get('first', "What is your first name?");

  // Ask the user for their last name.
  $cli->get('last', "What is your last name?");

  // Now print the results.
  print $cli->args['first'];
  print $cli->args['last'];
?>