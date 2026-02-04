<?php
include_once('header.php');

$codes = query("getcatcodedetails",$config,$values,$sort);

require_once("headerHtml.inc.php");
?>
<h2>Category Codes</h2>
    <table class="datatable sortable" id="categorytable" summary="table of categories">
        <thead>
          <tr>
            <td>Code</td>
            <td>Category</td>
        </tr>
        </thead>
        <tbody><?php foreach ($codes as $code) {
                    if ($code['parentId'] == NULL) {
                      echo '<tr><td>&nbsp;</td><td></td></tr>';
                      echo '<tr><td></td>';
                    } else
                      echo '<tr><td>' . $code['sortBy'] . '</td>';
                    echo '<td>' . $code['title'] . '</td></tr>';
                  } ?>
        </tbody>
    </table>
<?php
include_once('footer.php');
?>
