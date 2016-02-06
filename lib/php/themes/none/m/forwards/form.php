<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/forwards/form.php 20160206 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

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
          <label for="recipient">Recipient</label>
          <input type="text" id="recipient" name="recipient" value="' . $recipient . '">
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
