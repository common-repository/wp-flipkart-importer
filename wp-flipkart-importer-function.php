<?php
if (!function_exists('flipkart_importer_draw_pull_down_menu')):
function flipkart_importer_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) 
{
 $field = '<select name="' . htmlspecialchars($name) . '"';
 if ($parameters!='') 
  $field .= ' ' . $parameters;
 $field .= '>';
 for ($i=0, $n=sizeof($values); $i<$n; $i++) 
 {
  $field .= '<option value="' . htmlspecialchars($values[$i]['id']) . '"';
  if(is_array($default))
  {
   if(in_array($values[$i]['id'],$default))
   {
    $field .= ' SELECTED';
   }
  }
  else
  {
   if($default==$values[$i]['id'])
   {
    $field .= ' SELECTED';
   }
  }
  $field .= '>' . htmlspecialchars($values[$i]['text'],ENT_QUOTES) . '</option>';
 }
 $field .= '</select>';
 if ($required == true) $field .= '&nbsp;<span class="inputRequirement">*</span>';;
  return $field;
}
endif;
/////////////////
/////////////////
if (!function_exists('flipkart_importer_get_category_list')) :
function flipkart_importer_get_category_list($parent=0,$level=0)
{
 $args = array(
	'type'                     => 'post',
	'child_of'                 => 0,
	'parent'                   => $parent,
	'orderby'                  => 'name',
	'order'                    => 'ASC',
	'hide_empty'               => 0,
	'hierarchical'             => 1,
	'exclude'                  => '',
	'include'                  => '',
	'number'                   => '',
	'taxonomy'                 => 'category',
	'pad_counts'               => false );
 $list_set =array();
 $categories = get_categories( $args );
 foreach($categories as $category)
 {
  $list_set[]=array('id'=>$category->term_id,'text'=>$category->name,'level'=>$level);
  $list= flipkart_importer_get_category_list( $category->term_id ,($level+1));
  if(is_array($list) && count($list)>0)
  $list_set =array_merge($list_set,$list);
 }
 return $list_set;
}
endif;
///////////
if (!function_exists('flipkart_importer_get_category_drop_down')):
function flipkart_importer_get_category_drop_down($name='category',$parameters='',$selected="",$header="",$header_value="",$hierarchical=true)
{
 $cat_list =array();
 if($header!='')
 $cat_list[0] =array('id'=>$header_value,'text'=>$header);
 $cat_list= array_merge($cat_list,flipkart_importer_get_category_list());
 if($hierarchical)
 foreach($cat_list as $key =>$value)
 {
  $cat_list[$key]['text_pad'] = str_repeat(' - ',$value['level']);
 }
 return flipkart_importer_draw_pull_down_menu($name, $cat_list, $selected,$parameters);
}
endif;
////////

if (!function_exists('flipkart_importer_next_runtime')):
function flipkart_importer_next_runtime($occurrence,$occurrence_type,$start_date)
{
 $end_date   = current_time('mysql');
 $occurrence = (int) $occurrence;
 $year    = substr($start_date,0,4);
 $month   = substr($start_date,5,2);
 $day     = substr($start_date,8,2);
 $hour    = substr($start_date,11,2);
 $minutes = substr($start_date,14,2);
 $seconds = substr($start_date,17,2);
 if(!checkdate ( (int)$month, (int) $day, (int) $year))
 $start_date ='0000-00-00 00:00:00';
 switch($occurrence_type)
 {
  case "hour":
   if($start_date=="" ||$start_date=='0000-00-00 00:00:00')
    $end_date=date("Y-m-d H:i:s",mktime(date('H')+$occurrence,date('i'),date('s'),date('m'),date('d'),date('Y')));
   else
    $end_date=date("Y-m-d H:i:s",mktime($hour+$occurrence,$minutes,$seconds,$month,$day,$year));
   break;
  case "day":
   if($start_date=="" ||$start_date=='0000-00-00 00:00:00')
    $end_date=date("Y-m-d H:i:s",mktime(date('H'),date('i'),date('s'),date('m'),date('d')+$occurrence,date('Y')));
   else
    $end_date=date("Y-m-d H:i:s",mktime($hour,$minutes,$seconds,$month,$day+$occurrence,$year));
   break;
  case "week":
   if($start_date=="" ||$start_date=='0000-00-00 00:00:00')
    $end_date=date("Y-m-d H:i:s",mktime(date('H'),date('i'),date('s'),date('m'),date('d')+($occurrence*7),date('Y')));
   else
    $end_date=date("Y-m-d H:i:s",mktime($hour,$minutes,$seconds,$month,$day+($occurrence*7),$year));
   break;
 }
 return $end_date;
}
endif;
////////
if (!function_exists('flipkart_importer_feed_import')):
function flipkart_importer_feed_import($current_time='')
{
 global $wpdb;
 $idata = (array) json_decode(get_option('flipkart_importer_setting'));
 $affiliate_id     = stripslashes_deep($idata['affiliate_id']); 
 $affiliate_token  = stripslashes_deep($idata['affiliate_token']); 
 $wp_category      = stripslashes_deep($idata['wp_category']); 
 $keyword          = stripslashes_deep($idata['keyword']); 
 $publish_status   = stripslashes_deep($idata['publish_status']); 
 $max_item         = stripslashes_deep($idata['max_item']); 
 $run_every        = wp_filter_nohtml_kses($idata['run_every']);
 $occurrence_type  = wp_filter_nohtml_kses($idata['occurrence_type']);
 $display_template = stripslashes_deep($idata['display_template']); 
 $next_activate    = stripslashes_deep($idata['next_activate']); 
 $feed_status      = stripslashes_deep($idata['feed_status']); 
 $blog_id          = stripslashes_deep($idata['blog_id']); 
 $import_count     = 0;

 if(!(($next_activate=='' ||  $next_activate<=$current_time  || $current_time=='') && $feed_status=='active'))
  return false;
  $switch = false;
  if (function_exists('is_multisite') && is_multisite())
  {
   if ( get_current_blog_id() != $blog_id ) {
    $switch = true;
    switch_to_blog( $blog_id );
   }
  }
  $max_item1 = $max_item;
  if($max_item < 5)
  $max_item1=5; 
  $parameter =array('affiliate_id'=>$affiliate_id,'affiliate_token'=>$affiliate_token,'keyword'=>$keyword,'max_item'=>$max_item1);	  

  $content         = flipkartImporterFeed($parameter);
  $total_records = count($content);
  if($total_records >0 && is_array($content))
  for($i=0;$i<$total_records && $import_count<$max_item ;$i++)
  {
   $error            = false;
   $item_id          = sanitize_text_field($content[$i]['id']);
   $item_title       = sanitize_text_field($content[$i]['title']);
   $item_url         = wp_filter_nohtml_kses($content[$i]['url']);
   $item_description = stripslashes($content[$i]['description']);
   $item_image       = wp_filter_nohtml_kses($content[$i]['image']);
   $item_currency    = wp_filter_nohtml_kses($content[$i]['currency']);
   $item_size        = wp_filter_nohtml_kses($content[$i]['size']);
   $item_color       = wp_filter_nohtml_kses($content[$i]['color']);
   $item_price       = wp_filter_nohtml_kses($content[$i]['price']);
   $item_currency    = wp_filter_nohtml_kses($content[$i]['currency']);
   

  
   if(strlen($item_title)<=0)
   {
    $error=true;
   }
   elseif($result1=$wpdb->get_var($wpdb->prepare("select post_title FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE    meta_key = '_flipkart_id' AND meta_value='%s'",$item_id)))
   {
    $error=true;
   }  
   if(!$error)
   {
    $sql_data_array=array();
    $sql_data_array=array(
                          'post_title'    => $item_title,
                          'post_content'  => $item_description,
                          'post_author'   => 1,
                          'post_status' => $publish_status,
                         );
    ////////////////////////////////////////////
   
    
    $template_format=$display_template;
    if($template_format!='')
    {
	 $template_format = nl2br($template_format);
     
     
     if($item_color!='')
      $template_format = str_replace("{product_color}",'<span class="feed_color">'.$item_color.'</span>' ,$template_format);
     else
      $template_format = str_replace("{product_color}",'' ,$template_format);

     if($item_size!='')
      $template_format = str_replace("{product_size}",'<span class="feed_size">'.$item_size.'</span>' ,$template_format);
     else
      $template_format = str_replace("{product_size}",'' ,$template_format);

     if($item_price!='')
      $template_format = str_replace("{product_price}",'<span class="feed_price">'.$item_price.'</span>' ,$template_format);
     else
      $template_format = str_replace("{product_price}",'' ,$template_format);
    
     if($item_currency!='')
      $template_format = str_replace("{product_currency}",'<span class="feed_currency">'.$item_currency.'</span>' ,$template_format);
     else
      $template_format = str_replace("{product_currency}",'' ,$template_format);

    
	 $template_format = str_replace("{product_description}",$item_description ,$template_format);
     $template_format = str_replace("{product_image_url}",$item_image,$template_format);

     if($item_url!='')
     {
      $template_format = str_replace("{product_detail_url}",$item_url ,$template_format); 
      $template_format = str_replace("{product_detail_link}","<span class='feed_url_link'><a href='".$item_url."' target='_blank'>".$item_url."</a></span>" ,$template_format); 
      $template_format = str_replace("{product_detail_more_link}","<span class='feed_url'><a href='".$item_url."' target='_blank'>More >></a></span>" ,$template_format); 
     }	 
    }
    else
    $template_format =$item_description;
    //echo $template_format ;die();
    $sql_data_array['post_content'] = $template_format;
    ///////////////////////////////////////////
    $now=current_time('mysql');
    $sql_data_array['post_date']=$now;
    $sql_data_array['post_date_gmt']=current_time('mysql',1);
    if($post_ID = wp_insert_post($sql_data_array))
    {
	 wp_set_post_terms( $post_ID, $wp_category, 'category');
     update_post_meta($post_ID,'_source','flipkart');	
 	 update_post_meta($post_ID,'_flipkart_id',$item_id);	
    
	 $import_count=$import_count+1;
     $import_items=$import_items+1;
    }
   }
  }
  if($import_count >0)
  update_option('flipkartImporter_message','Successfully '. $import_count.' Product import ');
  $next_activate = flipkart_importer_next_runtime($run_every,$occurrence_type,current_time('mysql'));
  
  $idata['next_activate'] = $next_activate;
  $sdata=json_encode($idata);
  update_option('flipkart_importer_setting',$sdata);
  if($switch)
  restore_current_blog();
 
}
endif;
if (!function_exists('flipkartImporterFeed')):
function flipkartImporterFeed($parameter=array())
{
  $affiliate_id     = sanitize_text_field($parameter['affiliate_id']);
  $affiliate_token  = sanitize_text_field($parameter['affiliate_token']);
  $feed_keyword     = sanitize_text_field($parameter['keyword']);
  $max_feed         = sanitize_text_field($parameter['max_item']);
  $content          ='';
  if($affiliate_id=='' ||  $affiliate_token=='')
  return'';
  $api_url         = "https://affiliate-api.flipkart.net/affiliate/1.0/search.json?query=".urlencode($feed_keyword)."&resultCount=".urlencode($max_feed);
  $content         = flipkart_importer_readFeeds($affiliate_id,$affiliate_token,$api_url); 
  return $content;
}
endif;
///////
if (!function_exists('flipkart_importer_readFeeds')):
function flipkart_importer_readFeeds($affiliate_id='',$affiliate_token='',$url)
{
 $flipkart_content=array();
 if (function_exists('curl_init') )
 {
  $ch = curl_init();
  $headers = array();
  $headers[] = 'Fk-Affiliate-Id:'.urlencode($affiliate_id);
  $headers[] = 'Fk-Affiliate-Token:'.urlencode($affiliate_token);
  curl_setopt($ch, CURLOPT_USERAGENT,(isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : "Mozilla/5.0 (compatible; Konqueror/4.0; Microsoft Windows) KHTML/4.0.80 (like Gecko)"));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER ,false);
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_TIMEOUT, 160);
  $data = curl_exec($ch);
  $error = curl_error($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if($data && $http_code==200)
  {
   $r=json_decode($data,true);
   $results = $r['products'];
   $i=0;
   if($results)
   foreach($results as $c)
   {
    $current=$c['productBaseInfoV1'];
    $product_key    =  wp_filter_nohtml_kses($current['productId']); 
    $product_title  =  sanitize_text_field($current['title']); 
    $product_desc   =  wp_filter_nohtml_kses($current['productDescription']); 
    $product_url    =  wp_filter_nohtml_kses($current['productUrl']); 
    $product_img    =  wp_filter_nohtml_kses($current['imageUrls']['400x400']); 
    $product_price  =  wp_filter_nohtml_kses($current['flipkartSellingPrice']['amount']); 
    $product_curr   =  wp_filter_nohtml_kses($current['flipkartSellingPrice']['currency']); 
    $product_size   =  wp_filter_nohtml_kses($current['attributes']['size']); 
    $product_color  =  wp_filter_nohtml_kses($current['attributes']['color']); 

     $flipkart_content[$i]=array(
                        'id'          => $product_key,
                        'title'       => $product_title,
                        'description' => $product_desc,
                        'url'         => $product_url,
 		                'image'       => $product_img,
 		                'price'       => $product_price,
 		                'currency'    => $product_curr,
 		                'size'        => $product_size,
 		                'color'       => $product_color,
                       );
     $i++;
    }
   }
  }
 return $flipkart_content;
}
endif;
?>