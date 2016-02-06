<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/welcomes/item.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <hr>
      <table style="table-layout: fixed">
        <tr><td></td><td>ID</td><td>' . $id . '</td><td></td></tr>
        <tr><td></td><td>Domain</td><td>' . $domain . '</td><td></td></tr>
        <tr><td></td><td>Deliver</td><td>' . $deliver . '</td><td></td></tr>
        <tr><td></td><td>Use Default</td><td>' . $use_default . '</td><td></td></tr>
        <tr><td></td><td>Process</td><td>' . $process . '</td><td></td></tr>
        <tr><td></td><td>From Addr</td><td>' . $from_addr . '</td><td></td></tr>
        <tr><td></td><td>From Name</td><td>' . $from_name . '</td><td></td></tr>
        <tr><td></td><td>Subject</td><td>' . $subject . '</td><td></td></tr>
        <tr><td></td><td>Message</td><td>' . $message . '</td><td></td></tr>
        <tr><td></td><td>Updated</td><td>' . $updated . '</td><td></td></tr>
        <tr><td></td><td>Created</td><td>' . $created . '</td><td></td></tr>
        <tr><td></td>
          <td colspan="2" style="text-align:right">
            <p>'
            .$this->a('?o=m_welcomes&m=delete&i=' . $id, 'Delete', 'danger', ' onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')"')
            .$this->a('?o=m_welcomes&m=update&i=' . $id, 'Edit', 'primary').'
            </p>
          </td>
        </tr>
      </table>';
