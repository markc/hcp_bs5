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
          <label for="uid">UID</label>
          <input type="text" id="uid" name="uid" value="' . $uid . '">
        </p>
        <p>
          <label for="crypt">Crypt</label>
          <input type="text" id="crypt" name="crypt" value="' . $crypt . '">
        </p>
        <p>
          <label for="clear">Clear</label>
          <input type="text" id="clear" name="clear" value="' . $clear . '">
        </p>
        <p>
          <label for="name">Name</label>
          <input type="text" id="name" name="name" value="' . $name . '">
        </p>
        <p>
          <label for="muid">Muid</label>
          <input type="text" id="muid" name="muid" value="' . $muid . '">
        </p>
        <p>
          <label for="mgid">Mgid</label>
          <input type="text" id="mgid" name="mgid" value="' . $mgid . '">
        </p>
        <p>
          <label for="mquota">Mquota</label>
          <input type="text" id="mquota" name="mquota" value="' . $mquota . '">
        </p>
        <p>
          <label for="mpath">Mpath</label>
          <input type="text" id="mpath" name="mpath" value="' . $mpath . '">
        </p>
        <p>
          <label for="maildir">Maildir</label>
          <input type="text" id="maildir" name="maildir" value="' . $maildir . '">
        </p>
        <p>
          <label for="delivery">Delivery</label>
          <input type="text" id="delivery" name="delivery" value="' . $delivery . '">
        </p>
        <p>
          <label for="options">Options</label>
          <input type="text" id="options" name="options" value="' . $options . '">
        </p>
        <p>
          <label for="acl">ACL</label>
          <input type="text" id="acl" name="acl" value="' . $acl . '">
        </p>
        <p>
          <label for="spam">Spam</label>
          <input type="text" id="spam" name="spam" value="' . $spam . '">
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
