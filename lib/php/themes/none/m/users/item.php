<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users/item.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <hr>
      <table style="table-layout: fixed">
        <tr><td></td><td>ID:</td><td>' . $id . '</td><td></td></tr>
        <tr><td></td><td>UID:</td><td>' . $uid . '</td><td></td></tr>
        <tr><td></td><td>Crypt</td><td>' . $crypt . '</td><td></td></tr>
        <tr><td></td><td>Clear</td><td>' . $clear . '</td><td></td></tr>
        <tr><td></td><td>Name</td><td>' . $name . '</td><td></td></tr>
        <tr><td></td><td>Muid</td><td>' . $muid . '</td><td></td></tr>
        <tr><td></td><td>Mgid</td><td>' . $mgid . '</td><td></td></tr>
        <tr><td></td><td>Mquota</td><td>' . $mquota . '</td><td></td></tr>
        <tr><td></td><td>Mpath</td><td>' . $mpath . '</td><td></td></tr>
        <tr><td></td><td>Maildir</td><td>' . $maildir . '</td><td></td></tr>
        <tr><td></td><td>Delivery</td><td>' . $delivery . '</td><td></td></tr>
        <tr><td></td><td>Options</td><td>' . $options . '</td><td></td></tr>
        <tr><td></td><td>Acl</td><td>' . $acl . '</td><td></td></tr>
        <tr><td></td><td>Spam</td><td>' . $spam . '</td><td></td></tr>
        <tr><td></td><td>Updated</td><td>' . $updated . '</td><td></td></tr>
        <tr><td></td><td>Created</td><td>' . $created . '</td><td></td></tr>
        <tr><td></td>
          <td colspan="2" style="text-align:right">
            <p>'
            .$this->a('?o=m_users&m=delete&i=' . $id, 'Delete', 'danger', ' onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')"')
            .$this->a('?o=m_users&m=update&i=' . $id, 'Edit', 'primary').'
            </p>
          </td>
        </tr>
      </table>';
