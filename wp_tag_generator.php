<?php
/*
Plugin Name: WP Tag Generator
Version: 1.0
Plugin URI: http://www.seo-doctor.co.uk/seo-tools.html
Author: Thamizhchelvan
Author URI: http://www.seo-doctor.co.uk/
Description: Will generate tags based on the keyword while creating page or post.
*/

if(isset($_REQUEST['req']) && $_REQUEST['req'] == 'termajax' && $_REQUEST['term'] != ''){

	$term = trim($_REQUEST['term']);

	$tags = wp_generate_tag($term);

	echo implode(", ",$tags);

	exit;

}

/**
 * Function to generate related keywords or tags for the given keyword
 * @return array of tags
 * @param String $term
 */
function wp_suggest_tag(){
	$http_path_plugin = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'wp_tag_generator.php';
	?>
<script language="JavaScript" type="text/javascript">
function wp_fetch_term_tags(){
	var search_term = document.getElementById('wp_tag_search_term').value;
	if(search_term == '')
		return '';
	var applyField = document.getElementById('wp_tag_field_id').value;
	//for ajax operations

	var xmlhttp;

	if (window.XMLHttpRequest){

	 // code for IE7+, Firefox, Chrome, Opera, Safari

	  xmlhttp=new XMLHttpRequest();

  	}

	else{

	 // code for IE6, IE5

	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");

  	}

	xmlhttp.onreadystatechange=function(){

		if(xmlhttp.readyState==4){

			  out = eval("document.post."+applyField+".value = '"+xmlhttp.responseText+"';");

  		}

	}

xmlhttp.open("GET","<?php echo $http_path_plugin;?>?req=termajax&term="+search_term,true);

xmlhttp.send(null);



}

</script>

	<div class="postbox" id="wpgeneratetags" style="display: block;">

<div title="Click to toggle" class="handlediv"><br/></div><h3 class="hndle"><span>Generate Tags</span></h3>

<div class="inside">

Enter Term:<input type="text" size="15" id="wp_tag_search_term" name="wp_tag_search_term"> | Field Id:<input type="text" id="wp_tag_field_id" size="15" value="aiosp_keywords" name="wp_tag_field_id">&nbsp;

<input type="button" tabindex="3" value="Fetch" onclick="javascript:wp_fetch_term_tags();" class="button tagadd"/>

</div>

</div>

	<?php

}

function wp_generate_tag($term,$limit=15){

	$term = trim($term);

	if($term == '')

		return array();

	$meta_tags = wp_get_meta_terms($term);

	$meta_tags = wp_filter_meta_tags($meta_tags);

	$meta_tags = array_unique($meta_tags);

	shuffle($meta_tags);

	if(count($meta_tags) <= $limit)

		return $meta_tags;

	return array_slice($meta_tags,0,$limit);

}

function wp_filter_meta_tags($meta_tags){

	$filtered_tags = array();

	for($i=0;$i < count($meta_tags);$i++){

		$ctag = trim($meta_tags[$i]);

		if($ctag == '')

			continue;

		$filtered_tags[] = $ctag;

	}

	return $filtered_tags;

}

function wp_get_yahoo_terms($term){

	$url = 'http://api.search.yahoo.com/WebSearchService/rss/webSearch.xml?appid=yahoosearchwebrss&query='.urlencode($term).'&adult_ok=1';

	$xml = simplexml_load_file($url);

	if(!$xml)

		return array();

	//process all urls

	$meta_tags = array();

	foreach($xml->channel->item as $data)

	{

		$current_meta = @get_meta_tags($data->link);

		if(isset($current_meta['keywords']))

			$meta_tags = array_merge($meta_tags,explode(",",$current_meta['keywords']));



	}

	return $meta_tags;

}



function wp_get_meta_terms($term){

	$yahoo_terms = wp_get_yahoo_terms($term);

	return $yahoo_terms;

}

add_action('edit_form_advanced','wp_suggest_tag');
add_action('edit_page_form','wp_suggest_tag');

?>