<?php
/*
    Template Name: Detail
*/
wp_enqueue_style('main');
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-easing');
wp_enqueue_script('jquery-mousewheel');
wp_enqueue_script('jquery-ui');

wp_enqueue_script('knockout');

$id = (int)$_GET['id']; 
$post = get_post($id);    
$data = get_post_meta($post->ID, 'images', true);
$images = explode(",", $data);  
get_header(); ?> 
           
<div id="bg">
    <a href="#" class="nextImageBtn" data-bind="click: nextImage(event)" title="next"></a>
    <a href="#" class="prevImageBtn" data-bind="click: previousImage(event)" title="previous"></a>
    <img src="<?php echo $images[0]; ?>" width="1680" height="1050" alt="Denebola" title="Denebola" id="bgimg" /></div>
    <div id="preloader">
        <img src="<?php echo FARMERSGALLERY_PLUGIN_URL; ?>/images/gallery/ajax-loader_dark.gif" width="32" height="32" />
    </div>
    <div id="img_title"></div>
    <div id="toolbar">
        <a href="#" title="Maximize" data-bind="click: setViewMode('normal'), visible: fullViewMode">
            <img src="<?php echo FARMERSGALLERY_PLUGIN_URL; ?>images/gallery/toolbar_n_icon.png" width="50" height="50" />
        </a>
        <a href="#" title="Maximize" data-bind="click: setViewMode('full'), visible: !fullViewMode">
            <img src="<?php echo FARMERSGALLERY_PLUGIN_URL; ?>images/gallery/toolbar_fs_icon.png" width="50" height="50" />
        </a>
    </div>
    <div id="thumbnails_wrapper">
    <div id="outer_container">
        <div class="thumbScroller">
            <div class="container">
                <?php foreach($images as $image): ?> 
                    <div class="content">
                        <div>
                            <a href="<?php echo $image?>">
                                <img src="<?php echo $image?>" class="thumb" />
                            </a>
                        </div>
                    </div>       
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    
       var ViewModel = function ($) {
            var self = this;
            self.viewmodes =  {
                full: "full",
                normal: "normal",
                original: "original"
            };
            self.fullViewMode = ko.observable(true);
            self.tsMargin = 30; //first and last thumbnail margin (for better cursor interaction) 
            self.scrollEasing = 600; //scroll easing amount (0 for no easing) 
            self.scrollEasingType = "easeOutCirc"; //scroll easing type 
            self.thumbnailsContainerOpacity = 0.8; //thumbnails area default opacity
            self.thumbnailsContainerMouseOutOpacity = 0; //thumbnails area opacity on mouse out
            self.thumbnailsOpacity = 0.6; //thumbnails default opacity
            self.keyboardNavigation = true; //enable/disable keyboard navigation ("true" or "false")
            self.sliderLeft = 0;
            self.sliderWidth = 0;
            self.fadeSpeed = 0;
            self.placeMent = 0;
   
            self.thumbnails_wrapper = $("#thumbnails_wrapper");
            self.outer_container = $("#outer_container");
            self.thumbScroller = $(".thumbScroller");
            self.thumbScroller_container = $(".thumbScroller .container");
            self.thumbScroller_content = $(".thumbScroller .content");
            self.thumbScroller_thumb = $(".thumbScroller .thumb");
            self.preloader = $("#preloader");
            self.toolbar = $("#toolbar");
            self.toolbar_a = $("#toolbar a");
            self.bgimg = $("#bgimg");
            self.img_title = $("#img_title");
            self.nextImageBtn = $(".nextImageBtn");
            self.prevImageBtn = $(".prevImageBtn");  
            self.the_outer_container = $("#outer_container");  
            
            self.init = function() {
                // set default viewmode
                self.setViewMode(self.viewmodes.full);
                // Set control visibilty 
                self.setVisibilityControls('show');
                // Add margin
                self.thumbScroller_container.css("marginLeft", self.tsMargin + "px");
                // set bindings 
                self.setHoverBindings();
                self.setClickBindings();
                self.setRemainingBindings();
                // Show thumbnails
                self.setThumbsContainerWidth();
            }
            
            self.setViewMode = function(mode) {
                self.toolbar.data("imageViewMode", mode); 
                self.fullScreenBackground(self.bgimg, self.bgimg.data("newImageW"), self.bgimg.data("newImageH")); 
                if(mode == self.viewmodes.full) {
                    self.fullViewMode(true);
                } else {
                    self.fullViewMode(false);
                }
            }
            
            self.setVisibilityControls = function(state) {
                if(state == "hide"){
                    self.nextImageBtn.fadeOut();
                    self.prevImageBtn.fadeOut();
                } else {
                    self.nextImageBtn.fadeIn();
                    self.prevImageBtn.fadeIn();
                }
            }
            
            self.setThumbsContainerWidth = function() {
                self.thumbScroller_content.each(function (index, content) {
                    var $this=$(this);
                    $this.children().children().children(".thumb").fadeTo(self.fadeSpeed, self.thumbnailsOpacity);
                });
                self.thumbScroller_container.css("width", (250 * self.thumbScroller_content.length));   
            }
            
            self.setHoverBindings = function() {                     
                self.thumbnails_wrapper.hover(
                    function(){
                        $(this).stop().fadeTo("slow", 1);
                    },
                    function(){
                        $(this).stop().fadeTo("slow", self.thumbnailsContainerMouseOutOpacity);
                    }
                );                
                self.thumbScroller_thumb.hover(
                    function(){
                        $(this).stop().fadeTo(self.fadeSpeed, 1);
                    },
                    function(){ 
                        $(this).stop().fadeTo(self.fadeSpeed, self.thumbnailsOpacity);
                    }
                );                               
                self.toolbar.hover(
                    function(){ 
                        $(this).stop().fadeTo("fast", 1);
                    },
                    function(){ 
                        $(this).stop().fadeTo("fast", 0.4);
                    }
                );  
            }
            
            self.nextImage = function(event) {
                event.preventDefault();
                self.switchImage(self.outer_container.data("nextImage"));
                var $this=$("#outer_container a[href='" + self.outer_container.data("nextImage")+"']");
                self.getNextPrevImages($this);
                self.getImageTitle($this);
            }
            
            self.previousImage = function(event) {
                event.preventDefault();
                self.switchImage(self.outer_container.data("prevImage"));
                var $this=$("#outer_container a[href='" + self.outer_container.data("prevImage")+"']");
                self.getNextPrevImages($this);
                self.getImageTitle($this);
            }
            
            self.setClickBindings = function() {                               
                $("#outer_container a").click(function(event){
                    event.preventDefault();
                    var $this=$(this);
                    self.getNextPrevImages($this);
                    self.getImageTitle($this);
                    self.switchImage(this);
                    self.setVisibilityControls("show");
                }); 
            }
            
            self.setRemainingBindings = function() {
                self.sliderLeft = self.thumbScroller_container.position().left;
                self.fadeSpeed = 200;
                self.sliderWidth = self.outer_container.width();                
                self.thumbScroller.css("width", self.sliderWidth);
                self.placement = self.findPos(self.the_outer_container);                        
                self.thumbnails_wrapper.fadeTo(self.fadeSpeed, self.thumbnailsContainerOpacity);
           
                $(window).resize(function() {
                    self.fullScreenBackground("#bgimg", self.bgimg.data("newImageW"), self.gimg.data("newImageH"));
                    self.thumbScroller_container.stop().animate({left: sliderLeft}, 400,"easeOutCirc"); 
                    var newWidth = self.outer_container.width();
                    self.thumbScroller.css("width", newWidth);
                    self.sliderWidth = newWidth;
                    self.placement = self.findPos(self.the_outer_container);
                });                
                var the1stImg = new Image();
                the1stImg.onload = self.createDelegate(the1stImg, self.theNewImg_onload);
                the1stImg.src = self.bgimg.attr("src");
                self.outer_container.data("nextImage", $(".content").first().next().find("a").attr("href"));
                self.outer_container.data("prevImage", $(".content").last().find("a").attr("href"));                
                if(self.toolbar.css("display") != "none"){
                    self.toolbar.fadeTo("fast", 0.4);
                }             
                if(self.keyboardNavigation){
                    $(document).keydown(function(ev) {
                        if(ev.keyCode == 39) { 
                            self.switchImage(self.outer_container.data("nextImage"));
                            var $this = $("#outer_container a[href='"+ self.outer_container.data("nextImage")+"']");
                            self.getNextPrevImages($this);
                            self.getImageTitle($this);
                            return false; 
                        } else if(ev.keyCode == 37) { //left arrow
                            self.switchImage(self.outer_container.data("prevImage"));
                            var $this=$("#outer_container a[href='"+ self.outer_container.data("prevImage")+"']");
                            self.getNextPrevImages($this);
                            self.getImageTitle($this);
                            return false; 
                        }
                    });
                }
            }
            
            self.theNewImg_onload = function() {
                self.bgimg.data("newImageW", this.width).data("newImageH", this.height);
                self.backgroundLoad(self.bgimg, this.width, this.height, this.src);
            }
            
            self.fullScreenBackground = function(theItem, imageWidth, imageHeight) {
                var winWidth = $(window).width();
                var winHeight = $(window).height();
                
                if(self.toolbar.data("imageViewMode") != self.viewmodes.original) { 
                    var picHeight = imageHeight / imageWidth;
                    var picWidth = imageWidth / imageHeight;
                    if(self.toolbar.data("imageViewMode") == self.viewmodes.full) { 
                        if ((winHeight / winWidth) < picHeight) {
                            $(theItem).attr("width", winWidth);
                            $(theItem).attr("height", picHeight*winWidth);
                        } else {
                            $(theItem).attr("height", winHeight);
                            $(theItem).attr("width", picWidth*winHeight);
                        };
                    } else {
                        if ((winHeight / winWidth) > picHeight) {
                            $(theItem).attr("width", winWidth);
                            $(theItem).attr("height", picHeight*winWidth);
                        } else {
                            $(theItem).attr("height", winHeight);
                            $(theItem).attr("width", picWidth*winHeight);
                        };
                    }
                    $(theItem).css("margin-left",( winWidth-$(theItem).width())/2);
                    $(theItem).css("margin-top",( winHeight-$(theItem).height())/2);
                } else { //no scale
                    $(theItem).attr("width", imageWidth);
                    $(theItem).attr("height", imageHeight);
                    $(theItem).css("margin-left",( winWidth-imageWidth)/2);
                    $(theItem).css("margin-top",( winHeight-imageHeight)/2);
                }
            }
            
            self.findPos = function(obj) {
                var curleft = curtop = 0;
                if (obj.offsetParent) {
                    curleft = obj.offsetLeft
                    curtop = obj.offsetTop
                    while (obj = obj.offsetParent) {
                        curleft += obj.offsetLeft
                        curtop += obj.offsetTop
                    }
                }
                return [curtop, curleft];
            }
            
            self.backgroundLoad = function($this, imageWidth, imageHeight, imgSrc) {
                $this.fadeOut("fast",function() {
                    $this.attr("src", "").attr("src", imgSrc); //change image source
                    self.fullScreenBackground($this, imageWidth, imageHeight); //scale background image
                    self.preloader.fadeOut("fast",function(){$this.fadeIn("slow");});
                    var imageTitle = self.img_title.data("imageTitle");
                    if(imageTitle){
                        $this.attr("alt", imageTitle).attr("title", imageTitle);
                        self.img_title.fadeOut("fast",function(){
                            self.img_title.html(imageTitle).fadeIn();
                        });
                    } else {
                        self.img_title.fadeOut("fast",function(){
                            self.img_title.html($this.attr("title")).fadeIn();
                        });
                    }
                });
            }
            
            self.getImageTitle = function(elem) {                
                var title_attr = elem.children("img").attr("title"); 
                self.img_title.data("imageTitle", title_attr);
            }
            
            self.getNextPrevImages = function(curr) {                
                var nextImage = curr.parents(".content").next().find("a").attr("href");
                if(nextImage == null){ 
                    var nextImage = $(".content").first().find("a").attr("href");
                }
                self.outer_container.data("nextImage", nextImage);
                var prevImage = curr.parents(".content").prev().find("a").attr("href");
                if( prevImage == null){
                    var prevImage = $(".content").last().find("a").attr("href");
                }
                self.outer_container.data("prevImage", prevImage);
            }
            
            self.switchImage = function(img) {
                self.preloader.fadeIn("fast");
                var theNewImg = new Image();
                theNewImg.onload = self.createDelegate(theNewImg, self.theNewImg_onload);
                theNewImg.src = img;
            }
            
            self.createDelegate = function(contextObject, delegateMethod) {
                return function(){
                    return delegateMethod.apply(contextObject, arguments);
                }
            }
            
            self.init();
        };
    
    jQuery(document).ready(function($) {  
        var vm = new ViewModel($);
        ko.applyBindings(vm); 
	});
</script>
<?php 