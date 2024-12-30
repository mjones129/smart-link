<?php
// Function to get the slug from a post ID
function get_post_slug($post_id) {
    $post = get_post($post_id);
    if ($post) {
        return $post->post_name;
    }
    return null;
}

// Example usage
$post_id = 123; // Replace with your post ID
$slug = get_post_slug($post_id);
echo 'The slug is: ' . $slug;
?>
