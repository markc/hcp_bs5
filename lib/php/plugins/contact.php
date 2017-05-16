<?php
// lib/php/plugins/contact.php 20150101 - 20170317
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Contact extends Plugin
{
    public function list() : string
    {
error_log(__METHOD__);

        $buf = '
      <h2>Email Contact Form</h2>
      <form id="contact-send" method="post" onsubmit="return mailform(this);">
        <p><input id="subject" required="" type="text" placeholder="Message Subject"></p>
        <p><textarea id="message" rows="9" required="" placeholder="Message Content"></textarea></p>
        <p class="text-right">
          <small>(Note: Doesn\'t seem to work with Firefox 50.1)</small>
          <input class="btn" type="submit" id="send" value="Send">
        </p>
      </form>';

        $js = '
      <script>
function mailform(form) {
    location.href = "mailto:' . $this->g->email . '"
        + "?subject=" + encodeURIComponent(form.subject.value)
        + "&body=" + encodeURIComponent(form.message.value);
    form.subject.value = "";
    form.message.value = "";
    alert("Thank you for your message. We will get back to you as soon as possible.");
    return false;
}
      </script>';
        return $this->t->list(['buf' => $buf, 'js' => $js]);
    }
}

?>
