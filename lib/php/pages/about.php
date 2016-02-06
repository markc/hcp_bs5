<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// about.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2>About</h2>
      <p>
This is an example of a simple PHP7 "framework" to provide the core
structure for further experimental development with both the framework
design and some of the new features of PHP7.
      </p>
      <form method="post">
        <p>'.$t->button('Success', 'submit', 'success', 'l', 'success:Howdy, all is okay.')
            .$t->button('Danger', 'submit', 'danger', 'l', 'danger:Houston, we have a problem.')
            .$t->button('API Debug', 'button', 'default', '', '', ' onclick="ajax()"').'
        </p>
      </form>
      <pre id="dbg"></pre>
      <script>

function ajax() {
  if (window.XMLHttpRequest)  {
    var x = new XMLHttpRequest();
    x.open("POST", "", true);
    x.onreadystatechange = function() {
      if (x.readyState == 4 && x.status == 200) {
        document.getElementById("dbg").innerHTML = x.responseText
          .replace(/</g,"&lt;")
          .replace(/>/g,"&gt;")
          .replace(/\\\n/g,"\n")
          .replace(/\\\/g,"");
    }}
    x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    x.send("?p=about&a=json");
    return false;
  }
}
      </script>';
