<?php include("func.inc.php"); ?>

<?php
$aff_astable = $select_topinterval = "";

if(!isset($peerusage)) $peerusage = 0;
if (isset($_GET['n'])) $ntop = (int)$_GET['n'];
if ($ntop > 200) $ntop = 200;
$hours = 24;

if (isset($_GET['ix'])) { $ix_id = (int)$_GET['ix']; } else { $ix_id = ""; }
if (isset($_GET['name_ix'])) { $name_ix = $ix_name = $_GET['name_ix']; } else { $name_ix = $ix_name =""; }

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

if ($showv6) { $first_col = "1"; $second_col = "11"; $offset_second_col = "0";  } else { $first_col = "2"; $second_col = "9"; $offset_second_col = "1"; }

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

$peerdb = new PeeringDB();

if ( $my_asn ) {
  // SELECT IX FROM PEERINGDB
  $list_ix = $peerdb->GetIX($my_asn);
  $select_ix = '<select name="ix" id="ix" class="form-control" onchange="this.form.submit()">';
  $select_ix .= '<option value="">Select IX</option>';
  foreach ($list_ix as $key => $value) {
    if ( isset($ix_id) ) {
      if ( $value->ix_id == $ix_id ) {
        $selected = "selected";
  			$ix_name = $value->name . " - ";
      } else {
        $selected = "";
      }
    } else { $selected = ""; }

    $select_ix .= '<option '.$selected.' value="'.$value->ix_id.'">' . htmlspecialchars($value->name, ENT_QUOTES, 'UTF-8') . '</option>';
  }
  $select_ix .= '</select>';
}

if ( $ix_id ) {
	$list_asn = $peerdb->GetIXASN($ix_id);
	$topas = getasstats_top($ntop, $statsfile, $selected_links, $list_asn);
	$start = time() - $hours*3600;
	$end = time();

	$i = 0;
	$aff_astable = '<ul class="nav nav-stacked">';

	foreach ($topas as $as => $nbytes) {
	  $aff_astable .= renderASRow($as, $nbytes, $i, $start, $end, $peerusage, $selected_links, $label, $showv6, $customlinks);
	  $i++;
	}

	$aff_astable .= '</ul>';

	// TOP INTERVAL SELECT
  if ( count($top_intervals) > 1 ) {
  	$select_topinterval = '<select name="numhours" id="numhours" class="form-control" onchange="this.form.submit()">';
  	foreach ($top_intervals as $interval) {
  		$selected = isset($_GET['numhours']) && $_GET['numhours'] == $interval['hours'] ? "selected" : "";
  		$select_topinterval .= '<option '.$selected.' value="'.$interval['hours'].'">'.$interval['label'].'</option>';
  	}
  	$select_topinterval .= '</select>';
  }
}
$page_title = "AS-Stats | Top IX";
$meta_refresh = '<meta http-equiv="Refresh" content="300">';
$body_attrs = "";
include('templates/header.inc.php');
?>

  <!-- =============================================== -->
  <?php echo menu($selected_links); ?>
  <!-- =============================================== -->

  <div class="content-wrapper">
    <?php echo content_header(htmlentities($ix_name) . ' Top ' . $ntop . ' AS', '('.$label.')'); ?>

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
								<input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
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
									<input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
									<input type='hidden' name='name_ix' value='<?php echo htmlspecialchars($name_ix); ?>'/>
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
            <?php if ( $my_asn ) { ?>
						<div class="col-md-12 col-lg-4">
							<form method='get'>
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">My IX</h3>
                  </div>
                  <div class="box-body">
                  	<?php echo $select_ix; ?>
                  </div>
                </div>
              </form>
						</div>
            <?php } ?>
						<div class="col-md-12 col-lg-4">
							<form method='get' id="search_ix_name">
                <input type='hidden' name='numhours' value='<?php echo $hours; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Search IX</h3>
                  </div>
                  <div class="box-body">
                    <input type="text" class="form-control" name="name_ix" placeholder="Search IX" id="peeringdb" data-provide="typeahead" autocomplete="off" value="<?php echo htmlspecialchars($name_ix); ?>">
                    <input type='hidden' id='ix' name='ix'/>
                    <div id="message"></div>
                  </div>
                </div>
              </form>
						</div>
						<?php if ( $aff_astable ) { ?>
            <?php if ( $select_topinterval ) { ?>
						<div class="col-md-12 col-lg-4">
							<form method='get'>
                <input type='hidden' name='ix' value='<?php echo $ix_id; ?>'/>
                <input type='hidden' name='n' value='<?php echo $ntop; ?>'/>
								<input type='hidden' name='name_ix' value='<?php echo htmlspecialchars($name_ix); ?>'/>
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Interval</h3>
                  </div>
                  <div class="box-body">
                  	<?php echo $select_topinterval; ?>
                  </div>
                </div>
              </form>
						</div>
            <?php } ?>
						<div class="col-md-12">
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
						<?php } ?>
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
?>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="plugins/fastclick/fastclick.min.js"></script>
<script src="dist/js/app.min.js"></script>
<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
<script src="plugins/typeahead/bootstrap3-typeahead.min.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $('#peeringdb').typeahead({
      source: function (query, process) {
        $.ajax({
          url: 'lib/json/get_ixname.php',
          dataType: 'JSON',
          minLength: 2,
          data: 'name=' + query,
          success: function(data) {
						if ( data !== null ) {
            	process(data);
						} else {
							$("#message").html('<small class="form-text text-muted">No IX found.</small>');
						}
          },
          beforeSend: function () {
             $("#peeringdb").addClass("searchBox");
          },
          complete: function () {
             $("#peeringdb").removeClass("searchBox");
          },
        });
      },
      updater : function (item) {
				$("form input[name=ix]").val(item.id);
				//$("#ix").val("");
				this.$element[0].value = item.name;
      	this.$element[0].form.submit();
        return item.name;
      },
      autoselect: true,
    });
  });
</script>

</body>
</html>
