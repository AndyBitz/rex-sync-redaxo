<?php 
  $auth = rex_config::get('rex-sync', 'auth');

  if (!!$auth == false || $_POST['generate-new-token'] == 1) {
    $auth = bin2hex(openssl_random_pseudo_bytes(24));
    rex_config::set('rex-sync', 'auth', $auth);
  }
?>
<form method="POST" action="index.php?page=rex-sync-redaxo/config">
  <fieldset class="form-horizontal">
    <div class="form-group">
      <label class="col-sm-2 control-label">AUTH-Token</label>
      <div class="col-sm-10">
          <input class="form-control" readonly="true" type="text" value="<?= $auth ?>">
      </div>
    </div>
    <div class="btn-toolbar">
      <button name="generate-new-token" value="1" class="btn btn-warning">
        New Token
      </button>
    </div>
  </fieldset>
</form>
