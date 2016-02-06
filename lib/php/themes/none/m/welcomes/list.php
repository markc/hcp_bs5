<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/welcomes/list.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= m_welcomes_row($d);
return '
      <table>
        <tr><th>Domain</th><th>Subject</th><th></th></tr>' . $buf . '
      </table>';

    function m_welcomes_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=m_welcomes&m=read&i=' . $id . '">' . $domain . '</a></td>
          <td>' . $subject . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=m_welcomes&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=m_welcomes&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

