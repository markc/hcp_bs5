<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w/news/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= w_news_row($d);
return '
      <table>' . $buf . '
      </table>';

    function w_news_row($ary) : string
    {
        extract($ary);
        return '
        <tr><td colspan="2"><hr></td></tr>
        <tr>
          <td><a href="?o=w_news&m=read&i=' . $id . '">' . $title . '</a></td>
          <td style="text-align:right">
            <small>
              by <b>' . $author . '</b> - <i>' . util::now($updated) . '</i> -
              <a href="?o=w_news&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=w_news&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>
        <tr>
          <td colspan="2"><p>' . nl2br($content) . '</p></td>
        </tr>';
    }

