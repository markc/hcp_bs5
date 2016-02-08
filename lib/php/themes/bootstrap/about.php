<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// bootstrap/about.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2><i class="fa fa-question-circle fa-fw"></i> About</h2>
      <p>
This is an example of a simple PHP7 "framework" to provide the core
structure for further experimental development with both the framework
design and some of the new features of PHP7.
      </p>
      <form method="post">
        <p>
          <button class="btn btn-primary btn-success" type="submit" name="l" value="success:Howdy, all is okay.">Success</button>
          <button class="btn btn-primary btn-danger" type="submit" name="l" value="danger:Houston, we have a problem.">Danger</button>
          <button class="btn btn-primary btn-default" type="button" name="" onclick="ajax()">API Debug</button>
        </p>
      </form>
      <pre id="dbg"></pre>';
