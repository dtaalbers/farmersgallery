<?php
    /*
        Template Name: Albums
    */      
    wp_enqueue_style('main');
    wp_enqueue_style('grid');
    wp_enqueue_style('font-awsome');    
    wp_enqueue_script('jquery');
    wp_enqueue_script('knockout');
    wp_enqueue_script('jquery-easing');
    wp_enqueue_script('jquery-mousewheel');
    wp_enqueue_script('jquery-ui');
    wp_enqueue_script('custom');
    
    wp_localize_script('custom','farmer', array(
    	'full' => __( "Full", 'farmersgallery' ),
        'original' => __( "Original", 'farmersgallery' ),
        'fit' => __( "Fit", 'farmersgallery' ),
        'ViewMode' => __( "IMAGE VIEW MODE", 'farmersgallery' ),
    ));
    
    $args = array(
        'posts_per_page'   => 30,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_type'        => 'album',
        'post_status'      => 'publish',
        'suppress_filters' => true 
    );  
    
    $myposts = get_posts($args);

    get_header(); ?>
<div class="container"> 
    <div class="row">          
        <div id="albums">
            <?php foreach ($myposts as $post) : setup_postdata($post); ?>              
                <div class="col-md-4">
                    <div class="album">                    	 		
                        <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail'); ?>	                                         
                        <h3><?php the_title()?></h3>                
                        <img class="img-responsive" src="<?php echo (empty($image) ?  'http://placehold.it/1000x1000' : $image[0])?>">                        
                        <button data-id="<?php echo $post->ID ?>" data-bind="click: openAlbum" class="btn"><?php echo __('Open Album', 'farmersgallery')?></button>
                    </div>           
                </div> 
            <?php endforeach; ?>  
        </div>
    </div>  
</div> 


<script>
	var Image = function (url) {
	    this.url = ko.observable(url);
	}
    var ViewModel = function ($) {
        var self = this;
        self.images = ko.observableArray([]); 
        self.title = ko.observable("");
        self.description = ko.observable(""); 
                
        self.init = function() { }
        
        self.openAlbum = function(data, event) {           
            var id = $(event.currentTarget).data('id');
            var title = $(event.currentTarget).data('title');  
            self.fetchTitleAndDescription(id, function(data) {             
                $("#outer_container .title").html(data.title);
                $("#outer_container .description").html(data.description);                 
                self.fetchImages(id, function(){   
                    $('#bgimg').attr('src', self.images()[0].url());                 
                    $('body').css({ 'overflow': 'hidden'});
                    self.slidePanels('open');
                    $('#fullscreen').show();
                }); 
            });
        }
        
        self.fetchTitleAndDescription = function(postId, callback) {
            $.ajax({
                type: "POST",
                datatype : "json",
                url: "<?php echo FARMERSGALLERY_PLUGIN_URL; ?>classes/post-images.php",
                data: {
                    id: postId,
                    function: "title"
                },
                success: function(data){
                    callback(data);
                }
            });
        }
        
        self.fetchImages = function(postId, callback) {
            self.images.removeAll();
            $.ajax({
                type: "POST",
                datatype : "json",
                url: "<?php echo FARMERSGALLERY_PLUGIN_URL; ?>classes/post-images.php",
                data: {
                    id: postId,
                    function: "images"
                },
                success: function(urls){
                    urls.forEach(function(url) {
                        self.images.push(new Image(url)); 
                    });  
                    callback();
                }
            });
        }
        
        self.slidePanels = function(action) {
            var speed=900;
            var easing="easeInOutExpo";
            if(action=="open"){
                $("#arrow_indicator").fadeTo("fast",0);
                $("#outer_container").stop().animate({left: 0}, speed,easing);
                $bg.stop().animate({left: 585}, speed,easing);
            } else {
                $("#outer_container").stop().animate({left: -590}, speed,easing);
                $bg.stop().animate({left: 0}, speed,easing,function(){$("#arrow_indicator").fadeTo("fast",1);});
            }
        }
        
        self.open = function(data, event) {
            event.preventDefault();
            var $this= $(event.currentTarget);
            $("#bg #bgimg").css("display","none");
            $preloader.fadeIn("fast");
            $("#outer_container a.thumb_link").each(function() {
                $(this).children(".selected").css("display","none");
            });
            $this.children(".selected").css("display","block");
            $("#outer_container").data("selectedThumb",$this); 
            $("#bg").data("nextImage",$this.next().attr("href")); 	
            $("#bg").data("newTitle",$this.children("img").attr("title")); 
            itemIndex= self.getIndex($this[0]);
            lastItemIndex=($("#outer_container a.thumb_link").length)-1;
            $("#bg #bgimg").attr("src", "").attr("src", data.url());
        }
        
        self.close = function() {
            $('#fullscreen').hide();
            $('body').css({ 'overflow': 'visible'});
        }
        
        self.getIndex = function(theItem) {
            for ( var i = 0, length = $("#outer_container a.thumb_link").length; i < length; i++ ) {
                if ( $("#outer_container a.thumb_link")[i] === theItem ) {
                    return i;
                }
            }
        }
        
        self.init();
    };
    jQuery(document).ready(function($) {
        var itemIndex = 0;
        var lastItemIndex = 0;
        var vm = new ViewModel($);
        ko.applyBindings(vm);
	});
</script>

<div id="fullscreen">           
    <div id="outer_container">
        <div id="customScrollBox">
            <div class="container">
                <div class="content">
                    <h1 span class="title"></h1>
                    <i class="fa fa-close close-album" data-bind="click: close"></i>
                    <p class="description"></p>
                    <div id="toolbar"></div>
                    <div class="clear"></div> 
                    <!-- ko foreach: images --> 
                    <a data-bind="attr:{ href: url}, click: $root.open" class="thumb_link" >
                        <span class="selected"></span>
                        <div class="thumb" data-bind="style: { backgroundImage: 'url(\'' + url() + '\')' }"></div>
                    </a>
                    <!-- /ko -->
                    <p class="clear"></p>                   
                </div>
            </div>
            <div id="dragger_container">
                <div id="dragger"></div>
            </div>
        </div>
    </div>
    <div id="bg">
        <img id="bgimg"/>
        <div id="preloader"><img src="<?php echo FARMERSGALLERY_PLUGIN_URL; ?>images/ajax-loader_dark.gif" width="32" height="32" align="absmiddle" />LOADING...</div>
        <div id="arrow_indicator"><img src="<?php echo FARMERSGALLERY_PLUGIN_URL; ?>images/sw_arrow_indicator.png" width="50" height="50"  /></div>
        <div id="nextimage_tip"><?php echo __('Click for the next image', 'farmersgallery')?></div>
    </div>
</div>


<?php get_footer(); ?>