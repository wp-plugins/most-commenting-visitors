<?php
/*
Plugin Name: Most Commenting Visitors
Plugin URI: http://rubensargsyan.com/wordpress-plugin-most-commenting-visitors/
Description: This is a widget plugin which helps to display the visitors who left the most number of comments in the Wordpress blog.
Version: 1.3
Author: Ruben Sargsyan
Author URI: http://rubensargsyan.com/
*/

/*  Copyright 2009 Ruben Sargsyan (email: info@rubensargsyan.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

$most_commenting_visitors_url = WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));
$most_commenting_visitors_plugin_title = "Most Commenting Visitors";
$most_commenting_visitors_plugin_prefix = "most_commenting_visitors_";

function most_commenting_visitors_load(){
    $most_commenting_visitors_plugin_title = "Most Commenting Visitors";
    $most_commenting_visitors_plugin_prefix = "most_commenting_visitors_";
    $most_commenting_visitors_plugin_version = "1.3";

    if(get_option($most_commenting_visitors_plugin_prefix."widget_options")===false){
        $most_commenting_visitors_widget_options = array("title"=>$most_commenting_visitors_plugin_title,"count"=>5,"show_avatar"=>"no","show_count"=>"no","excluding_emails"=>"");

        add_option($most_commenting_visitors_plugin_prefix."widget_options",$most_commenting_visitors_widget_options);
    }

    if(get_option($most_commenting_visitors_plugin_prefix."version")===false){
        add_option($most_commenting_visitors_plugin_prefix."version",$most_commenting_visitors_plugin_version);
    }elseif(get_option($most_commenting_visitors_plugin_prefix."version")<$most_commenting_visitors_plugin_version){
        update_option($most_commenting_visitors_plugin_prefix."version",$most_commenting_visitors_plugin_version);
    }

    register_sidebar_widget($most_commenting_visitors_plugin_title, 'most_commenting_visitors_widget');
    register_widget_control($most_commenting_visitors_plugin_title, 'most_commenting_visitors_widget_options');
}

function most_commenting_visitors_widget_options(){
    global $most_commenting_visitors_plugin_prefix;

    if(isset($_POST[$most_commenting_visitors_plugin_prefix."title"]) && isset($_POST[$most_commenting_visitors_plugin_prefix."count"])){
        $most_commenting_visitors_widget_options["title"] = strip_tags(stripslashes($_POST[$most_commenting_visitors_plugin_prefix."title"]));
        $most_commenting_visitors_widget_options["count"] = intval($_POST[$most_commenting_visitors_plugin_prefix."count"]);
        if(isset($_POST[$most_commenting_visitors_plugin_prefix."show_avatar"])){
            $most_commenting_visitors_widget_options["show_avatar"] = "yes";
        }else{
            $most_commenting_visitors_widget_options["show_avatar"] = "no";
        }

        if(isset($_POST[$most_commenting_visitors_plugin_prefix."show_count"])){
            $most_commenting_visitors_widget_options["show_count"] = "yes";
        }else{
            $most_commenting_visitors_widget_options["show_count"] = "no";
        }

        $most_commenting_visitors_widget_options["excluding_emails"] = strip_tags(stripslashes($_POST[$most_commenting_visitors_plugin_prefix."excluding_emails"]));

        if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/",$_POST[$most_commenting_visitors_plugin_prefix."start_date"])){
            $most_commenting_visitors_widget_options["start_date"] = $_POST[$most_commenting_visitors_plugin_prefix."start_date"];
        }else{
            $most_commenting_visitors_widget_options["start_date"] = "";
        }

        update_option($most_commenting_visitors_plugin_prefix."widget_options", $most_commenting_visitors_widget_options);
    }

    $most_commenting_visitors_widget_options = get_option("most_commenting_visitors_widget_options");
    ?>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>title">Title:</label>
	<input class="widefat" id="<?php echo($most_commenting_visitors_plugin_prefix); ?>title" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>title" type="text" value="<?php echo(esc_attr($most_commenting_visitors_widget_options["title"])); ?>" /></p>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>count">Visitors count:</label>
	<input class="widefat" id="<?php echo($most_commenting_visitors_plugin_prefix); ?>count" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>count" type="text" value="<?php echo(esc_attr($most_commenting_visitors_widget_options["count"])); ?>" /></p>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_avatar">Show avatar:</label>	<input id="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_avatar" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_avatar" type="checkbox" <?php if($most_commenting_visitors_widget_options["show_avatar"]=="yes"){ echo('checked="checked"'); } ?> /></p>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_count">Show comments count:</label> <input id="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_count" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>show_count" type="checkbox" <?php if($most_commenting_visitors_widget_options["show_count"]=="yes"){ echo('checked="checked"'); } ?> /></p>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>excluding_emails">Exclude emails (separate by commas):</label>
	<input class="widefat" id="<?php echo($most_commenting_visitors_plugin_prefix); ?>excluding_emails" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>excluding_emails" type="text" value="<?php echo(esc_attr($most_commenting_visitors_widget_options["excluding_emails"])); ?>" /></p>
    <p><label for="<?php echo($most_commenting_visitors_plugin_prefix); ?>start_date">Start date (Format: YYYY-MM-DD):</label>
	<input class="widefat" id="<?php echo($most_commenting_visitors_plugin_prefix); ?>start_date" name="<?php echo($most_commenting_visitors_plugin_prefix); ?>start_date" type="text" value="<?php echo($most_commenting_visitors_widget_options["start_date"]); ?>" /></p>
    <?php
}

function most_commenting_visitors_widget($args){
    global $wpdb, $most_commenting_visitors_plugin_title, $most_commenting_visitors_plugin_prefix;

    extract($args, EXTR_SKIP);

    $most_commenting_visitors_widget_options = get_option($most_commenting_visitors_plugin_prefix."widget_options");
    if($most_commenting_visitors_widget_options["title"]!=""){
      $widget_title = $most_commenting_visitors_widget_options["title"];
    }else{
      $widget_title = $most_commenting_visitors_plugin_title;
    }

    if($most_commenting_visitors_widget_options["count"]>0){
      $count = $most_commenting_visitors_widget_options["count"];
    }else{
      $count = 5;
    }

    if($most_commenting_visitors_widget_options["start_date"]!==""){
        $start_date_query = " AND comment_date>'".$most_commenting_visitors_widget_options["start_date"]."' ";
    }else{
        $start_date_query = "";
    }


    $excluding_emails = explode(",",$most_commenting_visitors_widget_options["excluding_emails"]);
    if(!empty($excluding_emails)){
        $excluding_emails_query = "";
        foreach($excluding_emails as $excluding_email){
            if(eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$",trim($excluding_email))){
                $excluding_emails_query .= " AND $wpdb->comments.comment_author_email!='".trim($excluding_email)."'";
            }
        }
    }

    $commenting_visitors = $wpdb->get_results("SELECT $wpdb->comments.comment_author, $wpdb->comments.comment_author_email, $wpdb->comments.comment_author_url, $wpdb->comments.comment_approved, COUNT($wpdb->comments.comment_author_email) AS comments_count FROM $wpdb->comments JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID WHERE comment_approved = '1' $start_date_query AND comment_type = '' AND post_status = 'publish' $excluding_emails_query GROUP BY comment_author_email ORDER BY comments_count DESC LIMIT ".$count);

    echo($before_widget);
    echo($before_title.$widget_title.$after_title);
    ?>
    <ul>
    <?php
    if($commenting_visitors){
        foreach($commenting_visitors as $commenting_visitor){
            if($commenting_visitor->comments_count==1){
                $display_comments_count = "(".$commenting_visitor->comments_count." comment)";
            }else{
                $display_comments_count = "(".$commenting_visitor->comments_count." comments)";
            }
        ?>
            <li><?php if($most_commenting_visitors_widget_options["show_avatar"]=="yes"){ echo(get_avatar($commenting_visitor->comment_author_email,32)); } ?> <a href="<?php echo($commenting_visitor->comment_author_url); ?>"><?php echo($commenting_visitor->comment_author); ?></a> <?php if($most_commenting_visitors_widget_options["show_count"]=="yes"){ echo($display_comments_count); } ?></li>
        <?php
        }
    }else{
    ?>
        <li>There is no comment.</li>
    <?php
    }
    ?>
    </ul>
    <?php
    echo($after_widget);
}

add_action('plugins_loaded','most_commenting_visitors_load');
?>