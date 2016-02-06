<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/limits/list.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= m_limits_row($d);
return '
      <table>
        <tr><th>Domain</th><th>Max</th><th>Size</th><th>Count</th><th>Fwd</th><th>Rcpt</th><th></th></tr>' . $buf . '
      </table>';

    function m_limits_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=m_limits&m=read&i=' . $id . '">' . $domain . '</a></td>
          <td>' . $maxaccounts . '</td>
          <td>' . $maxaccountsize . '</td>
          <td>' . $maxaccountcount . '</td>
          <td>' . $maxforwards . '</td>
          <td>' . $maxforwardsrcpt . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=m_limits&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=m_limits&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

