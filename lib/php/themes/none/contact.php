<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// contact.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <form action="" method="post" onsubmit="return mailform(this);">
        <p>
          <label for="subject">Your Subject</label>
          <input type="text" id="subject" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,40}$">
        </p>
        <p>
          <label for="message">Your Message</label>
          <textarea type="text" rows= "5" id="message" maxlength="1024" minlength="5"></textarea>
        </p>
        <p style="text-align:right">' . $this->button('Send', 'submit', 'primary') . '
        </p>
      </form>';
