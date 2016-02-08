<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// auth/forgotpw.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2>Reset password</h2>
      <form method="post">
        <p>
          <label for="uid">Email Address</label>
          <input type="email" name="uid" id="uid" placeholder="Your Email Address" value="' . $uid . '" required autofocus>
        </p>
        <p>
You will receive an email with further instructions and please
note that this only resets the password for this website interface.
        </p>
        <p style="text-align:right">'
           . $this->a('?o=auth&amp;m=signin', '&laquo; Back')
           . $this->button('Send', 'submit', 'primary') . '
        </p>
      </form>';
