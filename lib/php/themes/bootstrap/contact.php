<?php
// lib/php/themes/bootstrap/contact.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Contact extends Themes_Bootstrap_Theme
{
    public function list(array $in) : string
    {
error_log(__METHOD__);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-envelope"></i> Contact us</h2>
          <form action="' . $this->g->self . '" method="post" onsubmit="return mailform(this)">
            <input type="hidden" name="o" value="auth">
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" class="form-control" id="subject" placeholder="Your Subject" required>
            </div>
            <div class="form-group">
              <label for="message">Message</label>
              <textarea class="form-control" id="message" rows="9" placeholder="Your Message" required></textarea>
            </div>
            <div class="form-group">
              <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" title="Please Note" data-content="Submitting this form will attempt to start your local mail program. If it does not work then you may have to configure your browser to recognize mailto: links."> <i class="fa fa-question-circle fa-fw"></i></a>
              <div class="btn-group pull-right">
                <button class="btn btn-primary" type="submit">Send</button>
              </div>
            </div>
          </form>
        </div>
        <script> $(function() { $("[data-toggle=popover]").popover(); }); </script>' . $in['js'];
    }
}

?>
