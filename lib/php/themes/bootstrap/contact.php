<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// bootstrap/contact.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2><i class="fa fa-envelope-o fa-fw"></i> Contact</h2>
      <form class="form-horizontal" role="form" method="post" onsubmit="return mailform(this);">
        <div class="form-group">
          <label for="subject" class="col-md-2 control-label">Subject</label>
          <div class="col-md-8">
            <input type="text" class="form-control" id="subject" placeholder="Your Subject" required>
          </div>
        </div>
        <div class="form-group">
          <label for="message" class="col-md-2 control-label">Message</label>
          <div class="col-md-8">
            <textarea class="form-control" id="message" rows="9" placeholder="Your Message" required></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-10">
            <input class="btn btn-primary pull-right" id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
          </div>
        </div>
      </form>';
