<?php
    /*
        Template Name: Albums
    */      
    wp_enqueue_style('main');
    wp_enqueue_style('grid');
    
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
                        <!--<button onclick="window.location.href='<?php echo get_site_url().'/detail?id='.get_the_ID();?>'" class="btn">Open album</button>-->
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
                
        self.init = function() { }
        
        self.openAlbum = function(data, event) {           
            var id = $(event.currentTarget).data('id'); 
            self.fetchImages(id, function(){   
                $('#bgimg').attr('src', self.images()[0].url());
                $('#fullscreen').show();
            }); 
        }
        
        self.fetchImages = function(postId, callback) { 
            $.ajax({
                type: "POST",
                datatype : "json",
                url: "<?php echo FARMERSGALLERY_PLUGIN_URL; ?>classes/post.images.php",
                data: {
                    id: postId 
                },
                success: function(urls){
                    urls.forEach(function(url) {
                        self.images.push(new Image(url)); 
                    });  
                    callback();
                }
            });
        }
        
        self.open = function(data, event) {
            event.preventDefault();
            var $this= $(event.currentTarget);
            $("#bg #bgimg").css("display","none");
            $preloader.fadeIn("fast"); //show preloader
            //style clicked thumbnail
            $("#outer_container a.thumb_link").each(function() {
                $(this).children(".selected").css("display","none");
            });
            $this.children(".selected").css("display","block");
            //get and store next image and selected thumb 
            $("#outer_container").data("selectedThumb",$this); 
            console.log('href',$this.next().attr("href"));
            $("#bg").data("nextImage",$this.next().attr("href")); 	
            $("#bg").data("newTitle",$this.children("img").attr("title")); //get and store new image title attribute
            itemIndex= self.getIndex($this[0]); //get clicked item index
            console.log(itemIndex);
            lastItemIndex=($("#outer_container a.thumb_link").length)-1; //get last item index
            $("#bg #bgimg").attr("src", "").attr("src", data.url()); //switch image
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
                    <h1>SIDE<span class="lightgrey">WAYS</span> <br /><span class="light"><span class="grey"><span class="s36">JQUERY FULLSCREEN IMAGE GALLERY</span></span></span></h1>
                    <p>A simple, yet elegant fullscreen image gallery created with the jQuery framework and some simple CSS. <a href="http://manos.malihu.gr/sideways-jquery-fullscreen-image-gallery" target="_blank">Full post and download files.</a></p>
                    <div id="toolbar"></div>
                    <div class="clear"></div> 
                    <!-- ko foreach: images --> 
                    <a data-bind="attr:{ href: url}, click: $root.open" class="thumb_link" >
                        <span class="selected"></span>
                        <img  class="thumb" data-bind="attr:{src: url}" />
                    </a>
                    <!-- /ko -->
                    <p class="clear"></p>
                    <p>Created by <a href="http://manos.malihu.gr" target="_blank">malihu</a> and his cats on a hot summer day.</p>
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
            //set default view mode
            $defaultViewMode="full"; //full (fullscreen background), fit (fit to window), original (no scale)
            //cache vars
            $bg=$("#bg");
            $bgimg=$("#bg #bgimg");
            $preloader=$("#preloader");
            $toolbar=$("#toolbar");
            $nextimage_tip=$("#nextimage_tip");
            
        $(window).load(function() {
            $toolbar.data("imageViewMode",$defaultViewMode); //default view mode
            ImageViewMode($toolbar.data("imageViewMode"));
            //cache vars
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
                if($customScrollBox_container.height()>visibleHeight){ //custom scroll depends on content height
                    $dragger_container,$dragger.css("display","block");
                    totalContent=$customScrollBox_content.height();
                    draggerContainerHeight=$(window).height()-innerMargin;
                    animSpeed=400; //animation speed
                    easeType="easeOutCirc"; //easing type
                    bottomSpace=1.05; //bottom scrolling space
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

                    //scrollbar click
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

                    //mousewheel
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

                    //dragger hover
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
                } else { //hide custom scrollbar if content is short
                    $dragger,$dragger_container.css("display","none");
                }
            }

            //resize browser window functions
            $(window).resize(function() {
                FullScreenBackground("#bgimg"); //scale bg image
                $dragger.css("top",0); //reset content scroll
                $customScrollBox_container.css("top",0);
                $customScrollBox.unbind("mousewheel");
                CustomScroller();
            });
            
            LargeImageLoad($bgimg);
        });
            
            //loading bg image
            $bgimg.load(function() {
                LargeImageLoad($(this));
            });
            
            function LargeImageLoad($this){
                $preloader.fadeOut("fast"); //hide preloader
                $this.removeAttr("width").removeAttr("height").css({ width: "", height: "" }); //lose all previous dimensions in order to rescale new image data
                $bg.data("originalImageWidth",$this.width()).data("originalImageHeight",$this.height());
                if($bg.data("newTitle")){
                    $this.attr("title",$bg.data("newTitle")); //set new image title attribute
                }
                FullScreenBackground($this); //scale new image
                $bg.data("nextImage",$($("#outer_container").data("selectedThumb")).next().attr("href")); //get and store next image
                if(typeof itemIndex!="undefined") {
                    if(itemIndex==lastItemIndex){ //check if it is the last image
                        $bg.data("lastImageReached","Y");
                        $bg.data("nextImage",$("#outer_container a.thumb_link").first().attr("href")); //get and store next image
                    } else {
                        $bg.data("lastImageReached","N");
                    }
                } else {
                    $bg.data("lastImageReached","N");
                }
                $this.fadeIn("slow"); //fadein background image
                if($bg.data("nextImage") || $bg.data("lastImageReached")=="Y"){ //don't close thumbs pane on 1st load
                    SlidePanels("close"); //close the left pane
                }
                NextImageTip();
            }

            //slide in/out left pane
            $("#outer_container").hover(
                function(){ //mouse over
                    SlidePanels("open");
                },
                function(){ //mouse out
                    SlidePanels("close");
                }
            )
            
            // //Clicking on thumbnail changes the background image
            // $outer_container_a.click(function(event){
            //     event.preventDefault();
            //     var $this=this;
            //     $bgimg.css("display","none");
            //     $preloader.fadeIn("fast"); //show preloader
            //     //style clicked thumbnail
            //     $outer_container_a.each(function() {
            //         $(this).children(".selected").css("display","none");
            //     });
            //     $(this).children(".selected").css("display","block");
            //     //get and store next image and selected thumb 
            //     $outer_container.data("selectedThumb",$this); 
            //     $bg.data("nextImage",$(this).next().attr("href")); 	
            //     $bg.data("newTitle",$(this).children("img").attr("title")); //get and store new image title attribute
            //     itemIndex=getIndex($this); //get clicked item index
            //     lastItemIndex=($outer_container_a.length)-1; //get last item index
            //     $bgimg.attr("src", "").attr("src", $this); //switch image
            // }); 

            //clicking on large image loads the next one
            $bgimg.click(function(event){
                var $this=$(this);
                if($bg.data("nextImage")){ //if next image data is stored
                    $this.css("display","none");
                    $preloader.fadeIn("fast"); //show preloader
                    $($("#outer_container").data("selectedThumb")).children(".selected").css("display","none"); //deselect thumb
                    if($bg.data("lastImageReached")!="Y"){
                        $($("#outer_container").data("selectedThumb")).next().children(".selected").css("display","block"); //select new thumb
                    } else {
                        $("#outer_container a.thumb_link").first().children(".selected").css("display","block"); //select new thumb - first
                    }
                    //store new selected thumb
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
                    $this.attr("src", "").attr("src", $bg.data("nextImage")); //switch image
                }
            });
            
            //function to get element index (fuck you IE!)
            function getIndex(theItem){
                for ( var i = 0, length = $("#outer_container a.thumb_link").length; i < length; i++ ) {
                    if ( $("#outer_container a.thumb_link")[i] === theItem ) {
                        return i;
                    }
                }
            }
            
            //toolbar (image view mode button) hover
            $toolbar.hover(
                function(){ //mouse over
                    $(this).stop().fadeTo("fast",1);
                },
                function(){ //mouse out
                    $(this).stop().fadeTo("fast",0.8);
                }
            ); 
            $toolbar.stop().fadeTo("fast",0.8); //set its original state
            
            //Clicking on toolbar changes the image view mode
            $toolbar.click(function(event){
                if($toolbar.data("imageViewMode")=="full"){
                    ImageViewMode("fit");
                } else if($toolbar.data("imageViewMode")=="fit") {
                    ImageViewMode("original");
                } else if($toolbar.data("imageViewMode")=="original"){
                    ImageViewMode("full");
                }
            });

            //next image balloon tip
            function NextImageTip(){
                if($bg.data("nextImage")){ //check if this is the first image
                    $nextimage_tip.stop().css("right",20).fadeIn("fast").fadeOut(2000,"easeInExpo",function(){$nextimage_tip.css("right",$(window).width());});
                }
            }

            //slide in/out left pane function
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

        //Image scale function
        function FullScreenBackground(theItem){
            var winWidth=$(window).width();
            var winHeight=$(window).height();
            var imageWidth=$(theItem).width();
            var imageHeight=$(theItem).height();
            if($toolbar.data("imageViewMode")!="original"){ //scale
                $(theItem).removeClass("with_border").removeClass("with_shadow"); //remove extra styles of orininal view mode
                var picHeight = imageHeight / imageWidth;
                var picWidth = imageWidth / imageHeight;
                if($toolbar.data("imageViewMode")!="fit"){ //image view mode: full
                    if ((winHeight / winWidth) < picHeight) {
                        $(theItem).css("width",winWidth).css("height",picHeight*winWidth);
                    } else {
                        $(theItem).css("height",winHeight).css("width",picWidth*winHeight);
                    };
                } else { //image view mode: fit
                    if ((winHeight / winWidth) > picHeight) {
                        $(theItem).css("width",winWidth).css("height",picHeight*winWidth);
                    } else {
                        $(theItem).css("height",winHeight).css("width",picWidth*winHeight);
                    };
                }
                //center it
                $(theItem).css("margin-left",((winWidth - $(theItem).width())/2)).css("margin-top",((winHeight - $(theItem).height())/2));
            } else { //no scale
                //add extra styles for orininal view mode
                $(theItem).addClass("with_border").addClass("with_shadow");
                //set original dimensions
                $(theItem).css("width",$bg.data("originalImageWidth")).css("height",$bg.data("originalImageHeight"));
                //center it
                $(theItem).css("margin-left",((winWidth-$(theItem).outerWidth())/2)).css("margin-top",((winHeight-$(theItem).outerHeight())/2));
            }
        }

        //image view mode function - full or fit
        function ImageViewMode(theMode){
            $toolbar.data("imageViewMode", theMode); //store new mode
            FullScreenBackground($bgimg); //scale bg image
            //re-style button
            if(theMode=="full"){
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> FULL");
            } else if(theMode=="fit") {
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> FIT");
            } else {
                $toolbar.html("<span class='lightgrey'>IMAGE VIEW MODE &rsaquo;</span> ORIGINAL");
            }
        }

        //preload script images
        var images=["ajax-loader_dark.gif","round_custom_scrollbar_bg_over.png"];
        $.each(images, function(i) {
            images[i] = new Image();
            images[i].src = this;
        });
    });
</script>

<?php get_footer(); ?>