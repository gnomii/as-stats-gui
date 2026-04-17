<?php include("func.inc.php"); ?>

<?php
if(!isset($peerusage)) $peerusage = 0;
if (isset($_GET['n'])) $ntop = (int)$_GET['n'];
if ($ntop > 200) $ntop = 200;
$hours = 24;

if (!empty($_GET['numhours'])) $hours = (int)$_GET['numhours'];
if ($peerusage) {
  $statsfile = $daypeerstatsfile;
} else {
	$statsfile = statsFileForHours($hours);
}

$label = statsLabelForHours($hours);
$knownlinks = getknownlinks();

$selected_links = array();

foreach($knownlinks as $link){
	if(isset($_GET["link_{$link['tag']}"]))
		$selected_links[] = $link['tag'];
}
$topas = getasstats_top($ntop, $statsfile, $selected_links);
$start = time() - $hours*3600;
$end = time();

if ($showv6) { $first_col = "1"; $second_col = "11"; $offset_second_col = "0";  } else { $first_col = "2"; $second_col = "9"; $offset_second_col = "1"; }

$i = 0;
$aff_astable = '<ul class="nav nav-stacked">';

foreach ($topas as $as => $nbytes) {
  $aff_astable .= renderASRow($as, $nbytes, $i, $start, $end, $peerusage, $selected_links, $label, $showv6, $customlinks);
  $i++;
}

$aff_astable .= '</ul>';

// LEGEND
if ( !isMobileDevice() && !isTabletDevice() ) {
  $aff_legend = buildLegend($knownlinks, $selected_links, "desktop", $brighten_negative);
} else {
  $aff_legend = "<table class='small'>";
  $aff_legend .= "<tr>";
  $aff_legend .= "<td style=\"border: 4px solid #fff;\">";
  $aff_legend .= "<table style=\"border-collapse: collapse; margin: 0; padding: 0\"><tr>";
  $aff_legend .= buildLegend($knownlinks, $selected_links, "mobile", $brighten_negative);
  $aff_legend .= "</tr></table>";
  $aff_legend .= "</td>";
  $aff_legend .= "</tr>";
  $aff_legend .= "</table>";
}

$page_title = "AS-Stats | Top " . $ntop . " AS" . ($peerusage ? " peer" : "") . " (" . $label . ")";
$meta_refresh = '<meta http-equiv="Refresh" content="300">';
$body_attrs = "";
include('templates/header.inc.php');
?>

  <!-- =============================================== -->
  <?php echo menu($selected_links); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header('Top ' . $ntop . ' AS', '('.$label.')'); ?>

    <section class="content">
      <div class="row">
        <div class="col-md-12 col-lg-<?php echo $first_col; ?>">
          <div class="row">

            <div class="col-lg-12">
              <?php
                if ( isMobileDevice() || isTabletDevice() ) {
              ?>

              <form method='get'>
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Legend</h3>
                  </div>
                  <div class="box-body">
                    <?php echo $aff_legend; ?>
                  </div>
                  <div class="box-footer">
                    <button type="submit" class="btn pull-right"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>

              <?php
                } else {
              ?>

              <div class="row affix col-md-12 col-lg-<?php echo $first_col; ?>">

                <form method='get'>
                  <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                  <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                  <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Legend</h3>
                    </div>
                    <div class="box-body">
                      <?php echo $aff_legend; ?>
                    </div>
                    <div class="box-footer">
                      <button type="submit" class="btn pull-right"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </form>

              </div>

              <?php } ?>
            </div>

          </div>
        </div>

        <div class="col-md-12 col-lg-<?php echo $second_col; ?> col-lg-offset-<?php echo $offset_second_col; ?>">

          <div class="row">
            <div class="col-lg-12">
              <div class="box box-primary">
                <div class="box-body">
                  <?php echo $aff_astable; ?>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

    </section>
  </div>

  <!-- =============================================== -->
  <?php echo footer(); ?>
  <!-- =============================================== -->

<?php
include('templates/footer.inc.php');
include('templates/footer_scripts.inc.php');
?>
