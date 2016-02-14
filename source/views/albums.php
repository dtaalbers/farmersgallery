<?php
    /*
        Template Name: Albums
    */      
    wp_enqueue_style('main');
    wp_enqueue_style('grid');
    
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
                        <?php                  
                            $itemId = $_GET['id']; 	     		
                            $post = get_post($itemId);				
                            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail'); 
                        ?>	                                         
                        <h3><?php the_title()?></h3>                
                        <img class="img-responsive" src="<?php echo (empty($image) ?  'http://placehold.it/1000x1000' : $image[0])?>">
                        
                        <button onclick="window.location.href='<?php echo get_site_url().'/index?p='.get_the_ID();?>'" class="btn">Open album</button>
                    </div>           
                </div> 
            <?php endforeach; ?>  
        </div>
    </div>  
</div> 
<?php get_footer(); ?>