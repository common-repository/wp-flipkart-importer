<?php
/*
Plugin Name: WP Flipkart Importer
Plugin URI: https://wordpress.org/plugins/wp-flipkart-importer/
Description: WP Flipkart Importer WordPress Plugin Import product using flipkart API .
Version: 1.4
Author: Shambhu Prasad Patnaik
Author URI:http://socialcms.wordpress.com/
*/
include_once('wp-flipkart-importer-function.php');
include_once('wp-flipkart-importer-help.php');

if (!function_exists('flipkart_importer_add_menus')) :
function flipkart_importer_add_menus()
{
 add_menu_page('Flipkart Importer', 'Flipkart Importer', 'manage_options', __FILE__, 'flipkartImporter_setting',plugin_dir_url(__FILE__).'/flipkart.png');
 add_submenu_page(__FILE__, 'Help', 'Help','manage_options', 'flipkartImporter_help','flipkartImporter_help');
}
endif;
add_action('admin_menu', 'flipkart_importer_add_menus');

if (!function_exists('flipkartImporter_setting')) :
function flipkartImporter_setting()
{
 wp_enqueue_style( 'wp-flipkart-importer',plugins_url('/stylesheet.css', __FILE__) );
 $action='';
 if(isset($_GET['action']))
 $action = wp_filter_nohtml_kses($_GET['action']);
 $error_message='';
 $error=false;
 
 $feed_run_array=array();
 $feed_run_array[]=array('id'=>'hour','text'=>'Hours');
 $feed_run_array[]=array('id'=>'day','text'=>'Days');
 $feed_run_array[]=array('id'=>'week','text'=>'Weeks');
 

 $feed_status_array=array();
 $feed_status_array[]=array('id'=>'active','text'=>'active');
 $feed_status_array[]=array('id'=>'inactive','text'=>'inactive');

 $publish_status_array=array();
 $publish_status_array[]=array('id'=>'publish','text'=>'published');
 $publish_status_array[]=array('id'=>'draft','text'=>'draft');

 $sdata = (array) json_decode(get_option('flipkart_importer_setting'));
 $action_url=admin_url('admin.php?page=wp-flipkart-importer/wp-flipkart-importer.php');
 $form_action=admin_url('admin.php?page=wp-flipkart-importer/wp-flipkart-importer.php&action=save');
 $form_button='<input type="submit" value="Update">';
 if ($action!="") 
 {
  switch ($action) 
  {
   case 'save':
     $affiliate_id    = sanitize_text_field($_POST['TR_affiliate_id']);
     $affiliate_token = sanitize_text_field($_POST['TR_affiliate_token']);
     $wp_category     = wp_filter_nohtml_kses($_POST['wp_category']);
     $keyword         = sanitize_text_field($_POST['keyword']);
     $publish_status  = wp_filter_nohtml_kses($_POST['publish_status']);
     $feed_status     = wp_filter_nohtml_kses($_POST['feed_status']);
     $max_item        = sanitize_text_field($_POST['IR_max_item']);
     $run_every       = wp_filter_nohtml_kses($_POST['IR_run_every']);
     $occurrence_type = wp_filter_nohtml_kses($_POST['occurrence_type']);
     $display_template= stripslashes_deep($_POST['display_template']);
     if($max_item<=0)
     {
      $error_message[] ='Max Item  cannot be less then 0.';
      $error=true;
     } 
     if($max_item>10)
     {
      $error_message[] ='Max Item  cannot be grater then 10.';
      $error=true;
     } 
     if(!$error)
	 {
      $sdata['affiliate_id']    = $affiliate_id;
	  $sdata['affiliate_token'] = $affiliate_token;
	  $sdata['wp_category']     = $wp_category;
	  $sdata['publish_status']  = $publish_status;
	  $sdata['feed_status']     = $feed_status;
	  $sdata['keyword']         = $keyword;
	  $sdata['max_item']        = $max_item;
	  $sdata['display_template']= $display_template;
	  $sdata['occurrence_type'] = $occurrence_type;
	  $sdata['run_every']       = $run_every;
	  $sdata['blog_id']         = get_current_blog_id();
	  
      $sdata=json_encode($sdata);
	  update_option('flipkart_importer_setting',$sdata);
      update_option('flipkartImporter_message','Successfully Updated.');
      echo "<meta http-equiv='refresh' content='0;url=".$action_url."&message=update' />";
	  die();
	 }
    break;
   case 'fetch_now':
    flipkart_importer_feed_import();
    echo "<meta http-equiv='refresh' content='0;url=".$action_url."' />"; 
    die('');
    break;
  }
 }
 if(!$error)
 {
  $affiliate_id     = stripslashes_deep($sdata['affiliate_id']); 
  $affiliate_token  = stripslashes_deep($sdata['affiliate_token']); 
  $wp_category      = stripslashes_deep($sdata['wp_category']); 
  $keyword          = stripslashes_deep($sdata['keyword']); 
  $publish_status   = stripslashes_deep($sdata['publish_status']); 
  $feed_status      = stripslashes_deep($sdata['feed_status']); 
  $max_item         = stripslashes_deep($sdata['max_item']); 
  $run_every        = wp_filter_nohtml_kses($sdata['run_every']);
  $occurrence_type  = wp_filter_nohtml_kses($sdata['occurrence_type']);
  $display_template = stripslashes_deep($sdata['display_template']); 
 }
 if ($action=="") 
 if($flipkartImporter_message = get_option('flipkartImporter_message'))
 {
   echo' <div class="updated"><p>'.$flipkartImporter_message.'</p></div>';
   update_option('flipkartImporter_message','');
 }
 if($affiliate_id!='' && $affiliate_token!='')
 $form_button .= ' <a  href="'.admin_url('admin.php?page=wp-flipkart-importer/wp-flipkart-importer.php&action=fetch_now').'" class="button">Import Product Now</a>';

if (!function_exists('curl_version'))
 {
  echo '<div class="error"><p>Please enable php  curl before fetch </p></div>';
 } 
 echo'<div style="width:550px;">
      <div class="wrap" >
       <h2>WP Flipkart Importer</h2></div>
		     <p class="intro">WP Flipkart Importer WordPress Plugin Import product using flipkart API.</p>
		      '.
 ((is_array($error_message)&& count($error_message)>0)?' <div class="error"><p>'.implode("<br>",$error_message).'</p></div>':'').'

		  
  		  <table border="0" cellspacing="3" cellpadding="2"  width="100%">
		  <tr><form method="post" action="'.$form_action.'" onsubmit="return importerValidateForm(this)">
           <td valign="top"><nobr>Fk-Affiliate-Id</nobr></td>
		   <td valign="top" colspan="2"><nobr><input type="text" name="TR_affiliate_id" value="'.$affiliate_id.'" class="flipkartImporterInput">&nbsp;<span class="inputRequirement">*</span></nobr><div><span style="color:#444444;font-size:11px;">Your Fk-Affiliate-Id from flipkart. Don\'t you have such a key? <a href="https://affiliate.flipkart.com/api/api-token" target="_blank" class="blue"><u>Request one here</u></a>.</span></div></td>
          </tr> 		  
		  <tr>
           <td valign="top"><nobr>Fk-Affiliate-Token</nobr></td>
		   <td valign="top" colspan="2"><nobr><input type="text" name="TR_affiliate_token" value="'.$affiliate_token.'" class="flipkartImporterInput">&nbsp;<span class="inputRequirement">*</span></nobr><div><span style="color:#444444;font-size:11px;">Your Fk-Affiliate-Token from flipkart. Don\'t you have such a key? <a href="https://affiliate.flipkart.com/api/api-token" target="_blank" class="blue"><u>Request one here</u></a>.</span></div></td>
          </tr> 		  
		  <tr>
           <td>Import Keyword</td>
           <td colspan="2"><input type="text" name="keyword" value="'.$keyword.'" class="flipkartImporterInput"></td>
          </tr> 
		 <tr>
          <td>Feed Status</td>
          <td colspan="2" >'.flipkart_importer_draw_pull_down_menu('feed_status', $feed_status_array, $feed_status).'</td>
          </tr>  
		  <tr>
		   <td valign="top">Max Item Import</td>
		   <td valign="top"><input type="text" name="IR_max_item" value="'.$max_item.'" size="2"><br><span style="color:#444444;font-size:11px;">Maximum value is 10;</span></td>
          </tr>
		  <tr>
			<td>New Post Status</td>
			<td colspan="2" >'.flipkart_importer_draw_pull_down_menu('publish_status',$publish_status_array,$publish_status).'</td>
		  </tr>                
       <tr>
        <td>Category Name</td>
        <td colspan="2"  >'.flipkart_importer_get_category_drop_down('wp_category','',$wp_category).'</td>
       </tr>
       <tr>
        <td>Run Every</td>
        <td colspan="2"  ><input name="IR_run_every" value="'.$run_every.'" size="2" type="text"> '.flipkart_importer_draw_pull_down_menu('occurrence_type', $feed_run_array, $occurrence_type,'').'</td>
       </tr> 
	   <tr>
         <td  valign="top"><nobr>Display Template</nobr></td>
         <td colspan="2"  ><textarea name="display_template" wrap="1" cols="80" rows="8">'.stripslashes($display_template).'</textarea><div> <span style="color:#444444;font-size:11px;"><b>Template Macro : </b> {product_description},{product_color},{product_image_url},{product_detail_url},<a href="admin.php?page=flipkartImporter_help#template_macro">More</a></span></div></td>
       </tr>  
	   <tr>
         <td  colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$form_button.'</td>
       </tr></form>
	 </table>      
	<div><h3 class="flipkart_heading"> Job Board & Related Plugin</h3>
        <ul class="flipkart_help_square">
         <li><a href="https://socialcms.wordpress.com/2016/03/28/flipkart-intense-search/" target="_blank">Flipkart Intense Search </a></li>
		 <li><a href="https://socialcms.wordpress.com/2013/12/28/indeed-job-importer/" target="_blank">Indeed Job Importer</a></li>
		 <li><a href="https://socialcms.wordpress.com/2015/03/10/indeed-intense-search/" target="_blank">Indeed Intense Search</a></li>
		 <li><a href="http://socialcms.wordpress.com/contact-us/" target="_blank">Pro Indeed Job Importer (Premium Version)</a></li>
		 <li><a href="http://wordpress.org/plugins/juju-job-importer/" target="_blank">Juju Job Importer</a></li>
		 <li><a href="https://wordpress.org/plugins/beyond-job-importer/" target="_blank">Beyond Job Importer</a></li>
		 <li><a href="http://socialcms.wordpress.com/2014/01/21/careerbuilder-job-importer/" target="_blank">CareerBuilder Job Importer</a></li>
   		 <li><a href="http://socialcms.wordpress.com/2014/03/05/simplyhired-job-importer/" target="_blank">SimplyHired Job Importer</a></li>
		 <li><a href="http://socialcms.wordpress.com/2014/07/02/authenticjobs-job-importer/" target="_blank">AuthenticJobs Job Importer</a></li>
		 <li><a href="http://socialcms.wordpress.com/2014/02/07/careerjet-job-importer/" target="_blank">CareerJet Job Importer</a></li>
		 <li><a href="https://socialcms.wordpress.com/2014/11/17/adzuna-job-importer/" target="_blank">Adzuna Job Importer</a></li>
		 <li><a href="http://socialcms.wordpress.com/category/job-board-2/" target="_blank">Job Board</a></li>
	  </ul>
	</div> 		   
		<br>

	<div>More Detail - <a href="http://socialcms.wordpress.com/" target="_blank">http://socialcms.wordpress.com</a></div>
	<div>In case of any clarifications, pl. contact us at - <a href="http://socialcms.wordpress.com/contact-us/" target="_blank">http://socialcms.wordpress.com/contact-us/</a></div>
	<br>
	<div><b>Thanks a Lot</b></div>
	<br>
	<br>
    <div align="center">********************</div>
		   
	   </div>';

}
endif;
if (!function_exists('flipkartImporter_activate')):
function flipkartImporter_activate()
{

 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


 $display_template ='Price :  {product_currency}  {product_price}
                     <img src="{product_image_url}" align="left">
					 {product_description}
					 {product_detail_more_link}';

 $sdata =array('affiliate_id'=>"",'affiliate_token'=>"",'keyword'=>'','wp_category'=>'','publish_status'=>'published','feed_status'=>'inactive','run_every'=>'1','occurrence_type'=>'day','max_item'=>2,'display_template'=>$display_template,'next_activate'=>'');	  
 $sdata=json_encode($sdata);
 wp_schedule_event( time(), 'hourly', "flipkart_importer_hook");
 update_option('flipkart_importer_setting',$sdata);
}
endif;
//////////////
if (!function_exists('flipkartImporter_deactivate')):
function flipkartImporter_deactivate()
{
 wp_clear_scheduled_hook("flipkart_importer_hook");	
 delete_option('flipkart_importer_setting');
 delete_option('flipkartImporter_message');
}
endif;
////
add_action('flipkart_importer_hook','flipkartImporter_checkhook');
function flipkartImporter_checkhook()
{
 $now=current_time('mysql');
 flipkart_importer_feed_import($now);
}

register_deactivation_hook(__FILE__, 'flipkartImporter_deactivate');
register_activation_hook(__FILE__, 'flipkartImporter_activate');
?>