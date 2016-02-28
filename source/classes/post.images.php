<?php 
require('../../../../wp-blog-header.php');
header('Content-type: application/json'); 
    $id = $_POST['id']; 
    $post = get_post($id);    
    $data = get_post_meta($post->ID, 'images', true);
    $images = explode(",", $data); 
    
     
    echo json_encode($images);
