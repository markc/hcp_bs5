<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// auth/newpw.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
        <h2>New Password</h2>
        <form method="post">
          <input type="hidden" name="i" value="'.$id.'">
          <p style="text-align:center"><b>' . $uid . '</b></p>
          <p>
            <label for="passwd1">Password</label>
            <input type="password" name="passwd1" id="passwd1" placeholder="New Password" value="" required autofocus>
          </p>
          <p>
            <label for="passwd2">Confirm Password</label>
            <input type="password" name="passwd2" id="passwd2" placeholder="Confirm Password" value="" required>
          </p>
          <p style="text-align:right">' .
           . $this->button('Reset my password', 'submit', 'primary') . '
          </p>
        </form>';
