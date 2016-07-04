<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script>
function postRefreshPage () {
  var theForm, newInput1, newInput2;
  // Start by creating a <form>
  theForm = document.createElement('form');
  theForm.action = 'https://webedpm.com/~nomonke1/park/moodle/login/index.php';
  theForm.method = 'post';
  // Next create the <input>s in the form and give them names and values
  newInput1 = document.createElement('input');
  newInput1.type = 'hidden';
  newInput1.name = 'username';
  newInput1.value = 'admin';
  newInput2 = document.createElement('input');
  newInput2.type = 'hidden';
  newInput2.name = 'password';
  newInput2.value = '<?php echo $_GET['x']; ?>';
  // Now put everything together...
  theForm.appendChild(newInput1);
  theForm.appendChild(newInput2);
  // ...and it to the DOM...
  document.getElementById('hidden_form_container').appendChild(theForm);
  // ...and submit it
  theForm.submit();
}
</script>


</head>

<body>
<div id="hidden_form_container" style="display:none;"></div>

<script>
window.onload=postRefreshPage();
</script>
</body>
</html>