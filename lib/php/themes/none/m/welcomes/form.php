<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/vacations/form.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

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
          <label for="deliver">Deliver</label>
          <input type="text" id="deliver" name="deliver" value="' . $deliver . '">
        </p>
        <p>
          <label for="use_default">Use Default</label>
          <input type="text" id="use_default" name="use_default" value="' . $use_default . '">
        </p>
        <p>
          <label for="process">Process</label>
          <input type="text" id="process" name="process" value="' . $process . '">
        </p>
        <p>
          <label for="from_addr">From Address</label>
          <input type="text" id="from_addr" name="from_addr" value="' . $from_addr . '">
        </p>
        <p>
          <label for="from_name">From Name</label>
          <input type="text" id="from_name" name="from_name" value="' . $from_name . '">
        </p>
        <p>
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" value="' . $subject . '">
        </p>
        <p>
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="7">' . $message . '</textarea>
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
