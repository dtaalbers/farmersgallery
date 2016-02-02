<?php 
// jQuery
wp_enqueue_script('jquery');
wp_enqueue_script('knockout');
// Css
wp_enqueue_style('main');
wp_enqueue_style('grid');
wp_enqueue_style('font-awsome');
// This will enqueue the Media Uploader script
wp_enqueue_media();
?>

<!-- Container holding the upload button -->
<div id="container">
    <input type="button" data-bind="click: addImages" id="upload-btn" class="button-secondary" value="Select Images">
</div>

<!-- Preview box where the image will be placed -->
<div id="preview">
	<div class="row" data-bind="foreach: images">
		<div class="col-md-1 image-container">
            <div class="close" data-bind="click: $root.removeImage"><i class="fa fa-close"></i></div>
			<div class="image">
				<img class="img-responsive" data-bind="attr:{src: url}">				
			</div>
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
        self.addImages = function() {
	        var imageContainer = wp.media({ 
	            title: 'Upload Image',
	            multiple: true
	        }).open()
	        .on('select', function(e){  
	            imageContainer.state().get('selection').each(function(image){
        			var jsonImage = image.toJSON();
	            	self.images.push(new Image(jsonImage.url));	
	        	});
	        });
        }
        self.removeImage = function(image) {
            var sure = confirm("Are you sure you want to delete this image?");
            if(sure) {               
                self.images.remove(image); 
            }
        }
    };

    jQuery(document).ready(function($) {
        var vm = new ViewModel($);
        ko.applyBindings(vm);
	});
</script>