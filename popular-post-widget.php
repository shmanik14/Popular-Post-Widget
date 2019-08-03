<?php
/*
Plugin Name: Popular Post Widget
Plugin URI: https://webcrews.net/
Description: Its a simple widget plugins
Version: 0.1.0
Author: Sahadat Hossain
Author URI: https://webcrews.net/
Text Domain: popular-post-widget
*/


/**
 * wc Popular Post Widget
 *
 */

// function to display number of posts.
function getPostViews($postID){
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 View";
    }
    return $count.' Views';
}

// function to count views.
function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}


// Add it to a column in WP-Admin
add_filter('manage_posts_columns', 'posts_column_views');
add_action('manage_posts_custom_column', 'posts_custom_column_views',5,2);
function posts_column_views($defaults){
    $defaults ['post_views'] = __('Views');
    return $defaults;
}
function posts_custom_column_views($column_name, $id){
  if($column_name === 'post_views'){
        echo getPostViews(get_the_ID());
    }
}




class wc_popular_post_widget extends WP_Widget{

  //setup widget name, description etc
  public function __construct(){
    $widget_ops = array(
      'classname' => 'wc_popular_widget',
      'description' => 'Popular Post Widget'
    );

    parent::__construct( 'wc_popular_post', 'wc Popular Post', $widget_ops);
  }

  //back-end display of widget
  public function form( $instance ){

    $title = ( !empty( $instance[ 'title' ]) ? $instance[ 'title' ] : null);
    $total_show_post = ( !empty( $instance[ 'total_show_post' ]) ? absint ($instance[ 'total_show_post' ]) : 4);

    $output = '<p>';
    $output .= '<label for="'.esc_attr($this->get_field_id( 'title' )).'">Title:</label>';
    $output .= '<input type="text" class="widefat" id="'.esc_attr($this->get_field_id( 'title' )).'" name="'.esc_attr($this->get_field_name( 'title' )).'" value="'.esc_attr( $title ).'"';
    $output .= '</p>';

    $output .= '<p>';
    $output .= '<label for="'.esc_attr($this->get_field_id( 'total_show_post' )).'">Number Of Posts:</label>';
    $output .= '<input type="number" class="widefat" id="'.esc_attr($this->get_field_id( 'total_show_post' )).'" name="'.esc_attr($this->get_field_name( 'total_show_post' )).'" value="'.esc_attr( $total_show_post ).'"';
    $output .= '</p>';

    echo $output;
  }

  //update widget
  public function update($new_instance, $old_instance){
    $instance = array();
    $instance['title'] = (!empty($new_instance['title']) ? strip_tags($new_instance['title']):'');
    $instance['total_show_post'] = (!empty($new_instance['total_show_post']) ? absint(strip_tags($new_instance['total_show_post'])):0);

    return $instance;
  }


  //front-end display of widget
  public function widget($args, $instance){
    $total_show_post = absint($instance['total_show_post']);

    $pp_args = array(
      'post_type'   =>  'post',
      'posts_per_page'   =>  $total_show_post,
      'meta_key'   =>  'post_views_count',
      'orderby'   =>  'meta_value_num',
      'order'   =>  'DESC'
    );



    $popular_post = new WP_Query($pp_args);

    // var_dump($popular_post);
    echo $args['before_widget'];

    if(!empty($instance['title'])):
      echo $args['before_title'] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args['after_title'];
    endif;
    if($popular_post->have_posts()): while($popular_post->have_posts()): $popular_post->the_post(); ?>
    	<div class="blog-post-content">
			<a href="<?php the_permalink(); ?>">
              <?php if(has_post_thumbnail()){
                the_post_thumbnail( '', array('class' => 'img-responsive'));
              } ?>
			<a href="<?php the_permalink(); ?>"><h4><?php the_title(); ?></h4></a>
			<?php echo wp_trim_words( get_the_content(), 20, '[...]' ); ?>
		</div><!-- /.end of blog-post-content -->
		<div class="horizontal-line"></div>  
    <?php endwhile; else: echo 'No Post Found'; endif;

    echo $args['after_widget'];
  }


}
add_action('widgets_init', function(){
	register_widget( 'wc_popular_post_widget' );
});