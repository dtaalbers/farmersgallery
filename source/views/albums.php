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
    
    $args = array(
        'posts_per_page'   => 30,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_type'        => 'album',
        'post_status'      => 'publish',
        'suppress_filters' => true 
    );  
    
    $myposts = get_posts($args);
?>
<?php get_header(); ?>
<div class="container"> 
    <div class="row">          
        <div id="albums">
            <?php foreach ($myposts as $post) : setup_postdata($post); ?>              
                <div class="col-md-4">
                    <div class="album">                    	 		
                        <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail'); ?>	                                         
                        <h3><?php the_title()?></h3>                
                        <img class="img-responsive" src="<?php echo (empty($image) ?  'http://placehold.it/1000x1000' : $image[0])?>">                        
                        <button data-id="<?php echo $post->ID ?>" data-bind="click: openAlbum" class="btn">Open album</button>
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
                url: "<?php echo FARMERSGALLERY_PLUGIN_URL; ?>classes/post.images.php",
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
                url: "<?php echo FARMERSGALLERY_PLUGIN_URL; ?>classes/post.images.php",
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
                        <img  class="thumb" data-bind="attr:{src: url}" />
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
        <div id="nextimage_tip">Click for next image</div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $defaultViewMode="full"; 
        $bg=$("#bg");
        $bgimg=$("#bg #bgimg");
        $preloader=$("#preloader");
        $toolbar=$("#toolbar");
        $nextimage_tip=$("#nextimage_tip");            
        $(window).load(function() {
            $toolbar.data("imageViewMode",$defaultViewMode); 
            ImageViewMode($toolbar.data("imageViewMode"));
            $customScrollBox=$("#customScrollBox");
            $customScrollBox_container=$("#customScrollBox .container");
            $customScrollBox_content=$("#customScrollBox .content");
            $dragger_container=$("#dragger_container");
            $dragger=$("#dragger");
            CustomScroller();
            function CustomScroller(){
                outerMargin=0;
                innerMargin=20;
                $customScrollBox.height($(window).height()-outerMargin);
                $dragger_container.height($(window).height()-innerMargin);
                visibleHeight=$(window).height()-outerMargin;
                if($customScrollBox_container.height()>visibleHeight){ 
                    $dragger_container,$dragger.css("display","block");
                    totalContent=$customScrollBox_content.height();
                    draggerContainerHeight=$(window).height()-innerMargin;
                    animSpeed=400; 
                    easeType="easeOutCirc"; 
                    bottomSpace=1.05;
                    targY=0;
                    draggerHeight=$dragger.height();
                    $dragger.draggable({ 
                        axis: "y", 
                        containment: "parent", 
                        drag: function(event, ui) {
                            Scroll();
                        }, 
                        stop: function(event, ui) {
                            DraggerOut();
                        }
                    });
                    $dragger_container.click(function(e) {
                        var mouseCoord=(e.pageY - $(this).offset().top);
                        var targetPos=mouseCoord+$dragger.height();
                        if(targetPos<draggerContainerHeight){
                            $dragger.css("top",mouseCoord);
                            Scroll();
                        } else {
                            $dragger.css("top",draggerContainerHeight-$dragger.height());
                            Scroll();
                        }
                    });
                    $(function($) {
                        $customScrollBox.bind("mousewheel", function(event, delta) {
                            vel = Math.abs(delta*10);
                            $dragger.css("top", $dragger.position().top-(delta*vel));
                            Scroll();
                            if($dragger.position().top<0){
                                $dragger.css("top", 0);
                                $customScrollBox_container.stop();
                                Scroll();
                            }
                            if($dragger.position().top>draggerContainerHeight-$dragger.height()){
                                $dragger.css("top", draggerContainerHeight-$dragger.height());
                                $customScrollBox_container.stop();
                                Scroll();
                            }
                            return false;
                        });
                    });
                    function Scroll(){
                        var scrollAmount=(totalContent-(visibleHeight/bottomSpace))/(draggerContainerHeight-draggerHeight);
                        var draggerY=$dragger.position().top;
                        targY=-draggerY*scrollAmount;
                        var thePos=$customScrollBox_container.position().top-targY;
                        $customScrollBox_container.stop().animate({top: "-="+thePos}, animSpeed, easeType); //with easing
                    }
                    $dragger.mouseup(function(){
                        DraggerOut();
                    }).mousedown(function(){
                        DraggerOver();
                    });
                    function DraggerOver(){
                        $dragger.css("background", "url(round_custom_scrollbar_bg_over.png)");
                    }
                    function DraggerOut(){
                        $dragger.css("background", "url(round_custom_scrollbar_bg.png)");
                    }
                } else { 
                    $dragger,$dragger_container.css("display","none");
                }
            }
            $(window).resize(function() {
                FullScreenBackground("#bgimg");
                $dragger.css("top",0); 
                $customScrollBox_container.css("top",0);
                $customScrollBox.unbind("mousewheel");
                CustomScroller();
            });
            LargeImageLoad($bgimg);
        });
        $bgimg.load(function() {
            LargeImageLoad($(this));
        });
        function LargeImageLoad($this){
            $preloader.fadeOut("fast");
            $this.removeAttr("width").removeAttr("height").css({ width: "", height: "" }); 
            $bg.data("originalImageWidth",$this.width()).data("originalImageHeight",$this.height());
            if($bg.data("newTitle")){
                $this.attr("title",$bg.data("newTitle"));
            }
            FullScreenBackground($this); 
            $bg.data("nextImage",$($("#outer_container").data("selectedThumb")).next().attr("href")); 
            if(typeof itemIndex!="undefined") {
                if(itemIndex==lastItemIndex){
                    $bg.data("lastImageReached","Y");
                    $bg.data("nextImage",$("#outer_container a.thumb_link").first().attr("href")); 
                } else {
                    $bg.data("lastImageReached","N");
                }
            } else {
                $bg.data("lastImageReached","N");
            }
            $this.fadeIn("slow");
            if($bg.data("nextImage") || $bg.data("lastImageReached")=="Y"){ 
                SlidePanels("close");
            }
            NextImageTip();
        }
        $("#outer_container").hover(
            function(){
                SlidePanels("open");
            },
            function(){ 
                SlidePanels("close");
            }
        ) 
        $bgimg.click(function(event){
            var $this=$(this);
            if($bg.data("nextImage")){ 
                $this.css("display","none");
                $preloader.fadeIn("fast"); 
                $($("#outer_container").data("selectedThumb")).children(".selected").css("display","none");
                if($bg.data("lastImageReached")!="Y"){
                    $($("#outer_container").data("selectedThumb")).next().children(".selected").css("display","block"); 
                } else {
                    $("#outer_container a.thumb_link").first().children(".selected").css("display","block"); 
                }
                var selThumb=$("#outer_container").data("selectedThumb");
                if($bg.data("lastImageReached")!="Y"){
                    $("#outer_container").data("selectedThumb",$(selThumb).next()); 
                } else {
                    $("#outer_container").data("selectedThumb",$("#outer_container a.thumb_link").first()); 
                }
                $bg.data("newTitle",$($("#outer_container").data("selectedThumb")).children("img").attr("title")); //get and store new image title attribute
        
                if($bg.data("lastImageReached")!="Y"){
                    itemIndex++;
                } else {
                    itemIndex=0;
                }
                $this.attr("src", "").attr("src", $bg.data("nextImage")); 
            }
        });
        function getIndex(theItem){
            for ( var i = 0, length = $("#outer_container a.thumb_link").length; i < length; i++ ) {
                if ( $("#outer_container a.thumb_link")[i] === theItem ) {
                    return i;
                }
            }
        }
        $toolbar.hover(
            function(){ 
                $(this).stop().fadeTo("fast",1);
            },
            function(){ 
                $(this).stop().fadeTo("fast",0.8);
            }
        ); 
        $toolbar.stop().fadeTo("fast",0.8); 
        $toolbar.click(function(event){
            if($toolbar.data("imageViewMode")=="full"){
                ImageViewMode("fit");
            } else if($toolbar.data("imageViewMode")=="fit") {
                ImageViewMode("original");
            } else if($toolbar.data("imageViewMode")=="original"){
                ImageViewMode("full");
            }
        });
        function NextImageTip(){
            if($bg.data("nextImage")){
                $nextimage_tip.stop().css("right",20).fadeIn("fast").fadeOut(2000,"easeInExpo",function(){$nextimage_tip.css("right",$(window).width());});
            }
        }
        function SlidePanels(action){
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
        function FullScreenBackground(theItem){
            var winWidth=$(window).width();
            var winHeight=$(window).height();
            var imageWidth=$(theItem).width();
            var imageHeight=$(theItem).height();
            if($toolbar.data("imageViewMode")!="original"){ 
                $(theItem).removeClass("with_border").removeClass("with_shadow"); 
                var picHeight = imageHeight / imageWidth;
                var picWidth = imageWidth / imageHeight;
                if($toolbar.data("imageViewMode")!="fit"){ 
                    if ((winHeight / winWidth) < picHeight) {
                        $(theItem).css("width",winWidth).css("height",picHeight*winWidth);
                    } else {
                        $(theItem).css("height",winHeight).css("width",picWidth*winHeight);
                    };
                } else {
                    if ((winHeight / winWidth) > picHeight) {
                        $(theItem).css("width",winWidth).css("height",picHeight*winWidth);
                    } else {
                        $(theItem).css("height",winHeight).css("width",picWidth*winHeight);
                    };
                }
                $(theItem).css("margin-left",((winWidth - $(theItem).width())/2)).css("margin-top",((winHeight - $(theItem).height())/2));
            } else { 
                $(theItem).addClass("with_border").addClass("with_shadow");
                $(theItem).css("width",$bg.data("originalImageWidth")).css("height",$bg.data("originalImageHeight"));
                $(theItem).css("margin-left",((winWidth-$(theItem).outerWidth())/2)).css("margin-top",((winHeight-$(theItem).outerHeight())/2));
            }
        }
        function ImageViewMode(theMode){
            $toolbar.data("imageViewMode", theMode); 
            FullScreenBackground($bgimg);
            if(theMode=="full"){
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> FULL");
            } else if(theMode=="fit") {
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> FIT");
            } else {
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> ORIGINAL");
            }
        }
        var images=["ajax-loader_dark.gif","round_custom_scrollbar_bg_over.png"];
        $.each(images, function(i) {
            images[i] = new Image();
            images[i].src = this;
        });
    });
</script>

<?php get_footer(); ?>