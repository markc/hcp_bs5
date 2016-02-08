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
        <p>'.$this->button('Success', 'submit', 'success', 'l', 'success:Howdy, all is okay.')
            .$this->button('Danger', 'submit', 'danger', 'l', 'danger:Houston, we have a problem.')
            .$this->button('API Debug', 'button', 'default', '', '', ' onclick="ajax()"').'
        </p>
      </form>
      <pre id="dbg"></pre>';
