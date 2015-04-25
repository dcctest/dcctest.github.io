<?php

$_SERVER = array(
    'HTTP_HOST' => "localhost",
    'REQUEST_URI' => "/"
);

function get_bloginfo($x) {
    $info = array(
        "url" => "http://localhost",
        "name" => "SolidNYC",
        "description" => "SolidNYC",
        "stylesheet_directory" => "wp-content/themes/map",
        "stylesheet_url" => "wp-content/themes/map/style.css",
        "zig" => "zag"
    );
    $r = $info[$x];
    if (!$r) throw new Exception($x);
    return $r;
}

function site_url($x) {
    return get_bloginfo("url") . $x;
}

function bloginfo($x) {
    echo get_bloginfo($x);
}

function wp_head() {
}

function add_action($a,$b) {
}

function add_filter($a,$b) {
}

$page_at = 0;
class Post {
    public $post_name;
    public $post_title;
    public $post_template;
};

$map_post = new Post();
$map_post->post_name = "Map";
$map_post->post_title = "Map";
$map_post->post_template = "map";

$dir_post = new Post();
$dir_post->post_name = "Directory";
$dir_post->post_title = "Directory";
$dir_post->post_template = "directory";

$posts = [$map_post, $dir_post];

function have_posts() {
    global $page_at;
    global $posts;
    return $page_at<count($posts);
}

function the_title() {
    global $post;
    return $post->post_title;
}

function the_post() {
    global $page_at;
    global $post;
    global $posts;
    $post = $posts[$page_at];
    $page_at++;
}

function get_page_template() {
    global $post;
    return $post->post_template;
}

function get_template_part($x) {
    include($x . ".php");
}

function rewind_posts() {
    global $page_at;
    $page_at = 0;
}

function wp_footer() {
}

function get_posts() {
    return array();
}

function wp_get_object_terms($x,$y,$z) {
    return array();
}

function get_categories($x) {
    return array();
}

include("./functions.php");


?>
