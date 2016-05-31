<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$bodyclasses = array();
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page">
<?php if ($hasheading || $hasnavbar) {} else { ?>
    <div id="page-header">
		<div id="page-header-wrapper" class="wrapper clearfix">
	        <?php if ($hasheading) { ?>
    		    <div class="schoolpic"></div><img src="<?php echo $OUTPUT->pix_url('header2', 'theme') ?>" class="headerimg"><div class="bass"></div><div class="computer"></div><!-- <div class="hockey"></div> --><div class="netball"></div><div class="faded"></div><br />
		        <div class="headermenu"><?php
		            echo $PAGE->headingmenu ?>
	            </div>
				<?php if ($hasnavbar) { ?>
				<div class="navbar">
					<div class="wrapper clearfix">
						<div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
						<div class="navbutton"><?php echo $PAGE->button; ?></div>
						<div class="loginmenu"><?php echo $OUTPUT->login_info(); ?></div>
					</div>
				</div>
				<?php } ?>
	        <?php } ?>
	    </div>
    </div>
<?php } ?>
<!-- END OF HEADER -->
	<div id="page-content-wrapper" class="wrapper clearfix">
		<div id="page-content">
			<div id="region-main-box">
				<div id="region-post-box">
					<div id="region-main-wrap">
						<div id="region-main">
							<div class="region-content">
								<?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
							</div>
						</div>
					</div>
					<?php if ($hassidepre) { ?>
					<div id="region-pre" class="block-region">
						<div class="region-content">
							<?php echo $OUTPUT->blocks_for_region('side-pre') ?>
						</div>
					</div>
					<?php } ?>
					<?php if ($hassidepost) { ?>
					<div id="region-post" class="block-region">
						<div class="region-content">
							<?php echo $OUTPUT->blocks_for_region('side-post') ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer">
		<?php
         echo $OUTPUT->standard_footer_html();
        ?>
        <div class="foothr"><img src="<?php echo $OUTPUT->pix_url('mhjcicon', 'theme') ?>" /><div class="line">&nbsp;</div></div>
        <p>Mission Heights Junior College, Auckland, New Zealand &bull; 103 Jeffs Road, Mission Heights, Manukau, Auckland 2016 &bull; +64 09 277 7881 &bull; <a href="mailto:admin@mhjc.school.nz">admin@mhjc.school.nz</a></p>
    </div>
    <?php } ?>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>