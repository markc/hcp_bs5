<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// auth/signin.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2>Sign in</h2>
      <form method="post">
        <p>
          <label for="uid">Login ID</label>
          <input type="text" name="uid" id="uid" placeholder="Your Email Address" value="' . $uid  . '" required autofocus>
        </p>
        <p>
          <label for="uid">Password</label>
          <input type="password" name="webpw" id="webpw" placeholder="Your Password">
        </p>
        <p>
          <input type="checkbox" name="remember" id="remember" value="yes"> Remember me on this computer
        </p>
        <p style="text-align:right">'
           . $this->a('?o=auth&amp;m=forgotpw', 'Forgot password')
           . $this->button('Log me in', 'submit', 'primary') . '
        </p>
      </form>';

