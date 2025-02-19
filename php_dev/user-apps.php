<?php
  require_once("init.php");
  if(!isset($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
  }
  $id = intval($_GET['id']) ?? NULL;
  if ($id === NULL or ctype_digit($_GET['id']) === false) {
    header("Location: pages/404.html");
    exit;
  }
  require_once("function.php");
  if ($id !== intval($_SESSION['user_id'])){
    $my_account = 0;
    $url = array(
      'userid' => $_SESSION['user_id'],
      'reciever' => $id
    );
    $chat_href = "chat.php?" . http_build_query($url, '', '&amp;');
  }
  else {
    $my_account = 1;
    $chat_href = 0;
  }
  $sql_user = "SELECT users.id, name, city, email, info, users.url, lock_id FROM users WHERE users.id = '$id';";
  $result_user = mysqli_query($connection, $sql_user);
  if (!$result_user) {
    exit;
  }
  $user = mysqli_fetch_assoc($result_user);
  $sql_applications = "SELECT applications.id AS appId, `date_creation`, applications.title AS title, `content`, applications.url, `user_id`, `subject_id`, users.id, subjects.id, subjects.title AS mainTitle, subjects.color_hex_id FROM `applications` JOIN users ON user_id = users.id JOIN subjects ON subjects.id = subject_id WHERE user_id = '$id';";
  $result_lots = mysqli_query($connection, $sql_applications);
  if (!$result_lots) {
    exit;
  }
  $apps = mysqli_fetch_all($result_lots, MYSQLI_ASSOC);

  $sql_user_subject = "SELECT subjects.id, subjects.title AS title, user_subjects.user_id AS sub_user_id, user_subjects.text, user_subjects.subject_id AS subject_id FROM subjects JOIN user_subjects ON subjects.id = user_subjects.subject_id WHERE user_subjects.user_id = '$id';";
  $result_user_subject = mysqli_query($connection, $sql_user_subject);
  if (!$result_user_subject) {
    exit;
  }
  $user_subjects = mysqli_fetch_all($result_user_subject, MYSQLI_ASSOC);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $safe_text = mysqli_real_escape_string($connection, $_POST['content']);
    $subject_id = intval($_POST['subject_id']);
    if (isset($_POST['status']) && intval($_POST['status']) === 1) {
      $sql = "UPDATE user_help_subjects SET text = '$safe_text' WHERE subject_id = '$subject_id' AND user_id = '$id';";
    }
    else {
      $sql = "UPDATE user_subjects SET text = '$safe_text' WHERE subject_id = '$subject_id' AND user_id = '$id';";
    }
    $result_subjects = mysqli_query($connection, $sql);
    if (!$result_subjects) {
      die("Ошибка" . mysqli_error($result_subjects));
    }
    else {
      header("Location: user-apps.php?id=" . $id);
    }
  }
  $content = include_template('user-apps.php', ['connection' => $connection, 'apps' => $apps, 'userSubjects' => $user_subjects, 'subjects' => $subjects, 'my_account' => $my_account, 'id' => $id, 'href' => $_SESSION['url'], 'visitor' => $_SESSION['user_id'], 'chat_href' => $chat_href, 'user' => $user]);
  $layout_content = include_template('layout.php', ['content' => $content, 'title' => 'Профиль', 'username' => $username, 'person_url' => $person_url, 'user_id' => $user_id]);
  print($layout_content);
?>
