<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// home.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <h2>Simple PHP7 Examples</h2>
      <p>
        <strong>
An ultra simple single-file PHP7 framework and template system example.
        </strong>
      </p>
      <p>
Comments and pull requests are most welcome via the Issue Tracker link above.
      </p>
      <p>' . ($t->a('https://github.com/markc/simple-php7-examples', 'Project Page', 'primary'))
           . ($t->a('https://github.com/markc/simple-php7-examples/issues', 'Issue Tracker', 'primary')) . '
      </p>';
