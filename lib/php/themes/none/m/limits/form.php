<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/limits/form.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <form method="post">
        <p>
          <label for="id">ID</label> <span><strong>' . $id . '</strong></span>
        </p>
        <p>
          <label for="domain">Domain</label>
          <input type="text" id="domain" name="domain" value="' . $domain . '">
        </p>
        <p>
          <label for="maxaccounts">maxaccounts</label>
          <input type="text" id="Maxaccounts" name="maxaccounts" value="' . $maxaccounts . '">
        </p>
        <p>
          <label for="maxaccountsize">Maxaccountsize</label>
          <input type="text" id="maxaccountsize" name="maxaccountsize" value="' . $maxaccountsize . '">
        </p>
        <p>
          <label for="maxaccountcount">Maxaccountcount</label>
          <input type="text" id="maxaccountcount" name="maxaccountcount" value="' . $maxaccountcount . '">
        </p>
        <p>
          <label for="maxforwards">Maxforwards</label>
          <input type="text" id="maxforwards" name="maxforwards" value="' . $maxforwards . '">
        </p>
        <p>
          <label for="maxforwardsrcpt">Maxforwardsrcpt</label>
          <input type="text" id="maxforwardsrcpt" name="maxforwardsrcpt" value="' . $maxforwardsrcpt . '">
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
