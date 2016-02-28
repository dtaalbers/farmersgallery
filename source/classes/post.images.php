<?php 
require('../../../../wp-blog-header.php');
header('Content-type: application/json'); 
    $id = $_POST['id']; 
    $function = $_POST['function'];
    
    $post = get_post($id);    
    $data = get_post_meta($post->ID, 'images', true);
    $images = explode(",", $data); ;
    
    switch($function) {
        case "images":
            echo json_encode($images);
            break;
        case "title":
            echo json_encode(array( 
                "title" => $post->post_title,
                "description" => $post->post_content
            ));
            break;
        default:
            echo 'Could not find a function';
            break;
    }
    
     

