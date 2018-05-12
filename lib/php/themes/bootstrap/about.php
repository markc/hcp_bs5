<?php
// lib/php/themes/bootstrap/about.php 20150101 - 20180512
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_About extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
error_log(__METHOD__);

        // TODO change the a class btn links to form input submits
        return '
      <div class="col-12">
        <h3>About</h3>
        <p class="columns">
This is an example of a simple PHP7 "framework" to provide the core
structure for further experimental development with both the framework
design and some of the new features of PHP7.
        </p>
        <form method="post">
          <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
          <p class="text-center">
            <a class="btn btn-success" href="?o=about&l=success:Howdy, all is okay.">Success Message</a>
            <a class="btn btn-danger" href="?o=about&l=danger:Houston, we have a problem.">Danger Message</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'json\');return false;">JSON</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'head\');return false;">HTML</a>
            <a class="btn btn-secondary" href="" onclick="ajax(\'foot\');return false;">FOOT</a>
          </p>
        </form>
        <pre id="dbg"></pre>
      </div>
      <script>
function ajax(a) {
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
    x.send("o=about&x="+a);
    return false;
  }
}
      </script>';
    }
}

?>
