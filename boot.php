<?php
  $authKey = rex_config::get('rex-sync', 'auth');
  $isRexSyncRequest = isset($_POST['rex-sync-request']) ? $_POST['rex-sync-request'] : null;

  if ($isRexSyncRequest) {
    function createDirIfNotExisting($path) {
      $info = pathinfo($path);
      if (is_dir($info['dirname']) === false) {
        return mkdir($info['dirname']);
      }
      return true;
    }

    header('Content-Type: application/json');

    // authorization
    if ($_SERVER['HTTP_AUTH'] !== $authKey) {
      http_response_code(401);
      echo '{ "error": "unauthorized" }';
      die();
    }

    // data
    $type = $_POST['type'];
    $path = $_POST['path'];
    $event = $_POST['event'];
    $file = $_FILES['file'];

    // handle assets
    if ($type === 'assets') {
      $path = rex_path::base('assets'.$path);

      if ($event === 'unlink') {
        if (unlink($path) === false) {
          echo '{ "error": "failed to delete file" }';
          die();
        }
      } else {
        if (createDirIfNotExisting($path) === false) {
          echo '{ "error": "failed to create directory" }';
          die();
        }

        if (move_uploaded_file($file['tmp_name'], $path) === false) {
          echo '{ "error": "failed to move uploaded file" }';
          die();
        }
      }
    }

    // handle addons
    if ($type === 'addons') {
      $path = rex_path::src('addons'.$path);

      if ($event === 'unlink') {
        if (unlink($path) === false) {
          echo '{ "error": "failed to delete file" }';
          die();
        }
      } else {
        if (createDirIfNotExisting($path) === false) {
          echo '{ "error": "failed to create directory" }';
          die();
        }

        if (move_uploaded_file($file['tmp_name'], $path) === false) {
          echo '{ "error": "failed to move uploaded file" }';
          die();
        }
      }
    }

    // handle templates
    if ($type === 'templates') {
      $name = pathinfo($path)['filename'];

      if ($event === 'unlink') {
        // delete template
        // don't delete for now. too many things can break
        // $sql = rex_sql::factory();
        // $sql->setTable('rex_template');
        // $sql->setWhere('name="'.$name.'"');
        // $sql->select();

        // $id = $sql->getValue('id');
        // $id = $sql->getValue('id');
        // $template = new rex_template($id);
        // $template->deleteCache();

        // $sql = rex_sql::factory();
        // $sql->setTable('rex_template');
        // $sql->setWhere('name="'.$name.'"');
        // $sql->delete();
      } else {
        $contents = file_get_contents($file['tmp_name']);
        if ($contents === false) {
          echo '{ "error": "failed to read uploaded file" }';
          die();
        }

        // check if template already exists
        $sql = rex_sql::factory();
        $sql->setTable('rex_template');
        $sql->setWhere('name="'.$name.'"');
        $sql->select();

        $rows = $sql->getRows();

        // name, content
        if ($rows <= 0) {
          // create template
          $sql = rex_sql::factory();
          $sql->setTable('rex_template');
          $sql->setValue('name', $name);
          $sql->setValue('content', $contents);
          $sql->insert();
        } else {
          // update template
          $sql = rex_sql::factory();
          $sql->setTable('rex_template');
          $sql->setWhere('name="'.$name.'"');
          $sql->setValue('content', $contents);
          $sql->update();
        }

        $sql = rex_sql::factory();
        $sql->setTable('rex_template');
        $sql->setWhere('name="'.$name.'"');
        $sql->select('id');

        $id = $sql->getValue('id');
        $template = new rex_template($id);
        $template->deleteCache();
      }
    }

    // handle modules
    if ($type === 'modules') {
      if ($event === 'unlink') {
        // ignore for yet
      } else {
        $contents = file_get_contents($file['tmp_name']);
        if ($contents === false) {
          echo '{ "error": "failed to read uploaded file" }';
          die();
        }

        $area = pathinfo($path)['filename']; // input or output
        $name = explode('/', $path)[1];

        if ($area !== 'output' && $area !== 'input') {
          echo '{ "error": "module must either be input.php or output.php" }';
          die();
        }

        // exists
        $sql = rex_sql::factory();
        $sql->setTable('rex_module');
        $sql->setWhere('name="'.$name.'"');
        $sql->select('id');

        $rows = $sql->getRows();

        if ($rows <= 0) {
          // create module
          $sql = rex_sql::factory();
          $sql->setTable('rex_module');
          $sql->setValue('name', $name);
          $sql->setValue($area, $contents);
          $sql->insert();
        } else {
          // update module
          $sql = rex_sql::factory();
          $sql->setTable('rex_module');
          $sql->setWhere('name="'.$name.'"');
          $sql->setValue($area, $contents);
          $sql->update();
        }

       rex_delete_cache();
      }
    }

    echo '{ "success": true }';
    die();
  }
?>
