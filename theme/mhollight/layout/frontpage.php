<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$bodyclasses = array();
$bodyclasses[] = 'content-only';

echo $OUTPUT->doctype()
?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
    <head>
        <title><?php echo $PAGE->title ?></title>
        <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme') ?>" />
        <meta name="description" content="<?php echo strip_tags(format_text($SITE->summary, FORMAT_HTML)) ?>" />
        <?php echo $OUTPUT->standard_head_html() ?>
        <script type="text/javascript">
            jQuery.fn.extend({
                captify: function (uo) {
                    var o = $.extend({
                        speedOver: 'fast', // speed of the mouseover effect
                        speedOut: 'normal', // speed of the mouseout effect
                        hideDelay: 500, // how long to delay the hiding of the caption after mouseout (ms)
                        animation: 'slide', // 'fade', 'slide', 'always-on'
                        prefix: '', // text/html to be placed at the beginning of every caption
                        opacity: '0.45', // opacity of the caption on mouse over
                        className: 'caption-bottom', // the name of the CSS class to apply to the caption box         
                        position: 'bottom', // position of the caption (top or bottom)         
                        spanWidth: '100%'				// caption span % of the image
                    }, uo);
                    $(this).each(function () {
                        var img = this;
                        $(this).load(function () {
                            if (img.hasInit) {
                                return false;
                            }
                            img.hasInit = true;
                            var over_caption = false;
                            var over_img = false;

                            //pull the label from another element if there is a 
                            //valid element id inside the rel="..." attribute, otherwise,
                            //just use the text in the alt="..." attribute.
                            var captionLabelSrc = $('#' + $(this).attr('rel'));
                            var captionLabelHTML = !captionLabelSrc.length ? $(this).attr('alt') : captionLabelSrc.html();
                            captionLabelSrc.remove();
                            var toWrap = this.parent && this.parent.tagName == 'a' ? this.parent : $(this);

                            var wrapper =
                                    toWrap.wrap('<div></div>').parent()
                                    .css({
                                        overflow: 'hidden',
                                        padding: 0,
                                        fontSize: 0.1
                                    })
                                    .addClass('caption-wrapper')
                                    .width($(this).width())
                                    .height($(this).height());

                            //transfer the margin and border properties from the image to the wrapper
                            $.map(['top', 'right', 'bottom', 'left'], function (i) {
                                wrapper.css('margin-' + i, $(img).css('margin-' + i));
                                $.map(['style', 'width', 'color'], function (j) {
                                    var key = 'border-' + i + '-' + j;
                                    wrapper.css(key, $(img).css(key));
                                });
                            });
                            $(img).css({border: '0 none'});

                            //create two consecutive divs, one for the semi-transparent background,
                            //and other other for the fully-opaque label
                            var caption = $('div:last', wrapper.append('<div></div>'))
                                    .addClass(o.className);

                            var captionContent = $('div:last', wrapper.append('<div></div>'))
                                    .addClass(o.className)
                                    .append(o.prefix)
                                    .append(captionLabelHTML);

                            //override hiding from CSS, and reset all margins (which could have been inherited)
                            $('*', wrapper).css({margin: 0}).show();

                            //ensure the background is on bottom
                            var captionPositioning = jQuery.browser.msie ? 'static' : 'relative';
                            caption.css({
                                zIndex: 1,
                                position: captionPositioning,
                                opacity: o.animation == 'fade' ? 0 : o.opacity,
                                width: o.spanWidth
                            });

                            if (o.position == 'bottom') {
                                var vLabelOffset =
                                        parseInt(caption.css('border-top-width').replace('px', '')) +
                                        parseInt(captionContent.css('padding-top').replace('px', '')) - 1;
                                captionContent.css('paddingTop', vLabelOffset);
                            }
                            //clear the backgrounds/borders from the label, and make it fully-opaque
                            captionContent.css({
                                position: captionPositioning,
                                zIndex: 2,
                                background: 'none',
                                border: '0 none',
                                opacity: o.animation == 'fade' ? 0 : 1,
                                width: o.spanWidth
                            });
                            caption.width(captionContent.outerWidth());
                            caption.height(captionContent.height());

                            // represents caption margin positioning for hide and show states
                            var topBorderAdj = o.position == 'bottom' && jQuery.browser.msie ? -4 : 0;
                            var captionPosition = o.position == 'top'
                                    ? {hide: -$(img).height() - caption.outerHeight() - 1, show: -$(img).height()}
                            : {hide: 0, show: -caption.outerHeight() + topBorderAdj};

                            //pull the label up on top of the background
                            captionContent.css('marginTop', -caption.outerHeight());
                            caption.css('marginTop', captionPosition[o.animation == 'fade' || o.animation == 'always-on' ? 'show' : 'hide']);

                            //function to push the caption out of view
                            var cHide = function () {
                                if (!over_caption && !over_img) {
                                    var props = o.animation == 'fade'
                                            ? {opacity: 0}
                                    : {marginTop: captionPosition.hide};
                                    caption.animate(props, o.speedOut);
                                    if (o.animation == 'fade') {
                                        captionContent.animate({opacity: 0}, o.speedOver);
                                    }
                                }
                            };

                            if (o.animation != 'always-on') {
                                //when the mouse is over the image
                                $(this).hover(
                                        function () {
                                            over_img = true;
                                            if (!over_caption) {
                                                var props = o.animation == 'fade'
                                                        ? {opacity: o.opacity}
                                                : {marginTop: captionPosition.show};
                                                caption.animate(props, o.speedOver);
                                                if (o.animation == 'fade') {
                                                    captionContent.animate({opacity: 1}, o.speedOver / 2);
                                                }
                                            }
                                        },
                                        function () {
                                            over_img = false;
                                            window.setTimeout(cHide, o.hideDelay);
                                        }
                                );
                                //when the mouse is over the caption on top of the image (the caption is a sibling of the image)
                                $('div', wrapper).hover(
                                        function () {
                                            over_caption = true;
                                        },
                                        function () {
                                            over_caption = false;
                                            window.setTimeout(cHide, o.hideDelay);
                                        }
                                );
                            }
                        });
                        //if the image has already loaded (due to being cached), force the load function to be called
                        if (this.complete || this.naturalWidth > 0) {
                            $(img).trigger('load');
                        }
                    });
                }
            });
            $(function () {
                $('img.captify').captify({
                    speedOver: 'fast',
                    speedOut: 'normal',
                    hideDelay: 500,
                    animation: 'slide',
                    // text/html to be placed at the beginning of every caption
                    prefix: '',
                    // opacity of the caption on mouse over
                    opacity: '0.6',
                    // the name of the CSS class to apply to the caption box
                    className: 'caption-top',
                    // position of the caption (top or bottom)
                    position: 'top',
                    // caption span % of the image
                    spanWidth: '100%'
                });
            });
        </script>
    </head>
    <body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses . ' ' . join(' ', $bodyclasses) ?>">
        <?php echo $OUTPUT->standard_top_of_body_html() ?>
        <div id="page">
            <!-- START OF HEADER -->
            <div id="page-header">
                <div id="page-header-wrapper" class="wrapper clearfix">
                    <div class="schoolpic"></div><img src="<?php echo $OUTPUT->pix_url('header2', 'theme') ?>" class="headerimg"><div class="bass"></div><div class="computer"></div><div class="hockey"></div><div class="netball"></div><div class="faded"></div><br />
                    <div class="headermenu"><?php echo $PAGE->headingmenu; ?></div>
                    <div class="navbar">
                        <div class="wrapper clearfix">
                            <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                            <div class="navbutton"><?php echo $PAGE->button; ?></div>
                            <div class="loginmenu"><?php echo $OUTPUT->login_info(); ?></div>
                        </div>
                    </div>
                    <div class="block-region">
                        <?php
                        if ($hassidepre) {
                            echo $OUTPUT->blocks('side-pre');
                        }
                        if ($hassidepost) {
                            echo $OUTPUT->blocks('side-post');
                        }
                        ?>
                    </div>
                </div>
            </div>
            <!-- END OF HEADER -->
            <!-- START OF CONTENT -->
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
                    </div>
                </div>
            </div>
            <!-- END OF CONTENT -->
            <!-- START OF FOOTER -->
            <div id="page-footer" class="wrapper">
                <?php
                echo $OUTPUT->standard_footer_html();
                ?>
                <div class="foothr"><img src="<?php echo $OUTPUT->pix_url('mhjcicon', 'theme') ?>" /><div class="line">&nbsp;</div></div>
                <p>Mission Heights Junior College, Auckland, New Zealand &bull; 103 Jeffs Road, Mission Heights, Manukau, Auckland 2016 &bull; +64 09 277 7881 &bull; <a href="mailto:admin@mhjc.school.nz">admin@mhjc.school.nz</a></p>
            </div>
            <!-- END OF FOOTER -->
        </div>
        <?php echo $OUTPUT->standard_end_of_body_html() ?>
    </body>
</html>