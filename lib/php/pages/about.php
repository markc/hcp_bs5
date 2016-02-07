<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// about.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return $t->about() . '
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
