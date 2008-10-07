<?php /*
Plugin Name: Tally Graph
Plugin URI: http://wordpress.org/extend/plugins/tally-graph/
Description: Add Google charts and graphs to your WordPress site based on tallies of any numeric custom field over time. Visualize progress toward any goal by day, week, month, or year.
Version: 0.1.1
Author: Dylan Kuhn
Author URI: http://www.cyberhobo.net/
Minimum WordPress Version Required: 2.5.1
*/

/*
Copyright (c) 2005-2007 Dylan Kuhn

This program is free software; you can redistribute it
and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation;
either version 2 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
PURPOSE. See the GNU General Public License for more
details.
*/

	
function tally_graph($atts) {
	return '<img src="'.tally_graph_url($atts).'" alt="'.$atts['key'].'" />';
}

function tally_graph_url($atts) {
	global $post;

	$atts = wp_parse_args($atts);
	$defaults = array('interval_count' => '6', 'chs' => '200x200', 'cht' => 'bvs');
	$atts = array_merge($defaults, $atts);
	if (!isset($atts['key'])) return 'Tally Graph: required parameter "key" is missing.';
	
	// Extract non-chart variables from attributes
	$keys = split(',',$atts['key']); 
	unset($atts['key']);
	$use_cache = true;
	if (isset($atts['no-cache'])) {
		$use_cache = false;
		unset($atts['no-cache']);
	}
	if (isset($atts['to_date'])) {
		$end_time = strtotime($atts['to_date']);
		if (!$end_time) {
			return 'Tally Graph: couldn\'t read the to_date ' . $atts['to_date'];
		}
		unset($atts['to_date']);
	} else if ($post) {
		$end_time = strtotime($post->post_date);
	} else {
		$end_time = time();
	}
	if (isset($atts['tally_interval'])) {
		$tally_interval = $atts['tally_interval'];
		unset($atts['tally_interval']);
	} else {
		$tally_interval = 'month';
	}
	$interval_count = $atts['interval_count'];
	unset($atts['interval_count']);
	
	list($index_gnu_format, $index_mysql_format, $first_day_suffix) = tally_graph_interval_settings($tally_interval);

	// Always start on the first day of the starting interval
	$start_time = strtotime('-'.$interval_count.' '.$tally_interval,$end_time);
	$next_date_prefix = date($index_gnu_format,strtotime('+1 '.$tally_interval,$start_time));
	$start_time = strtotime($next_date_prefix.$first_day_suffix);
	// Always end on the first day after the ending interval
	$next_date_prefix = date($index_gnu_format,strtotime('+1 '.$tally_interval,$end_time));
	$end_time = strtotime($next_date_prefix.$first_day_suffix);

	// Return cached URL if available
	if ($use_cache) {
		$cache_key = 'tally-graph-'.md5($start_time.$end_time.$tally_interval.serialize($atts));
		$cached_url = wp_cache_get($cache_key);
		if ($cached_url) {
			return $cached_url;
		}
	}

	// Tally ho
	$key_counts = array();
	foreach($keys as $index => $key) {
		$key_counts[$index] = tally_graph_get_counts($key, $start_time, $end_time, $tally_interval);
	}

	// Build the chart parameters
	$chd = 't:';
	$month_names = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May',
		'06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
	$first_index = $chd_min = $chd_max = null;
	foreach($key_counts as $index => $counts) {
		if (is_null($first_index)) $first_index = $index;
		if ($index != $first_index) $chd .= '|';
		$comma = '';
		foreach($counts as $date_index => $count) {
			$compare_count = $count;
			if ($index != $first_index && $atts['cht'] == 'bvs') {
				$compare_count = $key_counts[$first_index][$date_index] + $count;
			}
			if (is_null($chd_min) || $compare_count < $chd_min) $chd_min = $compare_count;
			if (is_null($chd_max) || $compare_count > $chd_max) $chd_max = $compare_count;
			$chd .= $comma . $count;
			$comma = ',';
			if ($index == $first_index) {
				$day_label_string .= '|'.substr($date_index,8,2);
				$week_label_string .= '|'.substr($date_index,5,3);
				$month = $month_names[substr($date_index,5,2)];
				if ($month != $last_month) $last_month = $month;
				else $month = ' ';
				$month_label_string .= '|'.$month;
				$year = substr($date_index,0,4);
				if ($year != $last_year) $last_year = $year;
				else $year = ' ';
				$year_label_string .= '|'.$year;
			}
		}
	}
	// Give nonzero minimum values a 10% pad
	$pad_min = floor($chd_min - (($chd_max - $chd_min)/10));
	$chd_min = ($pad_min>0) ? $pad_min : $chd_min;
	
	// Set Google chart attributes
	// chart data
	$atts['chd'] = $chd;
	if (!isset($atts['chds'])) {
		// Provide chart scale
		$atts['chds'] = $chd_min.','.$chd_max;
	}
	if (!isset($atts['chxt'])) {
		// Provide labels
		if ($tally_interval == 'year') {
			$atts['chxt'] = 'y,x';
			$atts['chxl'] = '1:'.$year_label_string;
		} else if ($tally_interval == 'week') {
			$atts['chxt'] = 'y,x,x';
			$atts['chxl'] = '1:'.$week_label_string.'|2:'.$year_label_string;
		} else if ($tally_interval == 'day') {
			$atts['chxt'] = 'y,x,x,x';
			$atts['chxl'] = '1:'.$day_label_string.'|2:'.$month_label_string.
				'3:'.$year_label_string;
		} else {
			$atts['chxt'] = 'y,x,x';
			$atts['chxl'] = '1:'.$month_label_string.'|2:'.$year_label_string;
		}
	}
	if (!isset($atts['chxr'])) {
		// Provide count range labels at index 0
		$atts['chxr'] = '0,'.$chd_min.','.$chd_max;
	}
	$chart_url = 'http://chart.apis.google.com/chart';
	$separator = '?';
	foreach($atts as $name => $value) {
		$chart_url .= $separator.$name.'='.urlencode($value);
		$separator = '&amp;';
	}
	if ($use_cache) {
		wp_cache_set($cache_key, $chart_url, '', 60 * 60 * 24 * 7); 
	}
	return $chart_url;
}

function tally_graph_get_counts($key, $start_time, $end_time, $interval) {
	global $wpdb;

	list($gnu_index_format, $mysql_index_format) = tally_graph_interval_settings($interval);
	$counts = array();
	for($the_time = $start_time; $the_time < $end_time; $the_time = strtotime('+1 '.$interval,$the_time)) {
		$counts[date($gnu_index_format,$the_time)] = 0;
	}

	$query_sql = 'SELECT DATE_FORMAT(p.post_date,\''.$mysql_index_format.'\') AS xdata '.
		',SUM(pm.meta_value) AS ydata '.
		'FROM '. $wpdb->posts .' p '. 
		'JOIN '. $wpdb->postmeta .' pm ON pm.post_id = p.ID '.
		'WHERE pm.meta_key = \''. $key .'\' '.
		'AND p.post_date >= \''.date('Y-m-d',$start_time).'\' '.
		'AND p.post_date < \''.date('Y-m-d',$end_time).'\' '.
		'GROUP BY DATE_FORMAT(p.post_date,\''.$mysql_index_format.'\') '.
		'ORDER BY p.post_date';
	$wpdb->query($query_sql);
	if ($wpdb->last_result) {
		$chd_min = $chd_max = $wpdb->last_result[0]->ydata;
		foreach ($wpdb->last_result as $result) {
			if ($result->ydata < $chd_min) $chd_min = $result->ydata;
			else if ($result->ydata > $chd_max) $chd_max = $result->ydata;
			$counts[$result->xdata] = $result->ydata;
		}
	}
	return $counts;
}

function tally_graph_interval_settings($interval) {
	if ($interval == 'year') {
		return array('Y','%Y','-01-01');
	} 
	if ($interval == 'week') {
		return array('Y-\\WW','%Y-W%v','-1');
	} 
	if ($interval == 'day') {
		return array('Y-m-d','%Y-%m-%d','');
	} 
	return array('Y-m','%Y-%m','-01');
}

add_shortcode('tally_graph','tally_graph');
