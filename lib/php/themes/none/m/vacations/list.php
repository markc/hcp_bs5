<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/vacations/list.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= m_vacations_row($d);
return '
      <table>
        <tr><th>UID</th><th>Spacing</th><th>Active</th><th>Message</th><th></th></tr>' . $buf . '
      </table>';

    function m_vacations_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=m_vacations&m=read&i=' . $id . '">' . $uid . '</a></td>
          <td>' . $spacing . '</td>
          <td>' . $active . '</td>
          <td>' . $message . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=m_vacations&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=m_vacations&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

