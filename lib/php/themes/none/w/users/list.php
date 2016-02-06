<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w_users/list.php 20151030 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= w_users_row($d);
return '
      <table>
        <tr><th>UID</th><th>FirstName</th><th>LastName</th><th>Alt Email</th><th></th></tr>' . $buf . '
      </table>';

    function w_users_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=w_users&m=read&i=' . $id . '">' . $uid . '</a></td>
          <td>' . $fname . '</td>
          <td>' . $lname . '</td>
          <td>' . $altemail . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=w_users&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=w_users&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

