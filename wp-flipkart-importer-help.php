<?php
/*
***************************************************
**********# Name  : Shambhu Prasad Patnaik #*******
***************************************************
*/

if (!function_exists('flipkartImporter_help')):
function flipkartImporter_help()
{
 wp_enqueue_style( 'wp-flipkart-importer',plugins_url('/stylesheet.css', __FILE__) );

 echo '<div class="wrap">';
	 ?>
   <div>
	 
   <p class="intro">WP Flipkart Importer WordPress Plugin Import product using flipkart API.</p>
	 
	 <ol id="ul_flipkart_help">
	  <li><h3 class="flipkart_heading">Install WP Flipkart Importer WordPress Plugin</h3>
	  <ul class="flipkart_help_square">
	   <li>Upload the WP Flipkart Importer WordPress Plugin folder to the /wp-content/plugins/ directory</li>
	   <li>Activate the WP Flipkart Importer WordPress Plugin through the 'Plugins' menu in WordPress</li>
	   <li>Go <b>Flipkart Importer</b> in admin menu and add set parameter click update button,after that click Product Import Now Button</li>	   
	  </ul>
	 </li>
	 <li><h3 class="flipkart_heading">Plugin Help File</h3>
	 After installing  WP Flipkart Importer  WordPress Plugin,set default parameter.
	  <ul class="flipkart_help_lower-alpha">
	   <li><h4 class="flipkart_heading1">WP Flipkart Importer Settings</h4>
	    <ul>
	     <li>
	      <ul class="flipkart_help_square">
		   <li><strong>Fk-Affiliate-Id</strong><br>Enter your Affiliate ID for Flipkart, Your Affiliate ID from Flipkart.Don't you have such a key- <a href="https://affiliate.flipkart.com/api/api-token" target="_balnk">Request one here</a>.</li>
		   <li><strong>Fk-Affiliate-Token</strong><br>Enter your Affiliate Token for Flipkart, Your Affiliate Token from Flipkart.Don't you have such a key- <a href="https://affiliate.flipkart.com/api/api-token" target="_balnk">Request one here</a>.</li>
		   <li><strong>Keyword<br></strong>Default Keyword  show that base import product</li>
		   <li><strong>Feed Status<br></strong>It Active then automatically product import</li>
		   <li><strong>New Post Status<br></strong>Default Post status </li>
		   <li><strong>Category Name<br></strong>Default New Post category</li>
		   <li><strong>Run Every<br></strong> Built in cron feature that automatically fetches products from Flipkart site that can be set to run after specific periods like day, week etc</li>
		   <li><strong>Max Items<br></strong>Maximum product import</li>
		   <li><strong>Display Template<br></strong>Product this based display</li>
		  </ul>
         </li>
	    </ul>
	   </li>	   
	  </ul>
	 </li>
	</ol>
   </div>
   <?php
echo'
 <div>
   <h3 class="flipkart_heading"><a id="template_macro"></a></a>Display Template Macro</h3>
 <table border="0" width="97%" cellspacing="1" cellpadding="2" class="middle_table1">
   <tr>
     <td valign="top">
       <table border="0" width="100%" cellspacing="1" cellpadding="4" class="middle_table2">
        <tr class="dataTableHeadingRow">
         <td class="dataTableHeadingContent"  align="center">Name</td>
         <td class="dataTableHeadingContent"   align="center">Description</td>
        </tr>
        <tr class="dataTableRow1">
         <td class="dataTableContent" valign="top">{product_color}</td>
         <td class="dataTableContent"  valign="top">Product  color name display.</td>
        </tr>
        <tr class="dataTableRow2">
         <td class="dataTableContent" valign="top">{product_price}</td>
         <td class="dataTableContent"  valign="top">Product  Price  display.</td>
        </tr>
        <tr class="dataTableRow1">
         <td class="dataTableContent" valign="top">{product_currency}</td>
         <td class="dataTableContent"  valign="top">Product  currency display.</td>
        </tr>
        <tr class="dataTableRow2">
         <td class="dataTableContent"  valign="top">{product_description}</td>
         <td class="dataTableContent"  valign="top" >Product description display.</td>
        </tr>
        <tr class="dataTableRow1">
         <td class="dataTableContent"  valign="top">{product_size}</td>
         <td class="dataTableContent"  valign="top">Product size display</td>
        </tr>       
        <tr class="dataTableRow2">
         <td class="dataTableContent"  valign="top">{product_detail_url}</td>
         <td class="dataTableContent"  valign="top">Product detail url from Flipkart like <i>http://www.flipkart.com</i> </td>
        </tr>
        <tr class="dataTableRow1">
         <td class="dataTableContent"  valign="top">{product_detail_link}</td>
         <td class="dataTableContent"  valign="top">Product detail url with link from indeed like <a href="http://www.flipkart.com" target="_blank">http://www.flipkart.com</a></td>
        </tr>
        <tr class="dataTableRow2">
         <td class="dataTableContent"  valign="top">{product_detail_url_more_link}</td>
         <td class="dataTableContent"  valign="top">Product detail url link from indeed like <a href="http://www.flipkart.com" target="_blank">More >></a></td>
        </tr>        
       </table>
      </td>
     </tr>
    </table>';?>
	<br>
	<div>More Detail - <a href="http://socialcms.wordpress.com/" target="_blank">http://socialcms.wordpress.com</a></div>
	<div>In case of any clarifications, pl. contact us at - <a href="http://profiles.wordpress.org/shambhu-patnaik/" target="_blank">http://profiles.wordpress.org/shambhu-patnaik/</a></div>
	<br>
	<div><b>Thanks a Lot</b></div>
	<br>
	<br>
    <div align="center">********************</div>
</div>
<?php
}
endif;
?>