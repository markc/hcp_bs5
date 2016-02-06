<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w_users/item.php 20151030 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <hr>
      <table style="table-layout: fixed">
        <tr><td></td><td>ID:</td><td>' . $id . '</td><td></td></tr>
        <tr><td></td><td>UID:</td><td>' . $uid . '</td><td></td></tr>
        <tr><td></td><td>FirstName:</td><td>' . $fname . '</td><td></td></tr>
        <tr><td></td><td>LastName:</td><td>' . $lname . '</td><td></td></tr>
        <tr><td></td><td>Email:</td><td>' . $altemail . '</td><td></td></tr>
        <tr><td></td><td>Created:</td><td>' . $created . '</td><td></td></tr>
        <tr><td></td><td>Updated:</td><td>' . $updated . '</td><td></td></tr>
        <tr><td></td><td colspan="2"><p><em>' . nl2br($anote) . '</em></p></td><td></td></tr>
        <tr><td></td>
          <td colspan="2" style="text-align:right">
            <p>'
            .$this->a('?o=w_users&m=delete&i=' . $id, 'Delete', 'danger', ' onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')"')
            .$this->a('?o=w_users&m=update&i=' . $id, 'Edit', 'primary').'
            </p>
          </td>
        </tr>
      </table>';
