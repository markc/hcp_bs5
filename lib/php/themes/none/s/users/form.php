<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users/form.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <form method="post">
        <p>
          <label for="id">ID</label> <span><strong>' . $id . '</strong></span>
        </p>
        <p>
          <label for="uid">Uid</label>
          <input type="text" id="uid" name="uid" value="' . $uid . '">
        </p>
        <p>
          <label for="gid">Gid</label>
          <input type="text" id="gid" name="gid" value="' . $gid . '">
        </p>
        <p>
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="' . $username . '">
        </p>
        <p>
          <label for="gecos">Gecos</label>
          <input type="text" id="gecos" name="gecos" value="' . $gecos . '">
        </p>
        <p>
          <label for="homedir">Homedir</label>
          <input type="text" id="homedir" name="homedir" value="' . $homedir . '">
        </p>
        <p>
          <label for="shell">Shell</label>
          <input type="text" id="shell" name="shell" value="' . $shell . '">
        </p>
        <p>
          <label for="password">Password</label>
          <input type="text" id="password" name="password" value="' . $password . '">
        </p>
        <p>
          <label for="lstchg">Lstchg</label>
          <input type="text" id="lstchg" name="lstchg" value="' . $lstchg . '">
        </p>
        <p>
          <label for="min">Min</label>
          <input type="text" id="min" name="min" value="' . $min . '">
        </p>
        <p>
          <label for="max">Max</label>
          <input type="text" id="max" name="max" value="' . $max . '">
        </p>
        <p>
          <label for="warn">Warn</label>
          <input type="text" id="warn" name="warn" value="' . $warn . '">
        </p>
        <p>
          <label for="inact">Inact</label>
          <input type="text" id="inact" name="inact" value="' . $inact . '">
        </p>
        <p>
          <label for="expire">Expire</label>
          <input type="text" id="expire" name="expire" value="' . $expire . '">
        </p>
        <p>
          <label for="flag">Flag</label>
          <input type="text" id="flag" name="flag" value="' . $flag . '">
        </p>
        <p>
          <label for="updated">Updated</label>
          <input type="text" id="updated" name="updated" value="' . $updated . '">
        </p>
        <p>
          <label for="created">Created</label>
          <input type="text" id="created" name="created" value="' . $created . '">
        </p>
        <p style="text-align:right">' . $this->button($submit, 'submit', 'primary') . '</p>
        <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
        <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
        <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
      </form>';
