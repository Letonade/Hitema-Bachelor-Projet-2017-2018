<<?php
class Push_Alerts{

  public function __construct(){
    push_alert_start();
  }

  public function setMessage($message, $type = 'error'){
    $_PUSH['alert'] = array(
      'message' => $message,
      'type' => $type
    );
  }

  public function message(){
    if(isset($_PUSH['alert'])){
      ?>
      <div class="alert alert-<?php echo $_PUSH['alert']['type'];
      ?>">
        <a class="close">x</a>
        <<?php echo $_PUSH['alert']['message']; ?>
      </div>
      <<?php
      unset($_PUSH['alert']);
    }
  }

}

 ?>
